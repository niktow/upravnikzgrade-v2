<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_community_id',
        'document_id',
        'inspection_type',
        'conducted_by',
        'scheduled_at',
        'conducted_at',
        'status',
        'findings',
    ];

    protected $casts = [
        'scheduled_at' => 'date',
        'conducted_at' => 'date',
    ];

    /**
     * Stambena zajednica kojoj pripada inspekcija
     */
    public function housingCommunity(): BelongsTo
    {
        return $this->belongsTo(HousingCommunity::class);
    }

    /**
     * Povezani dokument
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Tip inspekcije - lokalizovan naziv
     */
    public function getInspectionTypeLabelAttribute(): string
    {
        return match($this->inspection_type) {
            'fire_safety' => 'Požarna zaštita',
            'electrical' => 'Elektro pregled',
            'gas' => 'Gasna instalacija',
            'building' => 'Građevinska',
            'health' => 'Sanitarna',
            'elevator' => 'Lift',
            'lightning' => 'Gromobran',
            'chimney' => 'Dimnjak',
            'other' => 'Ostalo',
            default => $this->inspection_type
        };
    }

    /**
     * Status inspekcije - lokalizovan naziv
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Zakazano',
            'completed' => 'Završeno',
            'passed' => 'Položeno',
            'failed' => 'Palo',
            'cancelled' => 'Otkazano',
            default => $this->status
        };
    }
}
