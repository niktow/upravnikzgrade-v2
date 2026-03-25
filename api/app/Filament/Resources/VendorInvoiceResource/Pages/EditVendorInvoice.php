<?php

namespace App\Filament\Resources\VendorInvoiceResource\Pages;

use App\Filament\Resources\VendorInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorInvoice extends EditRecord
{
    protected static string $resource = VendorInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
