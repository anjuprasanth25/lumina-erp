<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Omnicom Media Group',
                'legal_name' => 'Omnicom Media Group LLC',
                'code' => 'OMG',
                'currency_id' => 1,
                'base_currency_id' => 1,
                'country_id' => 1
            ],
            [
                'name' => 'OMD Dubai',
                'legal_name' => 'Optimun Media Direction LLC',
                'code' => 'OMD',
                'currency_id' => 1,
                'base_currency_id' => 1,
                'country_id' => 1
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['code' => $company['code']],
                $company
            );
        }
    }
}
