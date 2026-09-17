<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\CurrencyExchangeRate;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencyExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseCurrency = Currency::where('code', 'AED')->first();

        $currencies = [
            'USD' => 3.672500, // 1 USD = 3.6725 AED
            'EUR' => 3.985000, // 1 EUR = 3.9850 AED
            'GBP' => 4.651000, // 1 GBP = 4.6510 AED
            'INR' => 0.044100, // 1 INR = 0.0441 AED
            'CAD' => 2.710000, // 1 CAD = 2.7100 AED
        ];

        for ($i = 0; $i < 6; $i++) {
            $effectiveDate = Carbon::now()->startOfMonth()->subMonths($i)->format('Y-m-d');

            foreach ($currencies as $currency => $baseRate) {
                $fromCurrencyId = Currency::where('code', $currency)->first();

                if ($fromCurrencyId && $baseCurrency) {
                    $monthlyVariance = (rand(-10, 10) / 1000);
                    $finalRate = round($baseRate + $monthlyVariance, 6);

                    CurrencyExchangeRate::updateorCreate(
                        [
                            'from_currency_id' => $fromCurrencyId->id,
                            'to_currency_id'   => $baseCurrency->id,
                            'effective_date'   => $effectiveDate,
                        ],
                        ['rate' => $finalRate]
                    );
                }
            }
        }
    }
}