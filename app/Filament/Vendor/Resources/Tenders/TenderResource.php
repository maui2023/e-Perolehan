<?php

namespace App\Filament\Vendor\Resources\Tenders;

use App\Filament\Vendor\Resources\Tenders\Pages\CreateTender;
use App\Filament\Vendor\Resources\Tenders\Pages\EditTender;
use App\Filament\Vendor\Resources\Tenders\Pages\ListTenders;
use App\Filament\Vendor\Resources\Tenders\Schemas\TenderForm;
use App\Filament\Vendor\Resources\Tenders\Tables\TendersTable;
use App\Models\Tender;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TenderResource extends Resource
{
    protected static ?string $model = Tender::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TenderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TendersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenders::route('/'),
            'create' => CreateTender::route('/create'),
            'edit' => EditTender::route('/{record}/edit'),
        ];
    }
}
