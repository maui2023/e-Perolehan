<?php

namespace App\Filament\Vendor\Resources\Bids\Pages;

use App\Filament\Vendor\Resources\Bids\BidResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBids extends ListRecords
{
    protected static string $resource = BidResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
