<?php

namespace App\Http\Controllers;

use App\Mail\SendUserCredentials;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Str;

use function Pest\Laravel\json;
use function Termwind\render;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with([
            'employee',
            'employee.company:id,name',
            'employee.designation:id,name',
            'employee.department:id,name'
        ])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()->toArray();


        return Inertia::render('User/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
            'flash' => [
                'success' => $request->session()->get('success')
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render(
            'User/Create',
            [
                'lookups' => [
                    'employees' => Employee::select('id', 'name', 'email', 'company_id', 'designation_id', 'department_id')
                        ->where('is_system_record', 0)
                        ->doesntHave('user')
                        ->with('company', 'designation', 'department')
                        ->orderBy('name')->get(),
                    'companies' => Company::select('id', 'name')->orderBy('name')->get(),
                    'roles' => Role::select('id', 'name', 'code')->orderBy('name')->get(),
                    'modules' => Module::select('id', 'name', 'is_default')->orderBy('name')->get()
                ]
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company_access_blocks' => 'required|array|min:1',
            'company_access_blocks.*.company_id' => 'required|exists:companies,id',
            'company_access_blocks.*.permissions' => 'required|array|min:1',
            'company_access_blocks.*.permissions.*.module_id' => 'required|exists:modules,id',
            'company_access_blocks.*.permissions.*.role_id' => 'required|exists:roles,id'
        ]);



        try {
            DB::transaction(function () use ($validatedData) {
                //check if the user exists
                $user = User::where('employee_id', $validatedData['employee_id'])->first();


                if (!$user) {
                    $plainPassword = Str::random(12);
                    $initialPassword = Hash::make($plainPassword);

                    $user = User::create([
                        'name' => $validatedData['name'],
                        'email' => $validatedData['email'],
                        'password' => $initialPassword,
                        'employee_id' => $validatedData['employee_id'],
                        'is_active' => true,
                        'is_admin' => false
                    ]);

                    //send mail
                    $this->sendPasswordSetupEmail($user);
                } else {
                    $user->update([
                        'name' => $validatedData['name'],
                        'email' => $validatedData['email']
                    ]);
                }

                DB::table('company_role_user')->where('user_id', $user->id)->delete();

                $recordsToInsert = [];
                $now = now();

                foreach ($validatedData['company_access_blocks'] as $block) {
                    foreach ($block['permissions'] as $permission) {
                        $recordsToInsert[] = [
                            'user_id' => $user->id,
                            'company_id' => $block['company_id'],
                            'role_id' => $permission['role_id'],
                            'module_id' => $permission['module_id'],
                            'created_at' => $now,
                            'updated_at' => $now

                        ];
                    }
                }

                if (!empty($recordsToInsert)) {
                    DB::table('company_role_user')->insert($recordsToInsert);
                }
            });
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Database Insertion Failed: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('user.index')
            ->with('success', 'User access permissions saved and setup email sent!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load([
            'employee',
            'employee.company:id,name',
            'employee.department:id,name',
            'employee.designation:id,name',
            'companies'
        ]);

        $modules = Module::pluck('name', 'id');
        $roles = Role::pluck('name', 'id');

        $companyAccessBlocks = $user->companies
            ->groupBy('id')
            ->map(function ($group, $companyId) use ($user, $modules, $roles) {
                $firstCompany = $group->first();
                return [
                    'id' => 'company-' . $companyId,
                    'company_id' => $companyId,
                    'company_name' => $firstCompany->name,
                    'is_primary' => (string) $user->employee->company_id === (string) $companyId,
                    'permissions' => $group->map(function ($perm) use ($modules, $roles) {
                        $pivot = $perm->pivot;
                        return [
                            'id' => $item->pivot->id ?? 'perm-' . $pivot->module_id,
                            'module_id' => $pivot->module_id,
                            'module_name' => $modules[$pivot->module_id],
                            'role_name' => $roles[$pivot->role_id]
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();

        return Inertia::render('User/Show', [
            'user' => $user,
            'companyAccessBlocks' => $companyAccessBlocks
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {

        $user->load([
            'employee',
            'employee.company:id,name',
            'employee.department:id,name',
            'employee.designation:id,name',
            'companies'
        ]);

        $companyAccessBlocks = $user->companies
            ->groupBy('id')
            ->map(function ($items, $companyId) {
                return [
                    'id' => 'company-' . $companyId,
                    'company_id' => (string) $companyId,
                    'permissions' => $items->map(function ($item, $index) {
                        return [
                            'id' => 'perm-' . ($item->pivot->id ?? ($item->pivot->module_id . '-' . $index)),
                            'module_id' => $item->pivot->module_id,
                            'role_id' => $item->pivot->role_id
                        ];
                    })->values()->toArray()
                ];
            })->values()->toArray();

        if (empty($companyAccessBlocks)) {
            $companyAccessBlocks = [
                [
                    'id' => 'company-default',
                    'company_id' => '',
                    'permissions' => [
                        [
                            'id' => 'perm-default',
                            'module_id' => '',
                            'role_id' => '',
                        ]
                    ]
                ]
            ];
        }

        return Inertia::render(
            'User/Edit',
            [
                'user' => $user,
                'companyAccessBlocks' => $companyAccessBlocks,
                'companies' => Company::select('id', 'name')->get(),
                'roles' => Role::select('id', 'name')->get(),
                'modules' => Module::select('id', 'name', 'is_default')->get()
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'company_access_blocks' => 'required|array|min:1',
            'company_access_blocks.*.company_id' => 'required|exists:companies,id',
            'company_access_blocks.*.permissions' => 'required|array|min:1',
            'company_access_blocks.*.permissions.*.module_id' => 'required|exists:modules,id',
            'company_access_blocks.*.permissions.*.role_id' => 'required|exists:roles,id'
        ]);

        try {
            DB::transaction(function () use ($validatedData, $user) {

                if ($user) {
                    DB::table('company_role_user')->where('user_id', $user->id)->delete();

                    $recordsToInsert = [];
                    $now = now();

                    foreach ($validatedData['company_access_blocks'] as $block) {
                        foreach ($block['permissions'] as $permission) {
                            $recordsToInsert[] = [
                                'user_id' => $user->id,
                                'company_id' => $block['company_id'],
                                'role_id' => $permission['role_id'],
                                'module_id' => $permission['module_id'],
                                'created_at' => $now,
                                'updated_at' => $now

                            ];
                        }
                    }
                    if (!empty($recordsToInsert)) {
                        DB::table('company_role_user')->insert($recordsToInsert);
                    }
                }
            });
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Database Insertion Failed: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('user.index')
            ->with('success', 'User access permissions saved!');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->update(['is_active' => 0]);

        return redirect()->route('user.index')
            ->with('success', 'User workspace status has been successfully deactivated.');
    }

    private function sendPasswordSetupEmail(User $user): void
    {
        $expireMinutes = config('auth.passwords.users.expire', 4320);

        //generate laravel password reset token
        $token = Password::createToken($user);

        // In UserController@store (Generating the link):
        $setPasswordUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes($expireMinutes), // Expiration time
            [
                'token' => $token,
                'email' => $user->email,
            ]
        );

        Mail::to($user->email)->send(new SendUserCredentials($user, $setPasswordUrl));
    }

    public function resendLink(User $user)
    {
        try {
            $this->sendPasswordSetupEmail($user);
            return back()->with('success', "Password setup link resent to {$user->email}.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }
}