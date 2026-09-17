<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            ['name' => 'CEO', 'code' => 'CEO'],
            ['name' => 'COO', 'code' => 'COO'],
            ['name' => 'Director', 'code' => 'DIR'],
            ['name' => 'Senior Manager', 'code' => 'SR_MGR'],
            ['name' => 'Finance Director', 'code' => 'FN_DR'],
            ['name' => 'HR Director', 'code' => 'HR_DR'],
            ['name' => 'HR Manager', 'code' => 'HR_MGR'],
            ['name' => 'Finance Manager', 'code' => 'FN_MGR'],
            ['name' => 'Senior Developer', 'code' => 'SR_DEV'],
            ['name' => 'Finance Executive', 'code' => 'FN_EXE'],
            ['name' => 'IT Director', 'code' => 'IT_DR'],
            ['name' => 'IT Manager', 'code' => 'IT_MGR'],
            ['name' => 'Analyst', 'code' => 'ANALYST'],

        ];

        foreach ($designations as $desig) {
            Designation::updateOrCreate(
                ['code' => $desig['code']],
                $desig
            );
        }
    }
}
