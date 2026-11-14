<?php

namespace App\Filament\Resources\AuditTrails;

use App\Filament\Resources\AuditTrails\Pages\CreateAuditTrail;
use App\Filament\Resources\AuditTrails\Pages\EditAuditTrail;
use App\Filament\Resources\AuditTrails\Pages\ListAuditTrails;
use App\Filament\Resources\AuditTrails\Schemas\AuditTrailForm;
use App\Filament\Resources\AuditTrails\Tables\AuditTrailsTable;
use App\Models\AuditTrail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuditTrailResource extends Resource
{
    protected static ?string $model = AuditTrail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AuditTrailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditTrailsTable::configure($table);
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
            'index' => ListAuditTrails::route('/'),
            'create' => CreateAuditTrail::route('/create'),
            'edit' => EditAuditTrail::route('/{record}/edit'),
        ];
    }
}
