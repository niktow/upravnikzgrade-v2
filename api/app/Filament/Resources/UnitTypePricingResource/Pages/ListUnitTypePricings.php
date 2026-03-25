<?php

namespace App\Filament\Resources\UnitTypePricingResource\Pages;

use App\Filament\Resources\UnitTypePricingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitTypePricings extends ListRecords
{
    protected static string $resource = UnitTypePricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
