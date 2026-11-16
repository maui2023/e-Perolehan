<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Syarikat & Pendaftaran')
                    ->description('Maklumat pendaftaran syarikat yang dikemukakan oleh vendor.')
                    ->components([
                        TextInput::make('company_name')
                            ->label('Nama Syarikat / Perniagaan')
                            ->disabled(),
                        TextInput::make('registration_number')
                            ->label('No. Pendaftaran SSM')
                            ->disabled(),
                        TextInput::make('tax_identification_number')
                            ->label('No. Cukai Pendapatan (TIN)')
                            ->disabled(),
                        TextInput::make('company_email')
                            ->label('Emel Syarikat')
                            ->disabled(),
                        TextInput::make('company_phone')
                            ->label('No. Telefon Syarikat')
                            ->disabled(),
                        Textarea::make('address')
                            ->label('Alamat Berdaftar')
                            ->columnSpanFull()
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Pentauliahan MOF, CIDB & Bank')
                    ->components([
                        TextInput::make('mof_registration_number')
                            ->label('No. Pendaftaran MOF')
                            ->disabled(),
                        TextInput::make('mof_codes')
                            ->label('Kod Bidang MOF')
                            ->disabled(),
                        TextInput::make('cidb_registration_number')
                            ->label('No. Pendaftaran CIDB')
                            ->disabled(),
                        TextInput::make('cidb_grade')
                            ->label('Gred CIDB')
                            ->disabled(),
                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->disabled(),
                        TextInput::make('bank_account_number')
                            ->label('No. Akaun Bank')
                            ->disabled(),
                        TextInput::make('bank_account_holder')
                            ->label('Nama Pemegang Akaun')
                            ->disabled()
                            ->columnSpanFull(),
                        Toggle::make('bumiputera_status')
                            ->label('Status Bumiputera')
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Keputusan Semakan & Pematuhan Urus Setia')
                    ->description('Penetapan status kelayakan bidaan syarikat oleh pihak pentadbir.')
                    ->components([
                        Select::make('compliance_status')
                            ->label('Status Pematuhan')
                            ->options([
                                'pending' => 'Menunggu Semakan',
                                'verified' => 'Disahkan / Lulus',
                                'rejected' => 'Ditolak',
                                'suspended' => 'Digantung',
                            ])
                            ->required(),
                        DateTimePicker::make('compliance_verified_at')
                            ->label('Tarikh & Waktu Pengesahan')
                            ->default(now()),
                        Textarea::make('compliance_notes')
                            ->label('Ulasan Semakan / Catatan Pematuhan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
