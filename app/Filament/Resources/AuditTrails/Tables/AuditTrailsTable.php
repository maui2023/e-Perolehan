<?php

namespace App\Filament\Resources\AuditTrails\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditTrailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Cap Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('event_type')
                    ->label('Jenis Peristiwa')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->placeholder('Sistem / Automatik'),
                TextColumn::make('description')
                    ->label('Keterangan Transaksi')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->searchable(),
                TextColumn::make('hash')
                    ->label('Hash Blok SHA-256')
                    ->limit(12)
                    ->copyable()
                    ->fontFamily('mono')
                    ->tooltip(fn ($record): string => $record->hash),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Jenis Peristiwa')
                    ->options([
                        'BID_SUBMITTED_SEALED' => 'Bidaan Bertutup Dihantar',
                        'TENDER_WITNESS_AFFIRMED' => 'Perakuan Saksi Pembukaan',
                        'TENDER_BOX_UNLOCKED' => 'Peti Tender Dibuka',
                        'TECHNICAL_EVALUATION_SUBMITTED' => 'Penilaian Teknikal Disimpan',
                        'FINANCIAL_EVALUATION_COMPLETED' => 'Penilaian Kewangan Disimpan',
                        'TENDER_AWARDED' => 'Tender Dianugerahkan',
                        'COLD_STORAGE_ARCHIVED_MINIO' => 'Arkib MinIO Disimpan',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
