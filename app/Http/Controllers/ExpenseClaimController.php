<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ExpenseClaimApprovalMail;
use App\Mail\ExpenseClaimFullyApprovedMail;
use App\Mail\ExpenseClaimPendingVerificationMail;
use App\Mail\ExpenseClaimRejectedMail;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\ExpenseClaim;
use App\Models\ExpenseClaimHistory;
use App\Models\ExpenseClaimItem;
use App\Models\ExpenseType;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ExpenseClaimSubmittedMail;
use App\Mail\ExpenseClaimVerifiedMail;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use phpDocumentor\Reflection\Types\Nullable;

class ExpenseClaimController extends Controller
{
    public array $chargedTo = [
        [
            'id' => 'department',
            'name' => 'Department'
        ],
        [
            'id' => 'project',
            'name' => 'Project'
        ],
    ];

    public function index(Request $request)
    {
        $claims = ExpenseClaim::with([
            'company:id,name',
            'department:id,name',
            'employee:id,name'
        ])
            ->withCount('items')
            ->forUserModuleAccess()
            ->when($request->search, function ($query, $search) {
                $query->where('claim_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->company_id, function ($query, $company_id) {
                $query->where('company_id', $company_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($claim) => [
                'id' => $claim->id,
                'claim_number' => $claim->claim_number,
                'status' => $claim->status,
                'total_amount_local_currency' => $claim->total_amount_local_currency,
                'created_at' => $claim->created_at->format('d M Y'),
                'company' => $claim->company,
                'department' => $claim->department,
                'employee' => $claim->employee,
                'items_count' => $claim->items_count,
                'can' => [
                    'update' => $request->user()->can('update', $claim),
                ],
            ]);


        return Inertia::render('ExpenseClaim/Index', [
            'claims' => $claims,
            'filters' => $request->only(['search', 'status', 'company_id']),

        ]);
    }
    public function create(Request $request)
    {
        $chargedTo = [
            [
                'id' => 'department',
                'name' => 'Department'
            ],
            [
                'id' => 'project',
                'name' => 'Project'
            ],
        ];

        $moduleContext = $request->attributes->get('moduleAccessDetails');
        $roleName = strtolower($moduleContext->firstWhere('company_id', Auth::user()?->employee?->company_id)?->role ?? '');
        $canSubmitForOthers = in_array($roleName, ['admin', 'verifier', 'approver']);

        return Inertia::render('ExpenseClaim/Create', [
            'lookups' => [
                'employees' => Employee::select('id', 'name', 'company_id', 'department_id')
                    ->where('is_active', true)
                    ->where('is_system_record', false)
                    ->with('company')
                    ->orderBy('name', 'asc')
                    ->get(),
                'companies' => Company::select('id', 'name')->where('is_active', true)->get(),
                'currencies' => Currency::select('id', 'code')->where('is_active', true)->get()
                    ->map(function ($currency) {
                        return [
                            'id' => $currency->id,
                            'name' => $currency->code
                        ];
                    })->values(),
                'categories' => ExpenseCategory::select('id', 'name', 'code')->where('is_active', true)->get(),
                'expenseTypes' => ExpenseType::select('id', 'name')->where('is_active', true)->get(),
                'departments' => Department::select('id', 'name')->get(),
                'projects' => Project::select('id', 'name')->get(),
                'chargedTo' => $this->chargedTo,
                'canSubmitForOthers' => $canSubmitForOthers,
                'employeeModuleRole' => $roleName
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'claim_date' => 'required|date',
                'currency_id' => 'required|exists:currencies,id',
                'employee_id' =>  'required|exists:employees,id',
                'expense_type_id' => 'required|exists:expense_types,id',
                'is_billed_to_client' => 'nullable|boolean',
                'has_policy_exception' => 'nullable|boolean',
                'client_name' => 'nullable|required_if:is_billed_to_client, true|string|max:255',
                'exception_reason' => 'nullable|required_if:has_policy_exception,true|string',
                'items' => 'required|array|min:1',
                'items.*.expense_category_id' => 'required|exists:expense_categories,id',
                'items.*.bill_date' => 'required|date',
                'items.*.invoice_number'        => 'required|string|max:255',
                'items.*.supplier_client_name'  => 'required|string|max:255',
                'items.*.attendee_employee_names' => 'nullable|string|max:255',
                'items.*.description'           => 'nullable|string',
                'items.*.charged_to_type'       => 'nullable|string|max:255',
                'items.*.charged_to_id'         => 'nullable|string|max:255',
                'items.*.item_currency_id'      => 'required|exists:currencies,id',
                'items.*.exchange_rate'         => 'required|numeric|min:0.000001',
                'items.*.amount'                => 'required|numeric|min:0.01',
                'items.*.vat_amount'            => 'nullable|numeric|min:0',
                'items.*.attachment'            => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB limit
            ],
            [
                // Custom messages per rule
                'items.*.expense_category_id.required'  => 'Category is required.',
                'items.*.invoice_number.required'       => 'Invoice / Receipt No. is required.',
                'items.*.supplier_client_name.required' => 'Merchant / Supplier name is required.',
                'items.*.amount.min'                    => 'Amount must be at least 0.01.',
                'items.*.attachment.required'           => 'Receipt attachment is required.',
            ]
        );


        $currentUser = Auth::user();
        $currentEmployeeId = $validatedData['employee_id'];
        $expenseUser = User::where('employee_id', $currentEmployeeId)->first();

        $currentModule = $request->attributes->get('moduleDetails');
        $buApproverId = $expenseUser->employee->department->head_user_id;
        $lineManagerId = $expenseUser->employee->line_manager_id;


        $claim = DB::transaction(function () use ($validatedData, $currentEmployeeId, $request, $currentUser, $expenseUser) {
            $empVlaues = Employee::select('company_id', 'department_id')->where('id', $currentEmployeeId)->first()->toArray();

            //generate a unique claim number
            $claimNumber = 'EXP-' . date('Ym') . '-' . strtoupper(Str::random(5));
            $totalAmountClaimCurrency = 0;
            $totalVatClaimCurrency    = 0;
            $totalAmountLocalCurrency  = 0;

            foreach ($validatedData['items'] as $item) {
                $amount = (float) $item['amount'];
                $vatAmount = (float) ($item['vat_amount'] ?? 0);
                $exchangeRate = (float)$item['exchange_rate'];

                $totalAmountClaimCurrency += $amount;
                $totalVatClaimCurrency += $vatAmount;

                $totalAmountLocalCurrency += ($amount + $vatAmount) * $exchangeRate;
            }

            $lineManagerId = null;
            $buApproverId = null;
            if ($totalAmountLocalCurrency > 5000) {
                $buApproverId = $expenseUser->employee->department->head_user_id;
            } else {
                $lineManagerId = $expenseUser->employee->line_manager_id;
            }

            $claim = ExpenseClaim::create([
                'claim_number' => $claimNumber,
                'company_id' => $empVlaues['company_id'],
                'employee_id' => $validatedData['employee_id'],
                'department_id' => $empVlaues['department_id'],
                'currency_id' => $validatedData['currency_id'],
                'claim_date' => $validatedData['claim_date'],
                'line_manager_id' => $lineManagerId,
                'bu_approver_id' => $buApproverId,
                'expense_type_id' => $validatedData['expense_type_id'],
                'is_billed_to_client' => $validatedData['is_billed_to_client'] ?? false,
                'client_name' => $validatedData['client_name'] ?? null,
                'has_policy_exception' => $validatedData['has_policy_exception'] ?? false,
                'exception_reason' => $validatedData['exception_reason'] ?? null,
                'status' => 'pending_verification',
                'total_amount_claim_currency' => $totalAmountClaimCurrency,
                'total_vat_claim_currency' => $totalVatClaimCurrency,
                'total_amount_local_currency' => $totalAmountLocalCurrency
            ]);

            foreach ($validatedData['items'] as $index => $itemData) {
                $amount = (float) $itemData['amount'];
                $rate = (float) $itemData['exchange_rate'];
                $vat = (float) ($itemData['vat_amount'] ?? 0);
                $amtLocal = ($amount + $vat) * $rate;

                $attachmentPath = null;
                $attachmentRef = null;

                if (isset($itemData['attachment']) && $itemData['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                    /** @var \Illuminate\Http\UploadedFile $file */
                    $file = $itemData['attachment'];

                    // 2. Store in storage/app/receipts/{claim_id} using default local disk
                    $attachmentPath = $file->store("receipts/{$claim->id}", 'local');
                    $attachmentRef  = $file->getClientOriginalName();
                }

                ExpenseClaimItem::create([
                    'expense_claim_id' => $claim->id,
                    'expense_category_id' => $itemData['expense_category_id'],
                    'bill_date' => $itemData['bill_date'],
                    'invoice_number' => $itemData['invoice_number'],
                    'supplier_client_name' => $itemData['supplier_client_name'],
                    'attendee_employee_names' =>  $itemData['attendee_employee_names'] ?? null,
                    'description' => $itemData['description'] ?? null,
                    'charged_to_type' => $itemData['charged_to_type'] ?? null,
                    'charged_to_id' => $itemData['charged_to_id'] ?? null,
                    'item_currency_id' => $itemData['item_currency_id'],
                    'exchange_rate' => $rate,
                    'amount' => $amount,
                    'vat_amount' => $vat,
                    'amount_local_currency' => $amtLocal,
                    'attachment_path' => $attachmentPath,
                    'attachment_ref' => $attachmentRef

                ]);
            }

            ExpenseClaimHistory::create([
                'expense_claim_id' => $claim->id,
                'action_by' => $currentUser->id,
                'action' => 'submitted',
                'from_status' => null,
                'to_status' => 'pending_verification',
                'comments' => 'Expense claim submitted for verification.'
            ]);

            return $claim;
        });

        $this->sendVerificationRequest($claim, $expenseUser, $currentModule);

        return redirect()->route('expense-claim.index')
            ->with('success', "Expense claim {$claim->claim_number} was successfully submitted.");
    }

    public function show(Request $request, ExpenseClaim $expenseclaim)
    {
        Gate::authorize('view', $expenseclaim);

        $expenseclaim->load([
            'company:id,name',
            'currency:id,code',
            'department:id,name',
            'employee:id,name,code',
            'lineManager:id,name',
            'buApprover:id,name',
            'financeApprover:id,name',
            'items.category:id,name',
            'items.currency:id,code',
            'histories.actionBy:id,name'

        ])->loadCount('items');


        return Inertia::render('ExpenseClaim/Show', [
            'claim' => $expenseclaim,
            'can' => [
                'update' => $request->user()->can('update', $expenseclaim),
                'verify' => $request->user()->can('verify', $expenseclaim),
                'approve' => $request->user()->can('approve', $expenseclaim),
                'financeapprove' => $request->user()->can('financeapprove', $expenseclaim),
                'book' => $request->user()->can('book', $expenseclaim),
                'post' => $request->user()->can('post', $expenseclaim),
                'financeview' => $request->user()->can('financeView', $expenseclaim),
            ]
        ]);
    }

    public function edit(Request $request, ExpenseClaim $expenseclaim)
    {

        Gate::authorize('update', $expenseclaim);


        $moduleContext = $request->attributes->get('moduleAccessDetails');

        $roleName = strtolower($moduleContext->firstWhere('company_id', Auth::user()?->employee?->company_id)?->role ?? '');

        $canSubmitForOthers = in_array($roleName, ['admin', 'verifier', 'approver']);

        $expenseclaim->load([
            'items',
            'employee:id,name'
        ]);



        return Inertia::render('ExpenseClaim/Edit', [
            'claim' => $expenseclaim,
            'lookups' => [
                'currencies' => Currency::select('id', 'code')->where('is_active', true)->get()
                    ->map(function ($currency) {
                        return [
                            'id' => $currency->id,
                            'name' => $currency->code
                        ];
                    })->values(),
                'expenseTypes' => ExpenseType::select('id', 'name')->where('is_active', true)->get(),
                'canSubmitForOthers' => $canSubmitForOthers,
                'categories' => ExpenseCategory::select('id', 'name')->where('is_active', true)->get(),
                'chargedTo' => $this->chargedTo,
                'departments' => Department::select('id', 'name')->get(),
                'projects' => Project::select('id', 'name')->get(),


            ]
        ]);
    }

    public function update(Request $request, ExpenseClaim $expenseclaim)
    {
        $validatedData = $request->validate([
            'claim_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
            'expense_type_id' => 'required|exists:expense_types,id',
            'is_billed_to_client' => 'nullable|boolean',
            'has_policy_exception' => 'nullable|boolean',
            'client_name' => 'nullable|required_if:is_billed_to_client, true|string|max:255',
            'exception_reason' => 'nullable|required_if:has_policy_exception,true|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable',
            'items.*.expense_category_id' => 'required|exists:expense_categories,id',
            'items.*.bill_date' => 'required|date',
            'items.*.invoice_number'        => 'required|string|max:255',
            'items.*.supplier_client_name'  => 'required|string|max:255',
            'items.*.attendee_employee_names' => 'nullable|string|max:255',
            'items.*.description'           => 'nullable|string',
            'items.*.charged_to_type'       => 'nullable|string|max:255',
            'items.*.charged_to_id'         => 'nullable|string|max:255',
            'items.*.item_currency_id'      => 'required|exists:currencies,id',
            'items.*.exchange_rate'         => 'required|numeric|min:0.000001',
            'items.*.amount'                => 'required|numeric|min:0.01',
            'items.*.vat_amount'            => 'nullable|numeric|min:0',
            'items.*.attachment'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB limit
        ], [
            // Custom messages per rule
            'items.*.expense_category_id.required'  => 'Category is required.',
            'items.*.invoice_number.required'       => 'Invoice / Receipt No. is required.',
            'items.*.supplier_client_name.required' => 'Merchant / Supplier name is required.',
            'items.*.amount.min'                    => 'Amount must be at least 0.01.',
        ]);



        $currentUser = Auth::user();
        $expenseUser = $expenseclaim->user;
        $currentModule = $request->attributes->get('moduleDetails');


        return DB::transaction(function () use (
            $expenseclaim,
            $validatedData,
            $currentUser,
            $expenseUser,
            $currentModule
        ) {

            $fromStatus     = $expenseclaim->status;
            $isResubmission = ($fromStatus === 'rejected');
            $targetStatus   = $isResubmission ? 'pending_verification' : $fromStatus;

            $totalAmountClaimCurrency = 0;
            $totalVatClaimCurrency    = 0;
            $totalAmountLocalCurrency  = 0;

            foreach ($validatedData['items'] as $item) {
                $amount = (float) $item['amount'];
                $vatAmount = (float) ($item['vat_amount'] ?? 0);
                $exchangeRate = (float) $item['exchange_rate'];

                $totalAmountClaimCurrency += $amount;
                $totalVatClaimCurrency += $vatAmount;
                $totalAmountLocalCurrency +=  ($amount + $vatAmount) * $exchangeRate;
            }

            $expenseclaim->update([
                'claim_date' => $validatedData['claim_date'],
                'currency_id' => $validatedData['currency_id'],
                'expense_type_id' => $validatedData['expense_type_id'],
                'is_billed_to_client' => $validatedData['is_billed_to_client'] ?? false,
                'has_policy_exception' => $validatedData['has_policy_exception'] ?? false,
                'client_name' => $validatedData['is_billed_to_client'] ? $validatedData['client_name'] : null,
                'exception_reason' => $validatedData['has_policy_exception'] ? $validatedData['exception_reason'] : null,
                'total_amount_claim_currency' => $totalAmountClaimCurrency,
                'total_vat_claim_currency' => $totalVatClaimCurrency,
                'total_amount_local_currency' => $totalAmountLocalCurrency,
                'status'                      => $isResubmission ? 'pending_verification' : $expenseclaim->status,
                'verifier_id'                 => $isResubmission ? null : $expenseclaim->verifier_id,
                'verified_at'                 => $isResubmission ? null : $expenseclaim->verified_at,
            ]);

            //Delete removed items from DB
            $submittedIds = collect($validatedData['items'])
                ->pluck('id')
                ->filter(fn($id) => is_numeric($id))
                ->toArray();

            $expenseclaim->items()->whereNotIn('id', $submittedIds)->delete();

            foreach ($validatedData['items'] as $itemData) {
                $amount = (float) $itemData['amount'];
                $vat = (float) ($itemData['vat_amount'] ?? 0);
                $rate = (float) $itemData['exchange_rate'];
                $amtLocal = ($amount + $vat) * $rate;

                $itemId = is_numeric($itemData['id'] ?? null) ? $itemData['id'] : null;
                $existingItem =  $itemId ? ExpenseClaimItem::find($itemId) : null;

                $attachmentPath = null;
                $attachmentRef = null;
                if ($existingItem) {
                    $attachmentPath = $existingItem->attachment_path;
                    $attachmentRef = $existingItem->attachment_ref;
                }

                //if the attachment for the existing item has been changed
                if (isset($itemData['attachment']) && $itemData['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $itemData['attachment'];

                    $attachmentPath = $file->store("receipts/{$expenseclaim->id}", 'local');
                    $attachmentRef  = $file->getClientOriginalName();
                }

                $payLoad = [
                    'expense_claim_id' => $expenseclaim->id,
                    'expense_category_id' => $itemData['expense_category_id'],
                    'bill_date' => $itemData['bill_date'],
                    'invoice_number' => $itemData['invoice_number'],
                    'supplier_client_name' => $itemData['supplier_client_name'],
                    'attendee_employee_names' =>  $itemData['attendee_employee_names'] ?? null,
                    'description' => $itemData['description'] ?? null,
                    'charged_to_type' => $itemData['charged_to_type'] ?? null,
                    'charged_to_id' => $itemData['charged_to_id'] ?? null,
                    'item_currency_id' => $itemData['item_currency_id'],
                    'exchange_rate' => $rate,
                    'amount' => $amount,
                    'vat_amount' => $vat,
                    'amount_local_currency' => $amtLocal,
                    'attachment_path' => $attachmentPath,
                    'attachment_ref' => $attachmentRef
                ];


                if ($existingItem) {
                    //update
                    $existingItem->update($payLoad);
                } else {
                    //insert
                    ExpenseClaimItem::create($payLoad);
                }
            }
            ExpenseClaimHistory::create([
                'expense_claim_id' => $expenseclaim->id,
                'action_by' => $currentUser->id,
                'action' => $isResubmission ? 'resubmitted by employee' : 'updated',
                'from_status' => $fromStatus,
                'to_status' => $targetStatus,
                'comments' => $isResubmission ?  'Claim updated and resubmitted for verification.' : 'Expense claim updated.',
            ]);

            $this->sendVerificationRequest($expenseclaim, $expenseUser, $currentModule, isUpdate: true, isResubmitted: $isResubmission);

            return redirect()->route('expense-claim.index')
                ->with('success', $isResubmission ? 'Claim resubmitted successfully for verification.' :
                    "Expense claim {$expenseclaim->claim_number} was successfully updated and submitted.");
        });
    }

    public function verify(Request $request, ExpenseClaim $expenseclaim)
    {

        Gate::authorize('verify', $expenseclaim);

        $validatedData = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'nullable|required_if:action,reject|string|max:1000'
        ]);
        $currentUserId = Auth::id();

        return DB::transaction(function () use ($validatedData, $expenseclaim, $currentUserId) {

            $fromStatus = $expenseclaim->status;
            if ($validatedData['action'] === 'approve') {

                $targetApprover = $expenseclaim->line_manager_id
                    ? $expenseclaim->lineManager
                    : $expenseclaim->buApprover;

                if (!$targetApprover) {
                    return back()->withErrors([
                        'message' => 'Cannot verify claim: No Line Manager or BU Approver assigned.'
                    ]);
                }

                $targetApproverText = $expenseclaim->line_manager_id
                    ? 'Line Manager'
                    : 'BU Approver';

                $expenseclaim->update([
                    'verifier_id' => $currentUserId,
                    'verified_at' => now(),
                    'status' => 'pending_approval'
                ]);

                ExpenseClaimHistory::create([
                    'expense_claim_id' => $expenseclaim->id,
                    'action_by' => $currentUserId,
                    'action' => 'verified',
                    'from_status' => $fromStatus,
                    'to_status' => 'pending_approval',
                    'comments' => 'Expense claim verified.',
                ]);

                //mail to employee
                if ($expenseclaim->user) {
                    Mail::to($expenseclaim->user->email)->queue(new ExpenseClaimVerifiedMail($expenseclaim));
                }

                //mail to the approver
                if ($targetApprover->email) {
                    Mail::to($targetApprover->email)->queue(new ExpenseClaimApprovalMail($expenseclaim));
                }

                return back()->with('success', 'Claim verified successfully and forwarded to ' . $targetApproverText . ' for approval.');
            }

            if ($validatedData['action'] === 'reject') {
                $expenseclaim->update([
                    'verifier_id' => $currentUserId,
                    'verified_at' => now(),
                    'status' => 'rejected'
                ]);

                ExpenseClaimHistory::create([
                    'expense_claim_id' => $expenseclaim->id,
                    'action_by' => $currentUserId,
                    'action' => 'rejected by verifier',
                    'from_status' => $fromStatus,
                    'to_status' => 'rejected',
                    'comments' => $validatedData['rejection_reason'],
                ]);

                //mail to employee
                if ($expenseclaim->user) {
                    Mail::to($expenseclaim->user->email)->queue(new ExpenseClaimRejectedMail($expenseclaim, $validatedData['rejection_reason'], 'verify'));
                }

                return back()->with('success', 'Claim has been rejected and returned to employee.');
            }
        });
    }

    public function approve(Request $request, ExpenseClaim $expenseclaim)
    {
        Gate::authorize('approve', $expenseclaim);
        if ($expenseclaim->status !== 'pending_approval') {
            return back()->with('error', 'Claim is not in a status that allows manager approval.');
        }

        $validatedData = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'nullable|required_if:action,reject|string|max:1000'
        ]);

        $currentUserId = Auth::id();

        $expenseUser = $expenseclaim->user;
        $currentModule = $request->attributes->get('moduleDetails');



        return DB::transaction(function () use ($validatedData, $currentUserId, $expenseclaim, $expenseUser, $currentModule) {

            $actionText = 'approved';
            $rejectActionText = 'rejected';
            if ($expenseclaim->line_manager_id == $currentUserId) {
                $updateData['line_manager_approved_at'] = now();
                $actionText  = 'approved by line manager';
                $rejectActionText = 'rejected by line manager';
            } else if ($expenseclaim->bu_approver_id == $currentUserId) {
                $updateData['bu_approved_at'] = now();
                $actionText  = 'approved by BU';
                $rejectActionText = 'rejected by BU';
            }

            $fromStatus = $expenseclaim->status;

            if ($validatedData['action'] == 'approve') {
                $updateData['status'] = 'pending_finance_approval';

                $expenseclaim->update($updateData);

                ExpenseClaimHistory::create([
                    'expense_claim_id' => $expenseclaim->id,
                    'action_by' => $currentUserId,
                    'action' => $actionText,
                    'from_status' => $fromStatus,
                    'to_status' => 'pending_finance_approval',
                    'comments' => 'Claim approved and sent for Finance Approval.',
                ]);

                //seding mail to finance approver
                $financeApproverEmails = User::where('id', '!=', $expenseUser->id)
                    ->whereHas('roles', function ($query) use ($expenseUser, $currentModule) {
                        $query->where('company_role_user.company_id', $expenseUser->employee->company_id)
                            ->where('company_role_user.module_id', $currentModule->id)
                            ->where('roles.code', 'finance-approver');
                    })->pluck('email')
                    ->filter()
                    ->toArray();

                if (!empty($financeApproverEmails)) {
                    Mail::to($financeApproverEmails)->queue(new ExpenseClaimApprovalMail(
                        $expenseclaim,
                        'Hello Finance Approver',
                        'Expense Claim Pending Finance Approval'
                    ));
                }

                return back()->with('success', 'Claim approved successfully and forwarded to finance team for approval.');
            } else {
                $updateData['status'] = 'rejected';
                $expenseclaim->update($updateData);

                ExpenseClaimHistory::create([
                    'expense_claim_id' => $expenseclaim->id,
                    'action_by' =>  $currentUserId,
                    'action' => $rejectActionText,
                    'from_status' => $fromStatus,
                    'to_status' => 'rejected',
                    'comments' => $validatedData['rejection_reason'],
                ]);

                //mail to employee
                if ($expenseclaim->user) {
                    Mail::to($expenseclaim->user->email)->queue(new ExpenseClaimRejectedMail($expenseclaim, $validatedData['rejection_reason'], 'approval'));
                }

                return back()->with('success', 'Claim has been rejected and returned to employee.');
            }
        });
    }

    public function financeapprove(Request $request, ExpenseClaim $expenseclaim)
    {
        Gate::authorize('financeApprove', $expenseclaim);

        if ($expenseclaim->status != 'pending_finance_approval') {
            return back()->with('error', 'Claim is not in a status that allows finance approval.');
        }

        $validatedData = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'nullable|required_if:action,reject|string|max:1000'
        ]);

        $currentUserId = Auth::id();

        return DB::transaction(function () use ($expenseclaim, $validatedData, $currentUserId) {

            $updatedData['finance_approver_id'] = $currentUserId;
            $fromStatus = $expenseclaim->status;

            if ($validatedData['action']  == 'approve') {
                $updatedData['finance_approved_at'] = now();
                $updatedData['status'] = 'approved';

                $expenseclaim->update($updatedData);

                ExpenseClaimHistory::create([
                    'expense_claim_id' => $expenseclaim->id,
                    'action_by' =>  $currentUserId,
                    'action' => 'approved by finance approver',
                    'from_status' => $fromStatus,
                    'to_status' => 'approved',
                    'comments' => 'Claim approved by finance approver',
                ]);

                //mail to employee informing claim fully approved
                if ($expenseclaim->user) {
                    Mail::to($expenseclaim->user->email)->queue(new ExpenseClaimFullyApprovedMail($expenseclaim));
                }

                return back()->with('success', 'Claim has been successfully approved.');
            } else {
                $updatedData['status'] = 'rejected';

                $expenseclaim->update($updatedData);

                ExpenseClaimHistory::create([
                    'expense_claim_id' => $expenseclaim->id,
                    'action_by' =>  $currentUserId,
                    'action' => 'rejected by finance approver',
                    'from_status' => $fromStatus,
                    'to_status' => 'rejected',
                    'comments' => $validatedData['rejection_reason'],
                ]);

                //mail to employee
                if ($expenseclaim->user) {
                    Mail::to($expenseclaim->user->email)->queue(new ExpenseClaimRejectedMail($expenseclaim, $validatedData['rejection_reason'], 'finance-approval'));
                }

                return back()->with('success', 'Claim has been rejected and returned to employee.');
            }
        });
    }

    public function book(Request $request, ExpenseClaim $expenseclaim)
    {
        Gate::authorize('book', $expenseclaim);

        if ($expenseclaim->status != "approved") {
            return back()->with('error', 'Claim is not in a status that allows to book it.');
        }

        $validatedData = $request->validate([
            'reference_code' => 'required|string|max:100'
        ]);

        DB::transaction(function () use ($expenseclaim, $validatedData) {
            $currentUserId = Auth::id();
            $fromStatus = $expenseclaim->status;

            $expenseclaim->update([
                'booking_reference_code' => $validatedData['reference_code'],
                'status' => 'booked',
                'booked_by' => $currentUserId,
                'booked_at' => now()
            ]);

            ExpenseClaimHistory::create([
                'expense_claim_id' => $expenseclaim->id,
                'action_by' =>  $currentUserId,
                'action' => 'booked by finance team',
                'from_status' => $fromStatus,
                'to_status' => 'booked',
                'comments' => "Booked with Reference: {$validatedData['reference_code']}",
            ]);
        });
        return back()->with('success', 'Expense claim booked successfully.');
    }

    public function post(Request $request, ExpenseClaim $expenseclaim)
    {
        Gate::authorize('post', $expenseclaim);

        if ($expenseclaim->status != "booked") {
            return back()->with('error', 'Claim is not in a status that allows to post it.');
        }

        $validatedData = $request->validate([
            'payment_method' => 'required|string|max:100'
        ]);


        DB::transaction(function () use ($expenseclaim, $validatedData) {
            $currentUserId = Auth::id();
            $fromStatus = $expenseclaim->status;

            $expenseclaim->update([
                'payment_method' => $validatedData['payment_method'],
                'status'         => 'posted',
                'posted_by'      => $currentUserId,
                'posted_at'      => now(),
            ]);

            ExpenseClaimHistory::create([
                'expense_claim_id' => $expenseclaim->id,
                'action_by'        => $currentUserId,
                'action'           => 'posted by finance team',
                'from_status'      => $fromStatus,
                'to_status'        => 'posted',
                'comments'         => "Posted via Payment Method: {$validatedData['payment_method']}",
            ]);
        });

        return back()->with('success', 'Expense claim posted successfully.');
    }

    private function sendVerificationRequest(ExpenseClaim $claim, User $expenseuser, $module, $isUpdate = false, $isResubmitted = false)
    {
        $mailAction = 'created';
        if ($isResubmitted) {
            $mailAction = 'resubmitted';
        } elseif ($isUpdate) {
            $mailAction = 'updated';
        }

        //send confirmtion mail to the user
        if (!empty($expenseuser->employee->email)) {
            Mail::to($expenseuser->employee->email)->queue(new ExpenseClaimSubmittedMail($claim, $mailAction));
        }

        $verifierEmails = User::where('id', '!=',  $expenseuser->id)
            ->whereHas('roles', function ($query) use ($expenseuser, $module) {
                $query->where('company_role_user.company_id', $expenseuser->employee->company->id)
                    ->where('company_role_user.module_id', $module->id)
                    ->where('roles.code', 'verifier');
            })->pluck('email')
            ->filter()
            ->toArray();

        if (!empty($verifierEmails)) {
            Mail::to($verifierEmails)->queue(new ExpenseClaimPendingVerificationMail($claim, $mailAction));
        }
    }

    public function getExchangeRate(Request $request)
    {
        $request->validate([
            'from_currency_id' => 'required',
            'to_currency_id'   => 'required',
            'date'             => 'nullable|date',
        ]);

        // If both currencies are identical
        if ($request->from_currency_id == $request->to_currency_id) {
            return response()->json(['rate' => 1.000000]);
        }

        $rate = DB::table('currency_exchange_rates')
            ->where('from_currency_id', $request->from_currency_id)
            ->where('to_currency_id', $request->to_currency_id)
            ->whereDate('effective_date', '<=', $request->date ?? now())
            ->orderBy('effective_date', 'desc')
            ->value('rate');



        return response()->json([
            'rate' => $rate ? (float) $rate : 1.000000
        ]);
    }

    public function downloadAttachment(ExpenseClaimItem $item)
    {
        if (!$item->attachment_path || !Storage::disk('local')->exists($item->attachment_path)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk('local')->path($item->attachment_path);

        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . ($item->attachment_ref ?? 'receipt') . '"'
        ]);
    }
}
