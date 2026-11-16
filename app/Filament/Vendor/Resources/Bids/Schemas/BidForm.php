<?php

namespace App\Filament\Vendor\Resources\Bids\Schemas;

use App\Models\Bid;
use App\Models\Tender;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Crypt;

class BidForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tender_id')
                    ->label('Tender / Sebut Harga')
                    ->options(function (?Bid $record) {
                        $vendor = auth()->user()?->vendor;
                        if (! $vendor) {
                            return [];
                        }

                        $query = Tender::query()
                            ->where('status', 'published')
                            ->where(function ($sub) use ($vendor) {
                                $sub->where('document_price', '<=', 0)
                                    ->orWhereHas('purchases', function ($p) use ($vendor) {
                                        $p->where('vendor_id', $vendor->id)
                                            ->where('status', 'paid');
                                    });
                            });

                        if ($record && $record->tender_id) {
                            $query->orWhere('id', $record->tender_id);
                        }

                        return $query->get()->mapWithKeys(function (Tender $tender) {
                            return [$tender->id => "{$tender->reference_no} — {$tender->title}"];
                        });
                    })
                    ->default(fn () => filled(request()->query('tender_id')) ? (int) request()->query('tender_id') : null)
                    ->required()
                    ->disabled(fn (?Bid $record) => $record !== null)
                    ->helperText('Hanya tender yang dibuka dan telah dibeli dokumennya (atau tender percuma) dipaparkan.')
                    ->columnSpanFull(),

                TextInput::make('bid_amount')
                    ->label('Tawaran Harga Bidaan (RM)')
                    ->numeric()
                    ->prefix('RM')
                    ->required()
                    ->formatStateUsing(function ($state, ?Bid $record) {
                        if (! $record) {
                            return $state;
                        }
                        if (filled($record->decrypted_bid_amount)) {
                            return $record->decrypted_bid_amount;
                        }
                        if (filled($record->encrypted_bid_amount)) {
                            try {
                                return Crypt::decryptString($record->encrypted_bid_amount);
                            } catch (\Throwable) {
                                return null;
                            }
                        }

                        return $state;
                    })
                    ->disabled(fn (?Bid $record) => $record !== null && $record->status !== 'draft')
                    ->helperText('Harga ini akan disulitkan secara automatik menggunakan enkripsi AES-256 sebaik sahaja disimpan.'),

                TextInput::make('completion_duration_months')
                    ->label('Tempoh Pelaksanaan / Penghantaran (Bulan)')
                    ->numeric()
                    ->suffix('Bulan')
                    ->default(12)
                    ->required()
                    ->disabled(fn (?Bid $record) => $record !== null && $record->status !== 'draft'),

                Textarea::make('technical_summary')
                    ->label('Ringkasan Eksekutif Cadangan Teknikal')
                    ->rows(3)
                    ->placeholder('Nyatakan secara ringkas spesifikasi, jenama bekalan, atau kaedah kerja...')
                    ->columnSpanFull()
                    ->disabled(fn (?Bid $record) => $record !== null && $record->status !== 'draft'),

                FileUpload::make('technical_proposal_file')
                    ->label('Dokumen Cadangan Teknikal (PDF)')
                    ->disk('private')
                    ->directory('bids/proposals')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(51200)
                    ->helperText('Format PDF sahaja (Maksimum 50MB).')
                    ->disabled(fn (?Bid $record) => $record !== null && $record->status !== 'draft'),

                FileUpload::make('financial_proposal_file')
                    ->label('Dokumen Cadangan Kewangan / Jadual Harga (PDF)')
                    ->disk('private')
                    ->directory('bids/proposals')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(51200)
                    ->helperText('Format PDF sahaja (Maksimum 50MB). Disimpan dalam zon tertutup.')
                    ->disabled(fn (?Bid $record) => $record !== null && $record->status !== 'draft'),

                TextInput::make('status')
                    ->label('Status Bidaan')
                    ->disabled()
                    ->visible(fn (?Bid $record) => $record !== null && $record->status !== 'draft'),

                TextInput::make('sha256_package_hash')
                    ->label('Cap Jari SHA-256 Integriti Bidaan')
                    ->disabled()
                    ->columnSpanFull()
                    ->visible(fn (?Bid $record) => $record !== null && filled($record->sha256_package_hash)),

                DateTimePicker::make('submitted_at')
                    ->label('Tarikh & Waktu Penyerahan Rasmi')
                    ->disabled()
                    ->visible(fn (?Bid $record) => $record !== null && filled($record->submitted_at)),
            ]);
    }
}
