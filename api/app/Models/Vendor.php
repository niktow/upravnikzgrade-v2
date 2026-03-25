<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'tax_number',
        'registration_number',
        'contact_name',
        'email',
        'phone',
        'address',
        'bank_account',
        'bank_name',
        'notes',
        'status',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
