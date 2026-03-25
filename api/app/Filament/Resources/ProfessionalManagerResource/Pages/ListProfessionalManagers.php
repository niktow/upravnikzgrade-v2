<?php

namespace App\Filament\Resources\ProfessionalManagerResource\Pages;

use App\Filament\Resources\ProfessionalManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfessionalManagers extends ListRecords
{
    protected static string $resource = ProfessionalManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
