<?php

namespace App\Filament\Resources\HousingCommunityResource\Pages;

use App\Filament\Resources\HousingCommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHousingCommunities extends ListRecords
{
    protected static string $resource = HousingCommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
