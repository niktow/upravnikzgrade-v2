<?php

namespace Database\Seeders;

use App\Models\UnitTypePricing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitTypePricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prices = [
            [
                'unit_type' => 'stan',
                'monthly_fee' => 2000.00,
                'fee_per_sqm' => null,
                'description' => 'Standardna mesečna naknada za stanove',
                'is_active' => true,
            ],
            [
                'unit_type' => 'lokal',
                'monthly_fee' => 1000.00,
                'fee_per_sqm' => null,
                'description' => 'Standardna mesečna naknada za lokale',
                'is_active' => true,
            ],
            [
                'unit_type' => 'garaza',
                'monthly_fee' => 500.00,
                'fee_per_sqm' => null,
                'description' => 'Standardna mesečna naknada za garaže',
                'is_active' => true,
            ],
            [
                'unit_type' => 'ostava',
                'monthly_fee' => 300.00,
                'fee_per_sqm' => null,
                'description' => 'Standardna mesečna naknada za ostave',
                'is_active' => true,
            ],
        ];

        foreach ($prices as $price) {
            UnitTypePricing::updateOrCreate(
                [
                    'unit_type' => $price['unit_type'],
                    'housing_community_id' => null, // Globalne cene
                ],
                $price
            );
        }
    }
}
