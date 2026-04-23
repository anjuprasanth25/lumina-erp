<?php

namespace Database\Seeders;

use App\Models\BillingType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $billingtypes = [
            ['name' => 'Billable', 'code' => 'BILL'],
            ['name' => 'Non-Billable', 'code' => 'NON_BILL'],
            ['name' => 'Contractor', 'code' => 'CONT'],
            ['name' => 'Internal Project', 'code' => 'INT_PROJ'],
            ['name' => 'Retainer', 'code' => 'RET']
        ];

        foreach ($billingtypes as $bill) {
            BillingType::updateOrCreate(
                ['code' => $bill['code']],
                $bill
            );
        }
    }
}
