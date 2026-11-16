<?php

namespace App\Filament\Penilai\Resources\Tenders;

use App\Filament\Penilai\Resources\Tenders\Pages\ListTenders;
use App\Filament\Penilai\Resources\Tenders\Pages\ViewTender;
use App\Filament\Penilai\Resources\Tenders\RelationManagers\BidsRelationManager;
use App\Filament\Penilai\Resources\Tenders\Schemas\TenderForm;
use App\Filament\Penilai\Resources\Tenders\Tables\TendersTable;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $navigationLabel = 'Penilaian Tender';

    protected static ?string $modelLabel = 'Tender';

    protected static ?string $pluralModelLabel = 'Senarai Penilaian Tender';

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
            ->whereIn('status', ['closed', 'box_opened', 'evaluating', 'awarded'])
            ->latest('closing_at');
    }

    public static function getRelations(): array
    {
        return [
            BidsRelationManager::class,
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
