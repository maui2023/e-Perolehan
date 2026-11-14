<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Bid;
use App\Models\BidDocument;
use App\Models\Tender;
use App\Models\TenderBoxWitness;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SealedBiddingService
{
    public function __construct(
        protected PdfReportService $pdfService
    ) {}

    /**
     * Penyerahan Bidaan Bertutup oleh Vendor
     */
    public function submitBid(Vendor $vendor, Tender $tender, array $data, array $files): Bid
    {
        if (! $tender->isOpenForBidding()) {
            throw new \RuntimeException('Tender ini tidak lagi dibuka untuk penyerahan bidaan.');
        }

        if (! $vendor->isVerified()) {
            throw new \RuntimeException('Profil syarikat vendor belum disahkan atau tidak memenuhi syarat kelayakan.');
        }

        if ($tender->document_price > 0 && ! $vendor->hasPurchasedTender($tender->id)) {
            throw new \RuntimeException('Sila buat pembelian dokumen tender terlebih dahulu sebelum menghantar bidaan.');
        }

        return DB::transaction(function () use ($vendor, $tender, $data, $files) {
            $existingBid = Bid::where('tender_id', $tender->id)
                ->where('vendor_id', $vendor->id)
                ->first();

            $bid = $existingBid ?? new Bid;
            $bid->tender_id = $tender->id;
            $bid->vendor_id = $vendor->id;
            $bid->bid_amount = (float) $data['bid_amount']; // Auto-encrypts via mutator
            $bid->completion_duration_months = (int) ($data['completion_duration_months'] ?? 12);
            $bid->technical_summary = $data['technical_summary'] ?? null;
            $bid->status = 'sealed';
            $bid->submitted_at = now();
            $bid->save();

            $fileHashes = [];

            // Process uploaded documents
            foreach ($files as $type => $file) {
                if ($file instanceof UploadedFile) {
                    $originalName = $file->getClientOriginalName();
                    $mime = $file->getClientMimeType();
                    $size = $file->getSize();
                    $content = file_get_contents($file->getRealPath());
                    $checksum = hash('sha256', $content);
                    $fileHashes[] = $checksum;

                    $isFinancial = in_array($type, ['financial_proposal', 'commercial_breakdown', 'pricing_schedule']);

                    $fileName = sprintf('%s_%s_%s.%s', $type, time(), Str::random(6), $file->getClientOriginalExtension());
                    $path = sprintf('bids/%d/%d/%s', $tender->id, $vendor->id, $fileName);

                    Storage::disk('private')->put($path, $content);

                    BidDocument::create([
                        'bid_id' => $bid->id,
                        'type' => $type,
                        'file_path' => $path,
                        'file_name' => $originalName,
                        'file_size' => $size,
                        'mime_type' => $mime,
                        'sha256_checksum' => $checksum,
                        'is_financial' => $isFinancial,
                        'disk' => 'private',
                    ]);
                }
            }

            // Generate tamper-evident submission package hash
            $packageContent = json_encode([
                'bid_ref' => $bid->bid_reference,
                'tender_ref' => $tender->reference_no,
                'vendor_ssm' => $vendor->registration_number,
                'encrypted_price' => $bid->encrypted_bid_amount,
                'files' => $fileHashes,
                'submitted_at' => $bid->submitted_at->toIso8601String(),
            ]);

            $packageHash = hash('sha256', $packageContent);
            $bid->update(['sha256_package_hash' => $packageHash]);

            // Generate official submission slip PDF
            $this->pdfService->generateBidSubmissionSlipPdf($bid);

            AuditTrail::log(
                eventType: 'BID_SUBMITTED_SEALED',
                description: "Bidaan bertutup {$bid->bid_reference} diserahkan untuk tender {$tender->reference_no}",
                auditable: $bid,
                newValues: [
                    'bid_reference' => $bid->bid_reference,
                    'package_hash' => $packageHash,
                    'files_count' => count($fileHashes),
                ],
                userId: $vendor->user_id
            );

            return $bid;
        });
    }

    /**
     * Prosedur Pengesahan Saksi Digital & Pembukaan Peti Tender (Dual-Witness Protocol)
     */
    public function affirmWitnessOpening(Tender $tender, User $witnessUser, ?string $remarks = null): array
    {
        if (! in_array($witnessUser->role, ['saksi_pembuka', 'admin', 'urus_setia'])) {
            throw new \RuntimeException('Pengguna ini tidak mempunyai peranan saksi pembuka peti tender yang sah.');
        }

        if ($tender->witnesses()->where('user_id', $witnessUser->id)->exists()) {
            throw new \RuntimeException('Anda telah pun mengesahkan pembukaan peti tender ini.');
        }

        $ip = request()->ip() ?? '127.0.0.1';
        $ua = request()->userAgent() ?? 'System';
        $now = now();

        $signaturePayload = hash('sha256', sprintf(
            'WITNESS|%s|%s|%s|%s|%s',
            $tender->reference_no,
            $witnessUser->id,
            $witnessUser->email,
            $now->toIso8601String(),
            $ip
        ));

        $witnessRecord = TenderBoxWitness::create([
            'tender_id' => $tender->id,
            'user_id' => $witnessUser->id,
            'witness_role' => $witnessUser->role,
            'digital_signature_hash' => $signaturePayload,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'witness_remarks' => $remarks,
            'confirmed_at' => $now,
        ]);

        AuditTrail::log(
            eventType: 'TENDER_WITNESS_AFFIRMED',
            description: "Pegawai saksi {$witnessUser->name} mengesahkan pembukaan peti tender {$tender->reference_no}",
            auditable: $tender,
            newValues: ['witness_id' => $witnessUser->id, 'signature' => $signaturePayload],
            userId: $witnessUser->id
        );

        $totalWitnesses = $tender->witnesses()->count();

        // Check if dual-witness threshold reached (>= 2 distinct witnesses)
        if ($totalWitnesses >= 2 && ! $tender->isBoxOpened()) {
            $this->unlockTenderBox($tender);

            return [
                'status' => 'box_unlocked',
                'witnesses_count' => $totalWitnesses,
                'message' => 'Dua saksi telah lengkap. Peti tender telah dibuka dan maklumat bidaan telah dinyahsulit.',
            ];
        }

        return [
            'status' => 'awaiting_second_witness',
            'witnesses_count' => $totalWitnesses,
            'message' => 'Saksi pertama disahkan. Menunggu pengesahan saksi kedua untuk membuka peti tender.',
        ];
    }

    /**
     * Membuka Peti Tender, Menyahsulit Harga, dan Menetapkan Kod Blind Pembida
     */
    protected function unlockTenderBox(Tender $tender): void
    {
        DB::transaction(function () use ($tender) {
            $bids = $tender->bids()->get()->shuffle();

            $alphabet = range('A', 'Z');
            $index = 0;

            foreach ($bids as $bid) {
                $blindCode = 'PEMBIDA-'.($alphabet[$index % 26]).($index >= 26 ? (int) ($index / 26) : '');
                $index++;

                $decryptedAmount = null;
                try {
                    $decryptedAmount = (float) Crypt::decryptString($bid->encrypted_bid_amount);
                } catch (\Exception $e) {
                    $decryptedAmount = null;
                }

                $bid->update([
                    'blind_code' => $blindCode,
                    'decrypted_bid_amount' => $decryptedAmount,
                    'status' => 'opened',
                    'opened_at' => now(),
                ]);
            }

            $tender->update([
                'status' => 'box_opened',
                'dual_witness_opened_at' => now(),
            ]);

            // Generate official Opening Slip PDF
            $this->pdfService->generateTenderOpeningSlipPdf($tender);

            AuditTrail::log(
                eventType: 'TENDER_BOX_UNLOCKED',
                description: "Peti tender {$tender->reference_no} telah dibuka secara rasmi melalui protokol 2 saksi. {$bids->count()} bidaan dinyahsulit.",
                auditable: $tender,
                newValues: ['status' => 'box_opened', 'bids_opened' => $bids->count()],
            );
        });
    }
}
