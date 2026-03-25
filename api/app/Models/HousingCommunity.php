<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HousingCommunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address_line',
        'city',
        'postal_code',
        'registry_number',
        'tax_id',
        'bank_account_number',
        'contact_email',
        'contact_phone',
        'established_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'established_at' => 'date',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class, 'owner_unit')
            ->withPivot(['ownership_share', 'starts_at', 'ends_at'])
            ->withTimestamps();
    }
}
