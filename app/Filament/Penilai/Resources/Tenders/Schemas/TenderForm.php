<?php

namespace App\Filament\Penilai\Resources\Tenders\Schemas;

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
                    ->label('Kategori')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'supply' => 'Bekalan',
                        'service' => 'Perkhidmatan',
                        'work' => 'Kerja / Pembinaan',
                        'consultancy' => 'Perkhidmatan Perunding',
                        default => $state ?? '-',
                    })
                    ->disabled(),
                TextInput::make('min_technical_score')
                    ->label('Ambang Markah Teknikal Minimum')
                    ->suffix('%')
                    ->disabled(),
                DateTimePicker::make('closing_at')
                    ->label('Tarikh Tutup Bidaan')
                    ->disabled(),
                DateTimePicker::make('dual_witness_opened_at')
                    ->label('Tarikh Pembukaan Peti Dua Saksi')
                    ->disabled(),
                Textarea::make('description')
                    ->label('Keterangan Tender')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(),
                Textarea::make('scope_of_work')
                    ->label('Skop Kerja & Spesifikasi Teknikal')
                    ->rows(4)
                    ->columnSpanFull()
                    ->disabled(),
                Textarea::make('eligibility_criteria')
                    ->label('Kriteria Kelayakan & Pematuhan')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }
}
