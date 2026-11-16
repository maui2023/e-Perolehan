<?php

namespace App\Filament\Resources\Tenders\Pages;

use App\Filament\Resources\Tenders\TenderResource;
use App\Models\AuditTrail;
use Filament\Resources\Pages\CreateRecord;

class CreateTender extends CreateRecord
{
    protected static string $resource = TenderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $tender = $this->getRecord();

        AuditTrail::log(
            eventType: 'TENDER_CREATED',
            description: "Tender baharu {$tender->reference_no} dicipta oleh pentadbir",
            auditable: $tender,
            newValues: ['reference_no' => $tender->reference_no, 'title' => $tender->title],
            userId: auth()->id()
        );
    }
}
