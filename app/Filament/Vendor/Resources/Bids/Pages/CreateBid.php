<?php

namespace App\Filament\Vendor\Resources\Bids\Pages;

use App\Filament\Vendor\Resources\Bids\BidResource;
use App\Models\AuditTrail;
use App\Models\Bid;
use App\Models\BidDocument;
use App\Models\Tender;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CreateBid extends CreateRecord
{
    protected static string $resource = BidResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $vendor = $user?->vendor;

        if (! $vendor) {
            throw ValidationException::withMessages([
                'tender_id' => 'Profil syarikat pembekal tidak dijumpai.',
            ]);
        }

        if (! $vendor->isVerified()) {
            throw ValidationException::withMessages([
                'tender_id' => 'Profil syarikat anda belum disahkan oleh pihak pentadbir untuk membida.',
            ]);
        }

        $tender = Tender::findOrFail($data['tender_id']);

        if (! $tender->isOpenForBidding()) {
            throw ValidationException::withMessages([
                'tender_id' => 'Tender ini tidak lagi dibuka untuk penyerahan bidaan.',
            ]);
        }

        if ((float) $tender->document_price > 0 && ! $vendor->hasPurchasedTender($tender->id)) {
            throw ValidationException::withMessages([
                'tender_id' => 'Sila buat pembelian dokumen tender terlebih dahulu sebelum menghantar bidaan.',
            ]);
        }

        $existingBid = Bid::where('tender_id', $tender->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if ($existingBid) {
            throw ValidationException::withMessages([
                'tender_id' => 'Syarikat anda telah pun mempunyai rekod bidaan bagi tender ini.',
            ]);
        }

        $data['vendor_id'] = $vendor->id;
        $data['status'] = 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Bid $bid */
        $bid = $this->getRecord();
        $data = $this->form->getState();

        // Process Technical Proposal File
        if (! empty($data['technical_proposal_file'])) {
            $this->storeBidDoc($bid, 'technical_proposal', $data['technical_proposal_file'], false);
        }

        // Process Financial Proposal File
        if (! empty($data['financial_proposal_file'])) {
            $this->storeBidDoc($bid, 'financial_proposal', $data['financial_proposal_file'], true);
        }

        AuditTrail::log(
            eventType: 'BID_DRAFT_CREATED',
            description: "Draf bidaan {$bid->bid_reference} dicipta oleh pembekal {$bid->vendor->company_name}",
            auditable: $bid,
            newValues: ['bid_reference' => $bid->bid_reference, 'tender_id' => $bid->tender_id],
            userId: auth()->id()
        );

        Notification::make()
            ->title('Draf Bidaan Berjaya Dicipta')
            ->body('Bidaan disimpan sebagai Draf. Anda boleh menyunting atau klik "Hantar Bidaan Rasmi" untuk mengunci bidaan ke peti tender.')
            ->success()
            ->send();
    }

    protected function storeBidDoc(Bid $bid, string $type, string $filePath, bool $isFinancial): void
    {
        $disk = Storage::disk('private');
        if ($disk->exists($filePath)) {
            $content = $disk->get($filePath);
            $checksum = hash('sha256', $content);
            $size = $disk->size($filePath);
            $mime = $disk->mimeType($filePath) ?? 'application/pdf';
            $fileName = basename($filePath);

            BidDocument::create([
                'bid_id' => $bid->id,
                'type' => $type,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $size,
                'mime_type' => $mime,
                'sha256_checksum' => $checksum,
                'is_financial' => $isFinancial,
                'disk' => 'private',
            ]);
        }
    }
}
