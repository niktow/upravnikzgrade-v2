<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_community_id',
        'expense_category_id',
        'contract_id',
        'unit_id',
        'type',
        'status',
        'amount',
        'incurred_on',
        'due_date',
        'description',
        'document_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'incurred_on' => 'date',
        'due_date' => 'date',
    ];

    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
