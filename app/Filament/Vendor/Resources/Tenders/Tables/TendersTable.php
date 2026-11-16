<?php

namespace App\Filament\Vendor\Resources\Tenders\Tables;

use App\Models\Tender;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TendersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordUrl(fn (Tender $record): string => route('filament.vendor.resources.tenders.view', $record))
            ->columns([
                TextColumn::make('reference_no')
                    ->label('No. Rujukan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('title')
                    ->label('Tajuk Perolehan')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(fn (Tender $record): string => $record->title),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'supply' => 'Bekalan',
                        'service' => 'Perkhidmatan',
                        'work' => 'Kerja',
                        'consultancy' => 'Perunding',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'supply' => 'info',
                        'service' => 'warning',
                        'work' => 'success',
                        'consultancy' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('document_price')
                    ->label('Harga Dokumen')
                    ->formatStateUsing(fn ($state) => (float) $state <= 0 ? 'Percuma' : 'RM '.number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('closing_at')
                    ->label('Tarikh Tutup')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('purchase_status')
                    ->label('Status Dokumen')
                    ->state(function (Tender $record): string {
                        $vendor = auth()->user()?->vendor;
                        if (! $vendor) {
                            return 'N/A';
                        }
                        if ((float) $record->document_price <= 0) {
                            return 'Percuma';
                        }
                        if ($vendor->hasPurchasedTender($record->id)) {
                            return 'Telah Dibeli';
                        }

                        return 'Belum Beli';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Telah Dibeli' => 'success',
                        'Percuma' => 'info',
                        'Belum Beli' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('my_bid_status')
                    ->label('Status Bidaan Anda')
                    ->state(function (Tender $record): string {
                        $vendor = auth()->user()?->vendor;
                        if (! $vendor) {
                            return '-';
                        }
                        $bid = $vendor->getBidForTender($record->id);
                        if (! $bid) {
                            return 'Belum Membida';
                        }

                        return match ($bid->status) {
                            'draft' => 'Draf Bidaan',
                            'submitted', 'sealed' => 'Bidaan Dihantar (Berkunci)',
                            'opened', 'passed_technical' => 'Sedang Dinilai',
                            'awarded' => 'Dianugerahkan',
                            'rejected', 'failed_technical' => 'Tidak Berjaya',
                            default => $bid->status,
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Bidaan Dihantar (Berkunci)' => 'primary',
                        'Dianugerahkan' => 'success',
                        'Draf Bidaan' => 'warning',
                        'Sedang Dinilai' => 'info',
                        'Tidak Berjaya' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'supply' => 'Bekalan',
                        'service' => 'Perkhidmatan',
                        'work' => 'Kerja / Pembinaan',
                        'consultancy' => 'Perkhidmatan Perunding',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('purchase')
                    ->label('Beli Dokumen')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(function (Tender $record): bool {
                        $vendor = auth()->user()?->vendor;

                        return $record->isOpenForBidding()
                            && (float) $record->document_price > 0
                            && $vendor
                            && ! $vendor->hasPurchasedTender($record->id);
                    })
                    ->url(fn (Tender $record): string => route('tenders.purchase', $record->id)),
                Action::make('submit_bid')
                    ->label('Masuk Bidaan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(function (Tender $record): bool {
                        $vendor = auth()->user()?->vendor;
                        if (! $record->isOpenForBidding() || ! $vendor) {
                            return false;
                        }
                        $canAccess = (float) $record->document_price <= 0 || $vendor->hasPurchasedTender($record->id);
                        $hasBid = $vendor->getBidForTender($record->id) !== null;

                        return $canAccess && ! $hasBid;
                    })
                    ->url(fn (Tender $record): string => route('filament.vendor.resources.bids.create', ['tender_id' => $record->id])),
            ])
            ->toolbarActions([]);
    }
}
