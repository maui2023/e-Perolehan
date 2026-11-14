<?php

namespace App\Filament\Penilai\Resources\Tenders\Pages;

use App\Filament\Penilai\Resources\Tenders\TenderResource;
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
