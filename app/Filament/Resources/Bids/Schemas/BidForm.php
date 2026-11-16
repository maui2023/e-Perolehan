<?php

namespace App\Filament\Resources\Bids\Schemas;

use App\Models\Bid;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BidForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bid_reference')
                    ->label('Kod Rujukan Bidaan')
                    ->disabled(),
                TextInput::make('tender_title')
                    ->label('Tender')
                    ->statePath('tender.title')
                    ->disabled(),
                TextInput::make('vendor_name')
                    ->label('Syarikat Pembekal')
                    ->statePath('vendor.company_name')
                    ->disabled(),
                TextInput::make('blind_code')
                    ->label('Kod Dwi-Buta')
                    ->placeholder('Belum dijana (peti belum dibuka)')
                    ->disabled(),
                TextInput::make('price_display')
                    ->label('Tawaran Harga (RM)')
                    ->statePath('decrypted_bid_amount')
                    ->formatStateUsing(function ($state, ?Bid $record) {
                        if (! $record) {
                            return '-';
                        }
                        if ($record->tender && $record->tender->isBoxOpened() && filled($record->decrypted_bid_amount)) {
                            return 'RM '.number_format((float) $record->decrypted_bid_amount, 2);
                        }

                        return 'TERKUNCI / DISULITKAN (AES-256)';
                    })
                    ->disabled()
                    ->helperText('Harga tawaran hanya dinyahsulitkan setelah prosedur dua saksi digital disahkan.'),
                TextInput::make('completion_duration_months')
                    ->label('Tempoh (Bulan)')
                    ->suffix('Bulan')
                    ->disabled(),
                TextInput::make('status')
                    ->label('Status Bidaan')
                    ->disabled(),
                DateTimePicker::make('submitted_at')
                    ->label('Waktu Penyerahan Bidaan')
                    ->disabled(),
                Textarea::make('technical_summary')
                    ->label('Ringkasan Teknikal')
                    ->rows(3)
                    ->columnSpanFull()
                    ->disabled(),
                TextInput::make('sha256_package_hash')
                    ->label('Cap Jari SHA-256 Integriti Bidaan')
                    ->disabled()
                    ->columnSpanFull(),
                DateTimePicker::make('opened_at')
                    ->label('Waktu Peti Dibuka')
                    ->disabled(),
            ]);
    }
}
