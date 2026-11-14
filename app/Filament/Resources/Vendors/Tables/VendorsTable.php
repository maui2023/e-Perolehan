<?php

namespace App\Filament\Resources\Vendors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('registration_number')
                    ->searchable(),
                TextColumn::make('tax_identification_number')
                    ->searchable(),
                TextColumn::make('company_email')
                    ->searchable(),
                TextColumn::make('company_phone')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('state')
                    ->searchable(),
                TextColumn::make('postcode')
                    ->searchable(),
                TextColumn::make('mof_registration_number')
                    ->searchable(),
                TextColumn::make('cidb_registration_number')
                    ->searchable(),
                IconColumn::make('bumiputera_status')
                    ->boolean(),
                TextColumn::make('compliance_status')
                    ->searchable(),
                TextColumn::make('compliance_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('verified_by_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('anti_bribery_agreed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ssm_cert_path')
                    ->searchable(),
                TextColumn::make('mof_cert_path')
                    ->searchable(),
                TextColumn::make('cidb_cert_path')
                    ->searchable(),
                TextColumn::make('bank_statement_path')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
