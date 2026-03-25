<?php

namespace App\Filament\Resources\ProfessionalManagerResource\Pages;

use App\Filament\Resources\ProfessionalManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfessionalManager extends EditRecord
{
    protected static string $resource = ProfessionalManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
