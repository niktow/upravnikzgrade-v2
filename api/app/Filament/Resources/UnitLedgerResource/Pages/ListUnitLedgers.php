<?php

namespace App\Filament\Resources\UnitLedgerResource\Pages;

use App\Filament\Resources\UnitLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitLedgers extends ListRecords
{
    protected static string $resource = UnitLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
