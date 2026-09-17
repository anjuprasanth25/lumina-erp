<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\ExpenseType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $travelCategory  = ExpenseCategory::where('code', 'GL-6101')->first() ?? ExpenseCategory::first();
        $mealsCategory   = ExpenseCategory::where('code', 'GL-6102')->first() ?? ExpenseCategory::first();
        $telecomCategory = ExpenseCategory::where('code', 'GL-6103')->first() ?? ExpenseCategory::first();
        $officeCategory  = ExpenseCategory::where('code', 'GL-6104')->first() ?? ExpenseCategory::first();

        $expenseTypes = [
            [
                'name'                 => 'Airfare / Flight Tickets',
                'expense_category_id'  => $travelCategory?->id,
                'requires_attachment'  => true,
                'max_daily_limit'      => null,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Hotel & Lodging',
                'expense_category_id'  => $travelCategory?->id,
                'requires_attachment'  => true,
                'max_daily_limit'      => 500.00,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Taxi / Uber / Public Transport',
                'expense_category_id'  => $travelCategory?->id,
                'requires_attachment'  => false, // Receipts optional for small trips
                'max_daily_limit'      => 100.00,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Client Lunch / Dinner',
                'expense_category_id'  => $mealsCategory?->id,
                'requires_attachment'  => true,
                'max_daily_limit'      => 200.00,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Mobile / Internet Allowance',
                'expense_category_id'  => $telecomCategory?->id,
                'requires_attachment'  => true,
                'max_daily_limit'      => 150.00,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Office Stationery & Printing',
                'expense_category_id'  => $officeCategory?->id,
                'requires_attachment'  => true,
                'max_daily_limit'      => null,
                'is_active'            => true,
            ],
        ];

        foreach ($expenseTypes as $type) {
            ExpenseType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}