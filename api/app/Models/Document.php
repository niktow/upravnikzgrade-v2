<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'title',
        'category',
        'storage_path',
        'issued_at',
        'metadata',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Polimorfna relacija - dokument može pripadati bilo kom entitetu
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Inspekcije povezane sa ovim dokumentom
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Skupštine povezane sa ovim dokumentom
     */
    public function assemblyMeetings(): HasMany
    {
        return $this->hasMany(AssemblyMeeting::class);
    }

    /**
     * Kategorija dokumenta - lokalizovan naziv
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'contract' => 'Ugovor',
            'invoice' => 'Račun',
            'report' => 'Izveštaj',
            'minutes' => 'Zapisnik',
            'certificate' => 'Sertifikat/Atest',
            'decision' => 'Odluka',
            'notice' => 'Obaveštenje',
            'legal' => 'Pravni dokument',
            'technical' => 'Tehnička dokumentacija',
            'financial' => 'Finansijski dokument',
            'other' => 'Ostalo',
            default => $this->category ?? 'Nekategorisan'
        };
    }

    /**
     * Puna putanja do fajla
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->storage_path);
    }

    /**
     * Da li fajl postoji na disku
     */
    public function getFileExistsAttribute(): bool
    {
        return file_exists($this->full_path);
    }

    /**
     * Veličina fajla (formatirana)
     */
    public function getFileSizeAttribute(): string
    {
        if (!$this->file_exists) {
            return '-';
        }
        
        $bytes = filesize($this->full_path);
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Ekstenzija fajla
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->storage_path, PATHINFO_EXTENSION);
    }
}
