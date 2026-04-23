<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exchangerates = [
            ['year' => 2026, 'month' => 4, 'from_currency_id' => 1, 'to_currency_id' => 2, 'rate' => 0.27],
            ['year' => 2026, 'month' => 4, 'from_currency_id' => 1, 'to_currency_id' => 3, 'rate' => 25.36],
        ];

        foreach ($exchangerates as $rate) {
            ExchangeRate::updateOrCreate(
                [
                    'year' => $rate['year'],
                    'month' => $rate['month'],
                    'from_currency_id' => $rate['from_currency_id'],
                    'to_currency_id' => $rate['to_currency_id']
                ],
                ['rate' => $rate['rate']]
            );


        }
    }
}
