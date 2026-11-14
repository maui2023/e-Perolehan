<?php

namespace App\Filament\Resources\AuditTrails\Pages;

use App\Filament\Resources\AuditTrails\AuditTrailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAuditTrail extends CreateRecord
{
    protected static string $resource = AuditTrailResource::class;
}
