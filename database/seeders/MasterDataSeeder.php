<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            DesignationSeeder::class,
            DepartmentSeeder::class,
            CountrySeeder::class,
            BillingTypeSeeder::class,
            ExchangeRateSeeder::class,
            ModuleSeeder::class,
            LeaveTypeSeeder::class

        ]);

        $this->call([
            CompanySeeder::class,
            SystemAdminSeeder::class
        ]);

    }
}
