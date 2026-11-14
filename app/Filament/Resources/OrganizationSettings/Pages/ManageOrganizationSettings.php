<?php

namespace App\Filament\Resources\OrganizationSettings\Pages;

use App\Filament\Resources\OrganizationSettings\OrganizationSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOrganizationSettings extends ManageRecords
{
    protected static string $resource = OrganizationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
