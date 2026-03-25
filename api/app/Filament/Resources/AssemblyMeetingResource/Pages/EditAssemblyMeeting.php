<?php

namespace App\Filament\Resources\AssemblyMeetingResource\Pages;

use App\Filament\Resources\AssemblyMeetingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssemblyMeeting extends EditRecord
{
    protected static string $resource = AssemblyMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
