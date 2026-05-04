<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ParentModule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hr = ParentModule::create(['name' => 'Human Resource', 'order' => 1]);
        //Module::create(['parent_id' => $hr->id, 'name' => 'Employee Onboarding', 'order' => 1]);
        //Module::create(['parent_id' => $hr->id, 'name' => 'User Onboarding', 'order' => 1]);

        $finance = ParentModule::create(['name' => 'Finance', 'order' => 2]);
        //Module::create(['parent_id' => $finance->id, 'name' => 'Payroll', 'order' => 1]);

        $inventory = ParentModule::create(['name' => 'Inventory', 'order' => 3]);
        //Module::create(['parent_id' => $inventory->id, 'name' => 'Stock Items', 'order' => 1]);
    }
}
