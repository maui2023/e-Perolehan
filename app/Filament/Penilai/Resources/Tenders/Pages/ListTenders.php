<?php

namespace App\Filament\Penilai\Resources\Tenders\Pages;

use App\Filament\Penilai\Resources\Tenders\TenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTenders extends ListRecords
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
