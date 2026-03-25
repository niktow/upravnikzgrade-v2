<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_community_id',
        'document_id',
        'scheduled_for',
        'location',
        'agenda',
        'status',
        'called_by',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
    ];

    /**
     * Stambena zajednica
     */
    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    /**
     * Povezani dokument (zapisnik)
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Status skupštine - lokalizovan naziv
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Zakazana',
            'in_progress' => 'U toku',
            'completed' => 'Održana',
            'cancelled' => 'Otkazana',
            'postponed' => 'Odložena',
            default => $this->status
        };
    }

    /**
     * Da li je skupština u budućnosti
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->scheduled_for->isFuture();
    }
}
