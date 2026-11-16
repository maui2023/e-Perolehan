<?php

namespace App\Filament\Resources\Bids;

use App\Filament\Resources\Bids\Pages\ListBids;
use App\Filament\Resources\Bids\Pages\ViewBid;
use App\Filament\Resources\Bids\Schemas\BidForm;
use App\Filament\Resources\Bids\Tables\BidsTable;
use App\Models\Bid;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BidResource extends Resource
{
    protected static ?string $model = Bid::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Peti Bidaan';

    protected static ?string $modelLabel = 'Bidaan';

    protected static ?string $pluralModelLabel = 'Peti Bidaan Masuk';

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
        return BidForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BidsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest();
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
            'index' => ListBids::route('/'),
            'view' => ViewBid::route('/{record}'),
        ];
    }
}
