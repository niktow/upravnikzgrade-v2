<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitTypePricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_community_id',
        'unit_type',
        'monthly_fee',
        'fee_per_sqm',
        'description',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'fee_per_sqm' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    /**
     * Pronađi aktivnu cenu za tip jedinice
     * Prvo traži specifičnu cenu za zajednicu, pa globalnu
     */
    public static function getPriceForUnit(Unit $unit, ?string $date = null): ?self
    {
        $date = $date ?? now()->toDateString();

        // Prvo traži cenu specifičnu za zajednicu
        $pricing = self::where('unit_type', $unit->type)
            ->where('housing_community_id', $unit->housing_community_id)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $date);
            })
            ->orderBy('valid_from', 'desc')
            ->first();

        // Ako nema, traži globalnu cenu (bez zajednice)
        if (!$pricing) {
            $pricing = self::where('unit_type', $unit->type)
                ->whereNull('housing_community_id')
                ->where('is_active', true)
                ->where(function ($query) use ($date) {
                    $query->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $date);
                })
                ->where(function ($query) use ($date) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', $date);
                })
                ->orderBy('valid_from', 'desc')
                ->first();
        }

        return $pricing;
    }

    /**
     * Izračunaj mesečnu naknadu za jedinicu
     */
    public function calculateFeeForUnit(Unit $unit): float
    {
        $fee = $this->monthly_fee ?? 0;

        // Ako postoji cena po m² i jedinica ima površinu
        if ($this->fee_per_sqm && $unit->area) {
            $fee += $this->fee_per_sqm * $unit->area;
        }

        return (float) $fee;
    }

    public static function getUnitTypes(): array
    {
        return [
            'stan' => 'Stan',
            'lokal' => 'Lokal',
            'garaza' => 'Garaža',
            'ostava' => 'Ostava',
        ];
    }
}
