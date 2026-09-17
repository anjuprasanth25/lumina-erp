<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'code' => 'PRJ-2026-001',
                'name' => 'ERP System Migration',
                'is_active' => true,
            ],
            [
                'code' => 'PRJ-2026-002',
                'name' => 'Mobile App Revamp',
                'is_active' => true,
            ],
            [
                'code' => 'PRJ-2026-003',
                'name' => 'Annual HR Onboarding Portal',
                'is_active' => true,
            ],
            [
                'code' => 'PRJ-2026-004',
                'name' => 'Cloud Infrastructure Upgrade',
                'is_active' => true,
            ],
        ];

        foreach ($projects as $project) {
            Project::updateorCreate(['code' => $project['code']], $project);
        }
    }
}
