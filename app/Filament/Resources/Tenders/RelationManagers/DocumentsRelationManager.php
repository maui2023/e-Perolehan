<?php

namespace App\Filament\Resources\Tenders\RelationManagers;

use App\Models\TenderDocument;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Dokumen Tender Rasmi & Spesifikasi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Tajuk Dokumen')
                    ->placeholder('Contoh: Dokumen Meja Tender & Jadual Spesifikasi')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->label('Fail Dokumen Rasmi (PDF / ZIP)')
                    ->disk('private')
                    ->directory('tenders/documents')
                    ->acceptedFileTypes(['application/pdf', 'application/zip', 'application/x-zip-compressed'])
                    ->maxSize(51200)
                    ->required()
                    ->helperText('Format PDF atau ZIP (Maksimum 50MB). Disimpan dalam storan persendirian.')
                    ->storeFileNamesIn('file_name'),
                Toggle::make('is_public')
                    ->label('Dokumen Awam (Boleh dimuat turun percuma di portal)')
                    ->helperText('Jika ditandakan, orang awam dan vendor boleh memuat turun tanpa membeli dokumen.')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Tajuk Dokumen')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('file_name')
                    ->label('Nama Fail')
                    ->searchable()
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
                    ->label('Akses Awam')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-open')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('sha256_checksum')
                    ->label('Hash SHA-256')
                    ->limit(10)
                    ->copyable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Tarikh Muat Naik')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Muat Naik Dokumen')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->mutateFormDataUsing(function (array $data): array {
                        $filePath = $data['file_path'];
                        $disk = Storage::disk('private');

                        if ($disk->exists($filePath)) {
                            $data['file_size'] = $disk->size($filePath);
                            $data['mime_type'] = $disk->mimeType($filePath) ?? 'application/pdf';
                            $data['sha256_checksum'] = hash('sha256', $disk->get($filePath));
                            $data['disk'] = 'private';
                            $data['file_name'] = basename($filePath);
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Muat Turun')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (TenderDocument $record): string => route('documents.download', $record->id), shouldOpenInNewTab: true),
                DeleteAction::make(),
            ]);
    }
}
