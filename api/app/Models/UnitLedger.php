<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitLedger extends Model
{
    use HasFactory;

    protected $table = 'unit_ledger';

    protected $fillable = [
        'unit_id',
        'date',
        'type',
        'description',
        'amount',
        'reference_type',
        'reference_id',
        'period',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relacija sa stanom
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope za zaduženja
     */
    public function scopeCharges($query)
    {
        return $query->where('type', 'charge');
    }

    /**
     * Scope za uplate
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Izračunaj saldo za stan
     */
    public static function getBalanceForUnit(int $unitId): float
    {
        $charges = self::where('unit_id', $unitId)
            ->where('type', 'charge')
            ->sum('amount');

        $payments = self::where('unit_id', $unitId)
            ->where('type', 'payment')
            ->sum('amount');

        return (float) $charges - (float) $payments;
    }

    /**
     * Dohvati detaljan pregled salda
     */
    public static function getBalanceDetails(int $unitId): array
    {
        $charges = self::where('unit_id', $unitId)
            ->where('type', 'charge')
            ->sum('amount');

        $payments = self::where('unit_id', $unitId)
            ->where('type', 'payment')
            ->sum('amount');

        $lastEntry = self::where('unit_id', $unitId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        return [
            'total_charges' => (float) $charges,
            'total_payments' => (float) $payments,
            'current_balance' => (float) $charges - (float) $payments,
            'last_entry' => $lastEntry,
        ];
    }

    /**
     * Kreiraj stavku zaduženja
     */
    public static function createCharge(
        int $unitId,
        string $date,
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $period = null
    ): self {
        return self::create([
            'unit_id' => $unitId,
            'date' => $date,
            'type' => 'charge',
            'description' => $description,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'period' => $period,
        ]);
    }

    /**
     * Kreiraj stavku uplate
     */
    public static function createPayment(
        int $unitId,
        string $date,
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): self {
        return self::create([
            'unit_id' => $unitId,
            'date' => $date,
            'type' => 'payment',
            'description' => $description,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }
}
