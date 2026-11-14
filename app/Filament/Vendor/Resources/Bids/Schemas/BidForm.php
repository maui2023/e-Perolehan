<?php

namespace App\Filament\Vendor\Resources\Bids\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BidForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tender_id')
                    ->relationship('tender', 'title')
                    ->required(),
                Select::make('vendor_id')
                    ->relationship('vendor', 'id')
                    ->required(),
                TextInput::make('bid_reference')
                    ->required(),
                TextInput::make('blind_code'),
                Textarea::make('encrypted_bid_amount')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('decrypted_bid_amount')
                    ->numeric(),
                TextInput::make('completion_duration_months')
                    ->numeric(),
                Textarea::make('technical_summary')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('sha256_package_hash'),
                DateTimePicker::make('submitted_at'),
                DateTimePicker::make('opened_at'),
                TextInput::make('submission_slip_pdf_path'),
                TextInput::make('technical_score')
                    ->numeric(),
                TextInput::make('financial_score')
                    ->numeric(),
                TextInput::make('total_score')
                    ->numeric(),
            ]);
    }
}
