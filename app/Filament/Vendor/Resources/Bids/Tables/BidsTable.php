<?php

namespace App\Filament\Vendor\Resources\Bids\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BidsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tender.title')
                    ->searchable(),
                TextColumn::make('vendor.id')
                    ->searchable(),
                TextColumn::make('bid_reference')
                    ->searchable(),
                TextColumn::make('blind_code')
                    ->searchable(),
                TextColumn::make('decrypted_bid_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('completion_duration_months')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('sha256_package_hash')
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('submission_slip_pdf_path')
                    ->searchable(),
                TextColumn::make('technical_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('financial_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_score')
                    ->numeric()
                    ->sortable(),
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
