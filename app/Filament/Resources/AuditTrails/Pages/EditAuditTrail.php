<?php

namespace App\Filament\Resources\AuditTrails\Pages;

use App\Filament\Resources\AuditTrails\AuditTrailResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAuditTrail extends EditRecord
{
    protected static string $resource = AuditTrailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
