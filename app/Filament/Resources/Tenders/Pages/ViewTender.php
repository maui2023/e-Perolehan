<?php

namespace App\Filament\Resources\Tenders\Pages;

use App\Filament\Resources\Tenders\TenderResource;
use App\Models\Tender;
use App\Services\SealedBiddingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTender extends ViewRecord
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Tender $tender */
        $tender = $this->getRecord();
        $user = auth()->user();

        $actions = [];

        // Dual witness affirmation
        if ($user && in_array($user->role, ['admin', 'urus_setia', 'saksi_pembuka']) && $tender->status === 'closed' && ! $tender->isBoxOpened()) {
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
                        $result = app(SealedBiddingService::class)->affirmWitnessOpening($tender, $user);
                        Notification::make()
                            ->title('Pengesahan Saksi Berjaya')
                            ->body($result['message'])
                            ->success()
                            ->send();
                        $this->redirect(TenderResource::getUrl('view', ['record' => $tender->id]));
                    });
            }
        }

        // Download Notice PDF
        $actions[] = Action::make('notice_pdf')
            ->label('Notis Iklan PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->url(route('tenders.notice-pdf', $tender->id), shouldOpenInNewTab: true);

        // Download Opening Slip PDF
        if ($tender->isBoxOpened()) {
            $actions[] = Action::make('opening_slip_pdf')
                ->label('Slip Pembukaan Saksi (PDF)')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->url(route('tenders.opening-slip', $tender->id), shouldOpenInNewTab: true);

            $actions[] = Action::make('evaluation_report_pdf')
                ->label('Laporan Penilaian (PDF)')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->url(route('tenders.evaluation-report', $tender->id), shouldOpenInNewTab: true);
        }

        // Download SST Letter PDF
        if ($tender->isAwarded() && filled($tender->sst_letter_pdf_path)) {
            $actions[] = Action::make('sst_letter_pdf')
                ->label('Surat Setuju Terima (SST PDF)')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->url(route('tenders.sst-letter', $tender->id), shouldOpenInNewTab: true);
        }

        return $actions;
    }
}
