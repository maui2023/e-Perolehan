<?php

namespace App\Jobs;

use App\Models\AuditTrail;
use App\Models\BidDocument;
use App\Models\Tender;
use App\Models\TenderDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArchiveTenderToMinioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $tenderId
    ) {}

    public function handle(): void
    {
        $tender = Tender::find($this->tenderId);
        if (! $tender) {
            Log::warning("ArchiveTenderToMinioJob: Tender {$this->tenderId} not found.");

            return;
        }

        Log::info("ArchiveTenderToMinioJob: Memulakan pemindahan arkib sejuk MinIO bagi tender {$tender->reference_no}");

        $privateDisk = Storage::disk('private');
        $minioDisk = Storage::disk('minio');

        $archivedCount = 0;
        $failedCount = 0;

        // 1. Pindahkan Dokumen Tender
        $tenderDocs = TenderDocument::where('tender_id', $tender->id)
            ->where('disk', 'private')
            ->get();

        foreach ($tenderDocs as $doc) {
            if (! $privateDisk->exists($doc->file_path)) {
                continue;
            }

            try {
                $content = $privateDisk->get($doc->file_path);
                $sourceHash = hash('sha256', $content);
                $targetPath = "cold-archive/{$tender->id}/tender-docs/{$doc->file_name}";

                $minioDisk->put($targetPath, $content);

                // Pengesahan integriti SHA-256
                $destContent = $minioDisk->get($targetPath);
                $destHash = hash('sha256', $destContent);

                if (hash_equals($sourceHash, $destHash)) {
                    $doc->update([
                        'disk' => 'minio',
                        'file_path' => $targetPath,
                        'sha256_checksum' => $sourceHash,
                    ]);
                    $privateDisk->delete($doc->file_path);
                    $archivedCount++;
                } else {
                    Log::error("ArchiveTenderToMinioJob: Ralat hash integriti bagi tender doc {$doc->id}");
                    $failedCount++;
                }
            } catch (\Exception $e) {
                Log::warning("ArchiveTenderToMinioJob: Gagal memindahkan tender doc {$doc->id} ke MinIO: ".$e->getMessage());
                $failedCount++;
            }
        }

        // 2. Pindahkan Dokumen Bidaan (Bids)
        $bidIds = $tender->bids()->pluck('id');
        $bidDocs = BidDocument::whereIn('bid_id', $bidIds)
            ->where('disk', 'private')
            ->get();

        foreach ($bidDocs as $doc) {
            if (! $privateDisk->exists($doc->file_path)) {
                continue;
            }

            try {
                $content = $privateDisk->get($doc->file_path);
                $sourceHash = hash('sha256', $content);
                $targetPath = "cold-archive/{$tender->id}/bids/{$doc->bid_id}/{$doc->file_name}";

                $minioDisk->put($targetPath, $content);

                // Pengesahan integriti SHA-256
                $destContent = $minioDisk->get($targetPath);
                $destHash = hash('sha256', $destContent);

                if (hash_equals($sourceHash, $destHash)) {
                    $doc->update([
                        'disk' => 'minio',
                        'file_path' => $targetPath,
                        'sha256_checksum' => $sourceHash,
                    ]);
                    $privateDisk->delete($doc->file_path);
                    $archivedCount++;
                } else {
                    Log::error("ArchiveTenderToMinioJob: Ralat integriti hash bagi bid doc {$doc->id}");
                    $failedCount++;
                }
            } catch (\Exception $e) {
                Log::warning("ArchiveTenderToMinioJob: Gagal memindahkan bid doc {$doc->id} ke MinIO: ".$e->getMessage());
                $failedCount++;
            }
        }

        if ($archivedCount > 0) {
            $tender->update(['archived_to_minio_at' => now()]);

            AuditTrail::log(
                eventType: 'COLD_STORAGE_ARCHIVED_MINIO',
                description: "{$archivedCount} fail bagi tender {$tender->reference_no} berjaya dipindahkan ke MinIO Cold Storage dengan verifikasi SHA-256 (Gagal: {$failedCount})",
                auditable: $tender,
                newValues: [
                    'archived_count' => $archivedCount,
                    'failed_count' => $failedCount,
                    'archived_at' => now()->toIso8601String(),
                ]
            );
        }
    }
}
