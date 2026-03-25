<?php

namespace App\Filament\Resources\AssemblyMeetingResource\Pages;

use App\Filament\Resources\AssemblyMeetingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAssemblyMeetings extends ListRecords
{
    protected static string $resource = AssemblyMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['housingCommunity']);
    }
}
