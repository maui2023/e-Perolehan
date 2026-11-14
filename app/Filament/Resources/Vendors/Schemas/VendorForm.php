<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('company_name')
                    ->required(),
                TextInput::make('registration_number')
                    ->required(),
                TextInput::make('tax_identification_number'),
                TextInput::make('company_email')
                    ->email(),
                TextInput::make('company_phone')
                    ->tel(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('postcode'),
                TextInput::make('mof_registration_number'),
                TextInput::make('cidb_registration_number'),
                Toggle::make('bumiputera_status')
                    ->required(),
                TextInput::make('compliance_status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('compliance_verified_at'),
                TextInput::make('verified_by_user_id')
                    ->numeric(),
                Textarea::make('compliance_notes')
                    ->columnSpanFull(),
                DateTimePicker::make('anti_bribery_agreed_at'),
                TextInput::make('ssm_cert_path'),
                TextInput::make('mof_cert_path'),
                TextInput::make('cidb_cert_path'),
                TextInput::make('bank_statement_path'),
            ]);
    }
}
