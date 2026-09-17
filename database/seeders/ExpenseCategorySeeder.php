<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'      => 'Travel & Accommodation',
                'code'      => 'GL-6101',
                'is_active' => true,
            ],
            [
                'name'      => 'Meals & Entertainment',
                'code'      => 'GL-6102',
                'is_active' => true,
            ],
            [
                'name'      => 'Telecommunication & Internet',
                'code'      => 'GL-6103',
                'is_active' => true,
            ],
            [
                'name'      => 'Office Supplies & Stationery',
                'code'      => 'GL-6104',
                'is_active' => true,
            ],
            [
                'name'      => 'Training & Professional Development',
                'code'      => 'GL-6105',
                'is_active' => true,
            ],
            [
                'name'      => 'Software & Subscriptions',
                'code'      => 'GL-6106',
                'is_active' => true,
            ],
            [
                'name'      => 'Miscellaneous Expenses',
                'code'      => 'GL-6199',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}