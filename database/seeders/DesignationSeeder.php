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
            ['name' => 'Senior Manager', 'code' => 'SR_MGR'],
            ['name' => 'HR Manager', 'code' => 'HR_MGR'],
            ['name' => 'Finance Manager', 'code' => 'FN_MGR'],
            ['name' => 'Senior Developer', 'code' => 'SR_DEV'],
        ];

        foreach ($designations as $desig) {
            Designation::updateOrCreate(
                ['code' => $desig['code']],
                $desig
            );
        }
    }
}
