<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_community_id',
        'created_by',
        'title',
        'content',
        'priority',
        'type',
        'is_pinned',
        'is_active',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Stambena zajednica kojoj pripada oglas
     */
    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    /**
     * Korisnik koji je kreirao oglas
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope za aktivne oglase
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope za prikacene oglase prvo
     */
    public function scopePinnedFirst($query)
    {
        return $query->orderByDesc('is_pinned')->orderByDesc('created_at');
    }

    /**
     * Labela za prioritet
     */
    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            'low' => 'Nizak',
            'normal' => 'Normalan',
            'high' => 'Visok',
            'urgent' => 'Hitan',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    /**
     * Labela za tip
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'info' => 'Informacija',
            'warning' => 'Upozorenje',
            'maintenance' => 'Održavanje',
            'meeting' => 'Sastanak',
            'financial' => 'Finansije',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}
