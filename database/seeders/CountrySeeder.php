<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'United Arab Emirates', 'iso_code' => 'AE', 'phone_code' => '971'],
            ['name' => 'Saudi Arabia', 'iso_code' => 'SA', 'phone_code' => '966'],
            ['name' => 'India', 'iso_code' => 'ND', 'phone_code' => '91'],
            // Add your primary countries here
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso_code' => $country['iso_code']],
                $country
            );
        }
    }
}
