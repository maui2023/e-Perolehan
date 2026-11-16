<?php

namespace App\Filament\Resources\Tenders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
                    ->placeholder('Dijana automatik jika kosong (cth: TDR-2026-0001)')
                    ->maxLength(50),
                TextInput::make('title')
                    ->label('Tajuk Perolehan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('category')
                    ->label('Kategori Perolehan')
                    ->options([
                        'supply' => 'Bekalan (Peralatan, ICT, Bahan)',
                        'service' => 'Perkhidmatan (Penyelenggaraan, Kawalan, dll.)',
                        'work' => 'Kerja / Pembinaan (Sivil, Elektrikal)',
                        'consultancy' => 'Perkhidmatan Perunding',
                    ])
                    ->required(),
                Select::make('procurement_type')
                    ->label('Kaedah Perolehan')
                    ->options([
                        'open' => 'Tender Terbuka',
                        'limited' => 'Tender Terhad / Khas',
                        'quotation' => 'Sebut Harga',
                    ])
                    ->default('open')
                    ->required(),
                TextInput::make('estimated_budget')
                    ->label('Anggaran Jabatan / Belanjawan (RM)')
                    ->numeric()
                    ->prefix('RM'),
                TextInput::make('document_price')
                    ->label('Harga Dokumen Tender (RM)')
                    ->numeric()
                    ->prefix('RM')
                    ->default(0)
                    ->required()
                    ->helperText('Pembelian dikutip secara automatik melalui AffanPay.'),
                TextInput::make('tender_deposit_amount')
                    ->label('Wang Cagaran / Deposit (RM)')
                    ->numeric()
                    ->prefix('RM')
                    ->default(0),
                TextInput::make('min_technical_score')
                    ->label('Ambang Markah Teknikal (%)')
                    ->numeric()
                    ->default(70)
                    ->suffix('%')
                    ->required(),
                DateTimePicker::make('published_at')
                    ->label('Tarikh & Waktu Iklan Dibuka')
                    ->default(now()),
                DateTimePicker::make('closing_at')
                    ->label('Tarikh & Waktu Tutup Bidaan')
                    ->required(),
                Select::make('status')
                    ->label('Status Tender')
                    ->options([
                        'draft' => 'Draf',
                        'published' => 'Diiklankan / Dibuka',
                        'closed' => 'Ditutup',
                        'evaluating' => 'Dalam Penilaian',
                        'awarded' => 'Dianugerahkan',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('published')
                    ->required(),
                Textarea::make('description')
                    ->label('Keterangan Ringkas')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('scope_of_work')
                    ->label('Skop Kerja & Spesifikasi Bekalan')
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('eligibility_criteria')
                    ->label('Syarat Kelayakan & Pematuhan (MOF / CIDB)')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
