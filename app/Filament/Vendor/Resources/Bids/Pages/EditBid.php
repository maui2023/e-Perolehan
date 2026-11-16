<?php

namespace App\Filament\Vendor\Resources\Bids\Pages;

use App\Filament\Vendor\Resources\Bids\BidResource;
use App\Models\AuditTrail;
use App\Models\Bid;
use App\Models\BidDocument;
use App\Services\PdfReportService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditBid extends EditRecord
{
    protected static string $resource = BidResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Bid $bid */
        $bid = $this->getRecord();

        $actions = [];

        if ($bid->status === 'draft') {
            $actions[] = Action::make('submit_sealed')
                ->label('Hantar Bidaan Rasmi (Kunci Peti & Cetak Slip)')
                ->icon('heroicon-o-lock-closed')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Pengesahan Penyerahan Bidaan Bertutup')
                ->modalDescription('Adakah anda pasti untuk menghantar bidaan ini secara rasmi? Sebaik sahaja diserahkan, bidaan akan dikunci (sealed) secara kekal dan tidak boleh dipinda lagi.')
                ->modalSubmitActionLabel('Ya, Kunci & Hantar Bidaan')
                ->action(function () use ($bid) {
                    if (! $bid->tender->isOpenForBidding()) {
                        Notification::make()
                            ->title('Gagal Menyerahkan Bidaan')
                            ->body('Tender ini telah pun ditutup.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $bid->status = 'sealed';
                    $bid->submitted_at = now();

                    $fileHashes = $bid->documents()->pluck('sha256_checksum')->toArray();
                    $packageContent = json_encode([
                        'bid_ref' => $bid->bid_reference,
                        'tender_ref' => $bid->tender->reference_no,
                        'vendor_ssm' => $bid->vendor->registration_number,
                        'encrypted_price' => $bid->encrypted_bid_amount,
                        'files' => $fileHashes,
                        'submitted_at' => $bid->submitted_at->toIso8601String(),
                    ]);

                    $packageHash = hash('sha256', $packageContent);
                    $bid->sha256_package_hash = $packageHash;
                    $bid->save();

                    // Generate official submission slip PDF
                    app(PdfReportService::class)->generateBidSubmissionSlipPdf($bid);

                    AuditTrail::log(
                        eventType: 'BID_SUBMITTED_SEALED',
                        description: "Bidaan bertutup {$bid->bid_reference} berjaya dikunci dan diserahkan ke peti tender",
                        auditable: $bid,
                        newValues: [
                            'bid_reference' => $bid->bid_reference,
                            'package_hash' => $packageHash,
                        ],
                        userId: auth()->id()
                    );

                    Notification::make()
                        ->title('Bidaan Berjaya Dihantar & Dikunci')
                        ->body("Bidaan {$bid->bid_reference} telah selamat dimasukkan ke dalam Peti Tender Digital. Slip Akuan Penerimaan (PDF) telah dijana.")
                        ->success()
                        ->send();

                    $this->redirect(BidResource::getUrl('view', ['record' => $bid->id]));
                });

            $actions[] = DeleteAction::make()
                ->label('Padam Draf Bidaan');
        } else {
            if ($bid->submission_slip_pdf_path) {
                $actions[] = Action::make('download_slip')
                    ->label('Muat Turun Slip Akuan Bidaan (PDF)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(route('vendor.bids.slip', $bid->id), shouldOpenInNewTab: true);
            }
        }

        return $actions;
    }

    protected function afterSave(): void
    {
        /** @var Bid $bid */
        $bid = $this->getRecord();
        $data = $this->form->getState();

        if ($bid->status === 'draft') {
            if (! empty($data['technical_proposal_file'])) {
                $this->updateBidDoc($bid, 'technical_proposal', $data['technical_proposal_file'], false);
            }

            if (! empty($data['financial_proposal_file'])) {
                $this->updateBidDoc($bid, 'financial_proposal', $data['financial_proposal_file'], true);
            }

            AuditTrail::log(
                eventType: 'BID_DRAFT_UPDATED',
                description: "Draf bidaan {$bid->bid_reference} dikemaskini oleh pembekal",
                auditable: $bid,
                userId: auth()->id()
            );
        }
    }

    protected function updateBidDoc(Bid $bid, string $type, string $filePath, bool $isFinancial): void
    {
        $disk = Storage::disk('private');
        if ($disk->exists($filePath)) {
            $content = $disk->get($filePath);
            $checksum = hash('sha256', $content);
            $size = $disk->size($filePath);
            $mime = $disk->mimeType($filePath) ?? 'application/pdf';
            $fileName = basename($filePath);

            BidDocument::updateOrCreate(
                ['bid_id' => $bid->id, 'type' => $type],
                [
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_size' => $size,
                    'mime_type' => $mime,
                    'sha256_checksum' => $checksum,
                    'is_financial' => $isFinancial,
                    'disk' => 'private',
                ]
            );
        }
    }
}
