<?php

namespace App\Http\Controllers;

use App\Models\BillingType;
use App\Models\Company;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Doctrine\DBAL\SQL\Builder\CreateSchemaObjectsSQLBuilder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Contracts\Service\Attribute\Required;

class EmployeeOnboardingController extends Controller
{

    public function index(Request $request)
    {
        $employees = Employee::with([
            'company:id,name',
            'department:id,name',
            'designation:id,name'
        ])->select([
            'id',
            'code',
            'first_name',
            'middle_name',
            'last_name',
            'name',
            'email',
            'date_of_joining',
            'company_id',
            'department_id',
            'designation_id',
            'is_system_record',
            'is_active'
        ])->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Employee/Index', [
            'employees' => $employees,
            'flash' => [
                'success' => $request->session()->get('success')
            ]
        ]);
    }


    public function create()
    {
        $lineManager = Employee::where('id', '!=', auth()->user()->employee_id)
            ->with('designation')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name . ' - ' . $employee->designation->name
                ];
            })->toArray();


        return Inertia::render('Employee/Create', [
            'lookups' => [
                'companies' => Company::select('id', 'name')->orderBy('name')->get(),
                'departments' => Department::select('id', 'name')->orderBy('name')->get(),
                'designation' => Designation::select('id', 'name')->orderBy('name')->get(),
                'lineManager' => $lineManager,
                'countries' => Country::select('id', 'name')->orderBy('name')->get(),
                'billingtypes' => BillingType::select('id', 'name')->orderBy('name')->get()
            ]
        ]);
    }

    public function store(Request $request)
    {
        //server side validation
        $validatedData = $request->validate([
            'code' => 'required|string|unique:employees,code|max:50',
            'email' => 'required|email|unique:employees,email|max:255',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'dob' => 'required|date',
            'gender' => 'required|in:M,F',
            'family_status' => 'required|string',
            'date_of_joining' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'line_manager_id' => 'nullable|exists:employees,id',
            'country_id' => 'required|exists:countries,id',
            'billing_type_id' => 'required|exists:billing_types,id',
        ]);

        try {
            //Safe Database Write using a Transaction Block
            DB::transaction(function () use ($validatedData) {
                $middle = $validatedData['middle_name'] ? $validatedData['middle_name'] . ' ' : '';
                $validatedData['name'] = $validatedData['first_name'] . ' ' . $middle . $validatedData['last_name'];

                $employeeData = collect($validatedData)->except(['dob', 'gender', 'family_status'])->toArray();

                $employee = Employee::create($employeeData);
                $employee->details()->create([
                    'dob' => $validatedData['dob'],
                    'gender' => $validatedData['gender'],
                    'family_status' => $validatedData['family_status'],
                ]);
            });
        } catch (Exception $e) {
            //dd($e->getMessage(), $e->getTraceAsString());
            return back()->withErrors(['error' => 'Database Insertion Failed: ' . $e->getMessage()])->withInput();
        }



        return redirect()->route('employee.index')
            ->with('success', 'Employee has been successfully onboarding into the platform!');
    }

    public function edit(Employee $employee)
    {
        $employee->load('details');


        return Inertia::render('Employee/Edit', [
            'employee' => $employee,
            'companies' => Company::all(['id', 'name']),
            'departments' => Department::all(['id', 'name']),
            'designation' => designation::all(['id', 'name']),
            'countries' => Country::all(['id', 'name']),
            'billingtypes' => BillingType::all(['id', 'name']),
            'linemanagers' => $this->getLineManagers()

        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|max:50|unique:employees,code,' . $employee->id,
            'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'dob' => 'required|date',
            'gender' => 'required|in:M,F',
            'family_status' => 'required|string',
            'date_of_joining' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'line_manager_id' => 'nullable|exists:employees,id',
            'country_id' => 'required|exists:countries,id',
            'billing_type_id' => 'required|exists:billing_types,id',
        ]);

        try {
            DB::transaction(function () use ($validatedData, $employee) {
                $middle = $validatedData['middle_name'] ? $validatedData['middle_name'] . ' ' : '';
                $validatedData['name'] = $validatedData['first_name'] . ' ' . $middle . $validatedData['last_name'];

                $employeeData = collect($validatedData)->except(['dob', 'gender', 'family_status'])->toArray();

                $employee->update($employeeData);

                $employee->details()->update([
                    'dob' => $validatedData['dob'],
                    'gender' => $validatedData['gender'],
                    'family_status' => $validatedData['family_status'],
                ]);
            });
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Database Insertion Failed: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('employee.index')
            ->with('success', 'Employee account has been successfully updated!');
    }

    public function view(Employee $employee)
    {
        // Eager load relationships so we can view text names instead onf database foreign IDs
        $employee->load(['department', 'company', 'designation', 'billingType', 'country', 'lineManager', 'details']);

        return Inertia::render(
            'Employee/Show',
            ['employee' => $employee]
        );
    }

    public function destroy(Employee $employee)
    {
        $employee->update(['is_active' => 0]);

        return redirect()->route('employee.index')
            ->with('success', 'Employee workspace status has been successfully deactivated.');
    }

    private function getLineManagers(): array
    {
        $currentEmployeeId = auth()->user()->employee_id ?? null;

        return
            Employee::where('id', '!=', $currentEmployeeId)
            ->with('designation')
            ->get()
            ->map(function ($employee) {
                $designation = $employee->designation?->name ?? 'No Designation';
                return [
                    'id' => $employee->id,
                    'name' => $employee->name . ' - ' . $designation
                ];
            })->toArray();
    }
}