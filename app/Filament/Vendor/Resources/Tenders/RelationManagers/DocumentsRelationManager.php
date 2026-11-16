<?php

namespace App\Filament\Vendor\Resources\Tenders\RelationManagers;

use App\Models\Tender;
use App\Models\TenderDocument;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Dokumen Tender Rasmi & Jadual Spesifikasi';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function canEdit(Model $record): bool
    {
        return false;
    }

    public function canDelete(Model $record): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Tajuk Dokumen')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('file_name')
                    ->label('Nama Fail')
                    ->limit(30),
                TextColumn::make('file_size')
                    ->label('Saiz')
                    ->formatStateUsing(function ($state) {
                        $bytes = (int) $state;
                        if ($bytes >= 1048576) {
                            return round($bytes / 1048576, 2).' MB';
                        }
                        if ($bytes >= 1024) {
                            return round($bytes / 1024, 2).' KB';
                        }

                        return "{$bytes} B";
                    }),
                IconColumn::make('is_public')
                    ->label('Akses')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-open')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('download_eligibility')
                    ->label('Status Kelayakan Muat Turun')
                    ->state(function (TenderDocument $record): string {
                        /** @var Tender $tender */
                        $tender = $this->getOwnerRecord();
                        $vendor = auth()->user()?->vendor;

                        if ($record->is_public || (float) $tender->document_price <= 0) {
                            return 'Percuma';
                        }

                        if ($vendor && $vendor->hasPurchasedTender($tender->id)) {
                            return 'Telah Dibeli (Akses Penuh)';
                        }

                        return 'Perlu Pembelian';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Percuma', 'Telah Dibeli (Akses Penuh)' => 'success',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Muat Turun')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (TenderDocument $record) {
                        /** @var Tender $tender */
                        $tender = $this->getOwnerRecord();
                        $vendor = auth()->user()?->vendor;

                        $isFree = $record->is_public || (float) $tender->document_price <= 0;
                        $hasPurchased = $vendor && $vendor->hasPurchasedTender($tender->id);

                        if (! $isFree && ! $hasPurchased) {
                            Notification::make()
                                ->title('Pembelian Diperlukan')
                                ->body('Sila beli dokumen tender ini terlebih dahulu untuk memuat turun spesifikasi lengkap.')
                                ->warning()
                                ->send();

                            return;
                        }

                        return redirect()->route('documents.download', $record->id);
                    }),
            ]);
    }
}
