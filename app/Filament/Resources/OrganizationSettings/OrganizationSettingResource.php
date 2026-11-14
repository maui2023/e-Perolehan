<?php

namespace App\Filament\Resources\OrganizationSettings;

use App\Filament\Resources\OrganizationSettings\Pages\ManageOrganizationSettings;
use App\Models\OrganizationSetting;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationSettingResource extends Resource
{
    protected static ?string $model = OrganizationSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('org_name')
                    ->required()
                    ->default('Universal Procurement System'),
                TextInput::make('org_short_name')
                    ->required()
                    ->default('E-Perolehan'),
                TextInput::make('org_reg_no'),
                TextInput::make('org_email')
                    ->email(),
                TextInput::make('org_phone')
                    ->tel(),
                Textarea::make('org_address')
                    ->columnSpanFull(),
                TextInput::make('org_logo_path'),
                TextInput::make('currency')
                    ->required()
                    ->default('MYR'),
                TextInput::make('tax_percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('anti_bribery_policy_text')
                    ->columnSpanFull(),
                Textarea::make('procurement_terms_text')
                    ->columnSpanFull(),
                TextInput::make('letterhead_footer_text'),
                Textarea::make('extra_config')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('org_name')
                    ->searchable(),
                TextColumn::make('org_short_name')
                    ->searchable(),
                TextColumn::make('org_reg_no')
                    ->searchable(),
                TextColumn::make('org_email')
                    ->searchable(),
                TextColumn::make('org_phone')
                    ->searchable(),
                TextColumn::make('org_logo_path')
                    ->searchable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('tax_percentage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('letterhead_footer_text')
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrganizationSettings::route('/'),
        ];
    }
}
