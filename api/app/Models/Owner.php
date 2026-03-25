<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'address',
        'national_id',
        'date_of_birth',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'owner_unit')
            ->withPivot(['ownership_share', 'starts_at', 'ends_at', 'obligation_notes'])
            ->withTimestamps();
    }
}
