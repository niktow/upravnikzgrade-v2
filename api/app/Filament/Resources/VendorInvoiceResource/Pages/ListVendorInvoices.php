<?php

namespace App\Filament\Resources\VendorInvoiceResource\Pages;

use App\Filament\Resources\VendorInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorInvoices extends ListRecords
{
    protected static string $resource = VendorInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
