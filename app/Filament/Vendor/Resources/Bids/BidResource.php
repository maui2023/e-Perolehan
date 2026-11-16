<?php

namespace App\Filament\Vendor\Resources\Bids;

use App\Filament\Vendor\Resources\Bids\Pages\CreateBid;
use App\Filament\Vendor\Resources\Bids\Pages\EditBid;
use App\Filament\Vendor\Resources\Bids\Pages\ListBids;
use App\Filament\Vendor\Resources\Bids\Pages\ViewBid;
use App\Filament\Vendor\Resources\Bids\Schemas\BidForm;
use App\Filament\Vendor\Resources\Bids\Tables\BidsTable;
use App\Models\Bid;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BidResource extends Resource
{
    protected static ?string $model = Bid::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $navigationLabel = 'Bidaan Saya';

    protected static ?string $modelLabel = 'Bidaan';

    protected static ?string $pluralModelLabel = 'Senarai Bidaan Saya';

    public static function canCreate(): bool
    {
        return auth()->user()?->isVendor() ?? false;
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
        $vendorId = auth()->user()?->vendor?->id;

        return parent::getEloquentQuery()
            ->when($vendorId, fn (Builder $q) => $q->where('vendor_id', $vendorId))
            ->latest();
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
            'create' => CreateBid::route('/create'),
            'view' => ViewBid::route('/{record}'),
            'edit' => EditBid::route('/{record}/edit'),
        ];
    }
}
