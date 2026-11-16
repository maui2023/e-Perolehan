<?php

namespace App\Filament\Resources\AuditTrails;

use App\Filament\Resources\AuditTrails\Pages\ListAuditTrails;
use App\Filament\Resources\AuditTrails\Schemas\AuditTrailForm;
use App\Filament\Resources\AuditTrails\Tables\AuditTrailsTable;
use App\Models\AuditTrail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditTrailResource extends Resource
{
    protected static ?string $model = AuditTrail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Jejak Audit Integriti';

    protected static ?string $modelLabel = 'Jejak Audit';

    protected static ?string $pluralModelLabel = 'Jejak Audit Rangkaian SHA-256';

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
        ];
    }
}
