<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_community_id',
        'identifier',
        'type',
        'floor',
        'area',
        'occupant_count',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class, 'owner_unit')
            ->withPivot(['ownership_share', 'starts_at', 'ends_at', 'obligation_notes'])
            ->withTimestamps();
    }

    /**
     * Stavke kartice stana (ledger)
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(UnitLedger::class);
    }

    /**
     * Izračunaj trenutni saldo
     */
    public function getCurrentBalanceAttribute(): float
    {
        return UnitLedger::getBalanceForUnit($this->id);
    }
}
