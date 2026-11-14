<?php

namespace App\Filament\Vendor\Resources\Bids\Pages;

use App\Filament\Vendor\Resources\Bids\BidResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBid extends EditRecord
{
    protected static string $resource = BidResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
