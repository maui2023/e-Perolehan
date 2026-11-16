<?php

namespace App\Filament\Resources\Bids\Tables;

use App\Models\Bid;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BidsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['tender', 'vendor']))
            ->columns([
                TextColumn::make('bid_reference')
                    ->label('Kod Bidaan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('tender.reference_no')
                    ->label('No. Tender')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tender.title')
                    ->label('Tajuk Tender')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (Bid $record): string => $record->tender->title ?? ''),
                TextColumn::make('vendor.company_name')
                    ->label('Pembekal')
                    ->searchable()
                    ->limit(25),
                TextColumn::make('blind_code')
                    ->label('Kod Blind')
                    ->placeholder('-'),
                TextColumn::make('price_status')
                    ->label('Tawaran Harga')
                    ->state(function (Bid $record): string {
                        if ($record->tender && $record->tender->isBoxOpened() && filled($record->decrypted_bid_amount)) {
                            return 'RM '.number_format((float) $record->decrypted_bid_amount, 2);
                        }

                        return 'TERKUNCI (AES-256)';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_starts_with($state, 'RM') ? 'success' : 'warning'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'submitted', 'sealed' => 'Bidaan Dikunci',
                        'opened', 'passed_technical' => 'Sedang Dinilai',
                        'awarded' => 'Dianugerahkan',
                        'rejected', 'failed_technical' => 'Tidak Berjaya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted', 'sealed' => 'primary',
                        'opened', 'passed_technical' => 'info',
                        'awarded' => 'success',
                        'rejected', 'failed_technical' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('submitted_at')
                    ->label('Waktu Hantar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Draf'),
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
                Action::make('download_slip')
                    ->label('Slip PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (Bid $record): bool => filled($record->submission_slip_pdf_path))
                    ->url(fn (Bid $record): string => route('vendor.bids.slip', $record->id), shouldOpenInNewTab: true),
            ])
            ->toolbarActions([]);
    }
}
