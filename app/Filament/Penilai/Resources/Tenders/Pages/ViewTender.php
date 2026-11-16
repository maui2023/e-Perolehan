<?php

namespace App\Filament\Penilai\Resources\Tenders\Pages;

use App\Filament\Penilai\Resources\Tenders\TenderResource;
use App\Services\SealedBiddingService;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTender extends ViewRecord
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        $tender = $this->getRecord();
        $user = auth()->user();

        $actions = [];

        // Dual witness affirmation action if user is saksi_pembuka or admin
        if ($user && $user->isSaksiPembuka() && $tender->status === 'closed' && ! $tender->isBoxOpened()) {
            $hasWitnessed = $tender->witnesses()->where('user_id', $user->id)->exists();
            if (! $hasWitnessed) {
                $actions[] = Action::make('witness_open')
                    ->label('Sahkan Saksi Pembukaan Peti')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Pengesahan Saksi Digital Pembukaan Peti Tender')
                    ->modalDescription("Anda mengesahkan secara rasmi bahawa tarikh tutup bidaan bagi tender {$tender->reference_no} telah dicapai dan peti tender bersedia dibuka.")
                    ->action(function () use ($tender, $user) {
                        app(SealedBiddingService::class)->affirmWitnessOpening($tender, $user);
                        $this->redirect(TenderResource::getUrl('view', ['record' => $tender->id]));
                    });
            }
        }

        return $actions;
    }
}
