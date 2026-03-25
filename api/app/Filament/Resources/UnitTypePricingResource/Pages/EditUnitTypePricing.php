<?php

namespace App\Filament\Resources\UnitTypePricingResource\Pages;

use App\Filament\Resources\UnitTypePricingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnitTypePricing extends EditRecord
{
    protected static string $resource = UnitTypePricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
