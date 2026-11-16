<?php

namespace App\Filament\Vendor\Resources\Bids\Tables;

use App\Models\AuditTrail;
use App\Models\Bid;
use App\Services\PdfReportService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BidsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('tender'))
            ->stackedOnMobile()
            ->recordUrl(fn (Bid $record): string => route('filament.vendor.resources.bids.view', $record))
            ->columns([
                TextColumn::make('bid_reference')
                    ->label('Kod Rujukan Bidaan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('tender.reference_no')
                    ->label('No. Tender')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tender.title')
                    ->label('Tajuk Perolehan')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn (Bid $record): string => $record->tender->title ?? ''),
                TextColumn::make('completion_duration_months')
                    ->label('Tempoh')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} Bulan" : '-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf Bidaan',
                        'submitted', 'sealed' => 'Bidaan Dikunci (Sealed)',
                        'opened', 'passed_technical' => 'Sedang Dinilai',
                        'awarded' => 'Dianugerahkan',
                        'rejected', 'failed_technical' => 'Tidak Berjaya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'submitted', 'sealed' => 'primary',
                        'opened', 'passed_technical' => 'info',
                        'awarded' => 'success',
                        'rejected', 'failed_technical' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('submitted_at')
                    ->label('Tarikh Hantar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Belum dihantar'),
                TextColumn::make('sha256_package_hash')
                    ->label('Hash SHA-256')
                    ->limit(10)
                    ->copyable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draf',
                        'sealed' => 'Bidaan Dikunci',
                        'opened' => 'Dibuka & Dinilai',
                        'awarded' => 'Dianugerahkan',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Bid $record): bool => $record->status === 'draft'),
                Action::make('submit_sealed')
                    ->label('Kunci & Hantar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->visible(fn (Bid $record): bool => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Kunci Peti & Hantar Bidaan Rasmi')
                    ->modalDescription('Adakah anda bersedia untuk menyerahkan bidaan ini secara muktamad? Bidaan akan dikunci dan Slip Penerimaan (PDF) akan dikeluarkan.')
                    ->action(function (Bid $record) {
                        if (! $record->tender->isOpenForBidding()) {
                            Notification::make()
                                ->title('Gagal')
                                ->body('Tender ini telah pun ditutup.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->status = 'sealed';
                        $record->submitted_at = now();

                        $fileHashes = $record->documents()->pluck('sha256_checksum')->toArray();
                        $packageContent = json_encode([
                            'bid_ref' => $record->bid_reference,
                            'tender_ref' => $record->tender->reference_no,
                            'vendor_ssm' => $record->vendor->registration_number,
                            'encrypted_price' => $record->encrypted_bid_amount,
                            'files' => $fileHashes,
                            'submitted_at' => $record->submitted_at->toIso8601String(),
                        ]);

                        $packageHash = hash('sha256', $packageContent);
                        $record->sha256_package_hash = $packageHash;
                        $record->save();

                        app(PdfReportService::class)->generateBidSubmissionSlipPdf($record);

                        AuditTrail::log(
                            eventType: 'BID_SUBMITTED_SEALED',
                            description: "Bidaan bertutup {$record->bid_reference} berjaya diserahkan",
                            auditable: $record,
                            userId: auth()->id()
                        );

                        Notification::make()
                            ->title('Bidaan Berjaya Dihantar')
                            ->body("Bidaan {$record->bid_reference} telah dikunci dan slip PDF telah dijana.")
                            ->success()
                            ->send();
                    }),
                Action::make('download_slip')
                    ->label('Slip PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (Bid $record): bool => $record->status !== 'draft' && filled($record->submission_slip_pdf_path))
                    ->url(fn (Bid $record): string => route('vendor.bids.slip', $record->id), shouldOpenInNewTab: true),
            ])
            ->toolbarActions([]);
    }
}
