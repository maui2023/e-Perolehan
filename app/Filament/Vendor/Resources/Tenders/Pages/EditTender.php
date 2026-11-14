<?php

namespace App\Filament\Vendor\Resources\Tenders\Pages;

use App\Filament\Vendor\Resources\Tenders\TenderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTender extends EditRecord
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
