<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'owner_id',
        'unit_id',
        'expense_id',
        'direction',
        'amount',
        'transaction_date',
        'value_date',
        'reference_number',
        'purpose_code',
        'counterparty_name',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'value_date' => 'date',
        'raw_payload' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Kada se kreira transakcija sa unit_id i direction=credit, upiši u ledger
        static::created(function (BankTransaction $transaction) {
            if ($transaction->unit_id && $transaction->direction === 'credit') {
                // Proveri da li već postoji zapis u ledger-u za ovu transakciju
                $existingEntry = UnitLedger::where('reference_type', 'bank_transaction')
                    ->where('reference_id', $transaction->id)
                    ->first();
                
                if (!$existingEntry) {
                    UnitLedger::createPayment(
                        $transaction->unit_id,
                        $transaction->transaction_date->toDateString(),
                        (float) $transaction->amount,
                        "Uplata - " . ($transaction->counterparty_name ?: 'Nepoznato'),
                        'bank_transaction',
                        $transaction->id
                    );
                }
            }
        });

        // Kada se ažurira transakcija i doda unit_id
        static::updated(function (BankTransaction $transaction) {
            if ($transaction->unit_id && $transaction->direction === 'credit' && $transaction->wasChanged('unit_id')) {
                // Obriši stari zapis ako postoji
                UnitLedger::where('reference_type', 'bank_transaction')
                    ->where('reference_id', $transaction->id)
                    ->delete();
                
                // Kreiraj novi
                UnitLedger::createPayment(
                    $transaction->unit_id,
                    $transaction->transaction_date->toDateString(),
                    (float) $transaction->amount,
                    "Uplata - " . ($transaction->counterparty_name ?: 'Nepoznato'),
                    'bank_transaction',
                    $transaction->id
                );
            }
        });

        // Kada se briše transakcija, obriši i ledger zapis
        static::deleted(function (BankTransaction $transaction) {
            UnitLedger::where('reference_type', 'bank_transaction')
                ->where('reference_id', $transaction->id)
                ->delete();
        });
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
