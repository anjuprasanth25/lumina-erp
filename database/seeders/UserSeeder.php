<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'LumaLegend625',
            'email' => 'admin@luminaerp.com',
            'password' => Hash::make('P@ss_Luma625!@#$$')
        ]);
    }
}
