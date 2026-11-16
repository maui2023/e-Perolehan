<?php

namespace App\Filament\Vendor\Resources\Tenders;

use App\Filament\Vendor\Resources\Tenders\Pages\ListTenders;
use App\Filament\Vendor\Resources\Tenders\Pages\ViewTender;
use App\Filament\Vendor\Resources\Tenders\RelationManagers\DocumentsRelationManager;
use App\Filament\Vendor\Resources\Tenders\Schemas\TenderForm;
use App\Filament\Vendor\Resources\Tenders\Tables\TendersTable;
use App\Models\Tender;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenderResource extends Resource
{
    protected static ?string $model = Tender::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Tender & Sebut Harga';

    protected static ?string $modelLabel = 'Tender';

    protected static ?string $pluralModelLabel = 'Senarai Tender & Sebut Harga';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return TenderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TendersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['published', 'closed', 'evaluating', 'awarded'])
            ->latest('published_at');
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenders::route('/'),
            'view' => ViewTender::route('/{record}'),
        ];
    }
}
