<?php

namespace App\Filament\Resources\Tenders\Tables;

use App\Models\Tender;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TendersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->limit(35)
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draf',
                        'published' => 'Diiklankan',
                        'closed' => 'Ditutup',
                        'evaluating' => 'Penilaian',
                        'awarded' => 'Dianugerahkan',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'info',
                        'closed' => 'warning',
                        'evaluating' => 'primary',
                        'awarded' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('bids_count')
                    ->counts('bids')
                    ->label('Peti Bidaan')
                    ->badge()
                    ->color('info'),
                TextColumn::make('witnesses_count')
                    ->counts('witnesses')
                    ->label('Saksi Sah')
                    ->badge()
                    ->color(fn ($state) => (int) $state >= 2 ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draf',
                        'published' => 'Diiklankan',
                        'closed' => 'Ditutup',
                        'evaluating' => 'Dalam Penilaian',
                        'awarded' => 'Dianugerahkan',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'supply' => 'Bekalan',
                        'service' => 'Perkhidmatan',
                        'work' => 'Kerja',
                        'consultancy' => 'Perunding',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('notice_pdf')
                    ->label('Notis PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Tender $record): string => route('tenders.notice-pdf', $record->id), shouldOpenInNewTab: true),
                Action::make('sst_pdf')
                    ->label('SST PDF')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Tender $record): bool => $record->status === 'awarded' && filled($record->sst_letter_pdf_path))
                    ->url(fn (Tender $record): string => route('tenders.sst-letter', $record->id), shouldOpenInNewTab: true),
            ])
            ->toolbarActions([]);
    }
}
