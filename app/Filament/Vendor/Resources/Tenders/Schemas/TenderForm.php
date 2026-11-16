<?php

namespace App\Filament\Vendor\Resources\Tenders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference_no')
                    ->label('Nombor Rujukan')
                    ->disabled(),
                TextInput::make('title')
                    ->label('Tajuk Perolehan')
                    ->disabled(),
                TextInput::make('category')
                    ->label('Kategori Perolehan')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'supply' => 'Bekalan',
                        'service' => 'Perkhidmatan',
                        'work' => 'Kerja / Pembinaan',
                        'consultancy' => 'Perkhidmatan Perunding',
                        default => $state ?? '-',
                    })
                    ->disabled(),
                TextInput::make('procurement_type')
                    ->label('Kaedah Perolehan')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'open' => 'Tender Terbuka',
                        'limited' => 'Tender Terhad',
                        'quotation' => 'Sebut Harga',
                        default => $state ?? '-',
                    })
                    ->disabled(),
                TextInput::make('document_price')
                    ->label('Harga Dokumen Tender (RM)')
                    ->numeric()
                    ->prefix('RM')
                    ->disabled(),
                DateTimePicker::make('closing_at')
                    ->label('Tarikh & Waktu Tutup Bidaan')
                    ->disabled(),
                Textarea::make('description')
                    ->label('Keterangan Ringkas')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(),
                Textarea::make('scope_of_work')
                    ->label('Skop Kerja & Spesifikasi Bekalan')
                    ->rows(4)
                    ->columnSpanFull()
                    ->disabled(),
                Textarea::make('eligibility_criteria')
                    ->label('Syarat Kelayakan & Pematuhan (MOF / CIDB)')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }
}
