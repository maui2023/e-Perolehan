<?php

namespace App\Filament\Penilai\Resources\Tenders\Tables;

use App\Models\Tender;
use App\Services\SealedBiddingService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TendersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('witnesses'))
            ->columns([
                TextColumn::make('reference_no')
                    ->label('No. Rujukan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('title')
                    ->label('Tajuk Perolehan')
                    ->searchable()
                    ->limit(40),
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
                TextColumn::make('closing_at')
                    ->label('Tarikh Tutup')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'closed' => 'Tutup (Menunggu Saksi)',
                        'box_opened' => 'Peti Dibuka (Sedia Dinilai)',
                        'evaluating' => 'Sedang Dinilai',
                        'awarded' => 'Dianugerahkan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'closed' => 'warning',
                        'box_opened' => 'info',
                        'evaluating' => 'primary',
                        'awarded' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('bids_count')
                    ->counts('bids')
                    ->label('Bidaan Masuk')
                    ->badge()
                    ->color('info'),
                TextColumn::make('witnesses_count')
                    ->counts('witnesses')
                    ->label('Saksi Sah (Min 2)')
                    ->badge()
                    ->color(fn ($state) => (int) $state >= 2 ? 'success' : 'warning'),
                TextColumn::make('dual_witness_opened_at')
                    ->label('Waktu Peti Dibuka')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Peti Terkunci'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'closed' => 'Tutup (Menunggu Saksi)',
                        'box_opened' => 'Peti Dibuka (Sedia Dinilai)',
                        'evaluating' => 'Sedang Dinilai',
                        'awarded' => 'Dianugerahkan',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('witness_open')
                    ->label('Sahkan Saksi')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(function (Tender $record): bool {
                        $user = auth()->user();
                        if (! $user || ! $user->isSaksiPembuka()) {
                            return false;
                        }
                        if ($record->isBoxOpened()) {
                            return false;
                        }

                        return ! $record->witnesses->contains('user_id', $user->id);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Pengesahan Saksi Digital')
                    ->modalDescription('Sahkan kehadiran anda sebagai saksi digital pembukaan peti tender.')
                    ->action(function (Tender $record) {
                        $result = app(SealedBiddingService::class)->affirmWitnessOpening($record, auth()->user());
                        Notification::make()
                            ->title('Pengesahan Saksi Berjaya')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
