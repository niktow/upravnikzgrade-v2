<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'housing_community_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'description',
        'status',
        'paid_date',
        'expense_id',
        'notes',
        'document_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relacija sa dobavljačem
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relacija sa stambenom zajednicom
     */
    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    /**
     * Relacija sa troškom (kada se plati)
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Scope za pending račune
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope za odobrene račune
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope za plaćene račune
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope za dospele račune
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    /**
     * Da li je račun dospeo
     */
    public function isOverdue(): bool
    {
        return $this->due_date 
            && $this->due_date->isPast() 
            && !in_array($this->status, ['paid', 'cancelled']);
    }

    /**
     * Označi račun kao plaćen
     */
    public function markAsPaid(?string $paidDate = null, ?int $expenseId = null): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_date' => $paidDate ?? now(),
            'expense_id' => $expenseId,
        ]);
    }

    /**
     * Odobri račun
     */
    public function approve(): bool
    {
        return $this->update(['status' => 'approved']);
    }

    /**
     * Otkaži račun
     */
    public function cancel(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Label za status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Na čekanju',
            'approved' => 'Odobren',
            'paid' => 'Plaćen',
            'cancelled' => 'Otkazan',
            default => $this->status,
        };
    }

    /**
     * Boja za status
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }
}
