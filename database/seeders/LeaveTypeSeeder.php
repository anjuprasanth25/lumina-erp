<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leavetypes = [
            [
                'name' => 'Annual Leave',
                'default_days_per_year' => 30,
                'is_paid' => 1
            ],
            [
                'name' => 'Sick Leave',
                'default_days_per_year' => 15,
                'is_paid' => true,
            ],
            [
                'name' => 'Casual Leave',
                'default_days_per_year' => 7,
                'is_paid' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'default_days_per_year' => 90,
                'is_paid' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'default_days_per_year' => 5,
                'is_paid' => true,
            ],
        ];

        foreach ($leavetypes as $type) {
            $type['slug'] = Str::slug($type['name']);

            LeaveType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
