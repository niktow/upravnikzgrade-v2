<?php

namespace App\Filament\Resources\MaintenanceProgramResource\Pages;

use App\Filament\Resources\MaintenanceProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaintenancePrograms extends ListRecords
{
    protected static string $resource = MaintenanceProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
