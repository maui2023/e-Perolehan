<?php

namespace App\Filament\Vendor\Resources\Tenders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TendersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_no')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('procurement_type')
                    ->searchable(),
                TextColumn::make('estimated_budget')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('document_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('tender_deposit_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closing_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('min_technical_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dual_witness_opened_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('awardedBid.id')
                    ->searchable(),
                TextColumn::make('awarded_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sst_letter_pdf_path')
                    ->searchable(),
                TextColumn::make('opening_slip_pdf_path')
                    ->searchable(),
                TextColumn::make('evaluation_report_pdf_path')
                    ->searchable(),
                TextColumn::make('archived_to_minio_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_by_user_id')
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
