<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UnitBillingStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_community_id',
        'unit_id',
        'period',
        'opening_balance',
        'charges',
        'payments',
        'closing_balance',
        'generated_at',
        'pdf_path',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'charges' => 'decimal:2',
        'payments' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'generated_at' => 'date',
    ];

    /**
     * Stambena zajednica
     */
    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    /**
     * Stan/jedinica
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Formatirani period (npr. "Januar 2026")
     */
    public function getPeriodLabelAttribute(): string
    {
        $date = Carbon::parse($this->period . '-01')->locale('sr_Latn');
        return ucfirst($date->translatedFormat('F Y'));
    }

    /**
     * Da li je stan u dugovanju
     */
    public function getIsInDebtAttribute(): bool
    {
        return $this->closing_balance > 0;
    }

    /**
     * Dohvati poslednji izvod za stan
     */
    public static function getLatestForUnit(int $unitId): ?self
    {
        return static::where('unit_id', $unitId)
            ->orderByDesc('period')
            ->first();
    }

    /**
     * Dohvati sve izvode za stan
     */
    public static function getForUnit(int $unitId)
    {
        return static::where('unit_id', $unitId)
            ->orderByDesc('period')
            ->get();
    }
}
