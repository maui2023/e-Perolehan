<?php

namespace App\Filament\Penilai\Resources\Tenders\Schemas;

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
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                TextInput::make('procurement_type')
                    ->required()
                    ->default('open'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('scope_of_work')
                    ->columnSpanFull(),
                Textarea::make('eligibility_criteria')
                    ->columnSpanFull(),
                TextInput::make('estimated_budget')
                    ->numeric(),
                TextInput::make('document_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('tender_deposit_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('published_at'),
                DateTimePicker::make('closing_at'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('min_technical_score')
                    ->required()
                    ->numeric()
                    ->default(70),
                DateTimePicker::make('dual_witness_opened_at'),
                Select::make('awarded_bid_id')
                    ->relationship('awardedBid', 'id'),
                DateTimePicker::make('awarded_at'),
                Textarea::make('award_notes')
                    ->columnSpanFull(),
                TextInput::make('sst_letter_pdf_path'),
                TextInput::make('opening_slip_pdf_path'),
                TextInput::make('evaluation_report_pdf_path'),
                DateTimePicker::make('archived_to_minio_at'),
                TextInput::make('created_by_user_id')
                    ->numeric(),
            ]);
    }
}
