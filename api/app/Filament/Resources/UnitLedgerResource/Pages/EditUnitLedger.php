<?php

namespace App\Filament\Resources\UnitLedgerResource\Pages;

use App\Filament\Resources\UnitLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnitLedger extends EditRecord
{
    protected static string $resource = UnitLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
