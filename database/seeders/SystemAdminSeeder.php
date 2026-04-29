<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $desig = Designation::create([
            'name' => 'System Administrator',
            'code' => 'SYS_ADM'
        ]);
        $dept = Department::where('code', 'IT')->first();
        $country = Country::first();

        $employee = Employee::create([
            'code' => 'ADM001',
            'name' => 'System Administrator',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@luminaerp.com',
            'company_id' => $company->id,
            'designation_id' => $desig->id,
            'department_id' => $dept->id,
            'country_id' => $country->id,
            'is_system_record' => true
        ]);

        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@luminaerp.com',
            'password' => Hash::make('P@ss_Luma625!@#$$'),
            'employee_id' => $employee->id
        ]);
    }
}
