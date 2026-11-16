<?php

namespace App\Filament\Resources\Bids\Pages;

use App\Filament\Resources\Bids\BidResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewBid extends ViewRecord
{
    protected static string $resource = BidResource::class;

    protected function getHeaderActions(): array
    {
        $bid = $this->getRecord();

        $actions = [];

        if (filled($bid->submission_slip_pdf_path)) {
            $actions[] = Action::make('download_slip')
                ->label('Muat Turun Slip Bidaan (PDF)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('vendor.bids.slip', $bid->id), shouldOpenInNewTab: true);
        }

        return $actions;
    }
}
