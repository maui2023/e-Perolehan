<?php

namespace App\Services;

use App\Jobs\ArchiveTenderToMinioJob;
use App\Models\AuditTrail;
use App\Models\Bid;
use App\Models\Evaluation;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    public function __construct(
        protected PdfReportService $pdfService
    ) {}

    /**
     * Fasa 1: Penilaian Teknikal Blind
     */
    public function evaluateTechnical(Bid $bid, User $evaluator, array $scores, ?string $comments = null): Evaluation
    {
        $tender = $bid->tender;

        // Ensure tender box has been unlocked
        if (! $tender->isBoxOpened()) {
            throw new \RuntimeException('Peti tender belum dibuka. Penilaian teknikal tidak dibenarkan.');
        }

        $c1 = (float) ($scores['score_criteria_1'] ?? 0); // e.g. Spesifikasi (Max 40)
        $c2 = (float) ($scores['score_criteria_2'] ?? 0); // e.g. Rekod Prestasi (Max 30)
        $c3 = (float) ($scores['score_criteria_3'] ?? 0); // e.g. Metodologi (Max 30)
        $total = min(100, $c1 + $c2 + $c3);

        $isPassed = $total >= $tender->min_technical_score;

        return DB::transaction(function () use ($bid, $tender, $evaluator, $c1, $c2, $c3, $total, $isPassed, $comments) {
            $evaluation = Evaluation::updateOrCreate(
                [
                    'tender_id' => $tender->id,
                    'bid_id' => $bid->id,
                    'evaluator_user_id' => $evaluator->id,
                    'evaluation_type' => 'technical',
                ],
                [
                    'score_criteria_1' => $c1,
                    'score_criteria_2' => $c2,
                    'score_criteria_3' => $c3,
                    'total_score' => $total,
                    'is_passed' => $isPassed,
                    'comments' => $comments,
                    'completed_at' => now(),
                    'digital_sign_hash' => hash('sha256', "TECH|{$bid->id}|{$evaluator->id}|{$total}|".now()->toIso8601String()),
                ]
            );

            // Compute average technical score across all evaluators for this bid
            $avgTechScore = Evaluation::where('bid_id', $bid->id)
                ->where('evaluation_type', 'technical')
                ->avg('total_score');

            $bid->update([
                'technical_score' => round($avgTechScore, 2),
                'status' => $avgTechScore >= $tender->min_technical_score ? 'passed_technical' : 'failed_technical',
            ]);

            AuditTrail::log(
                eventType: 'TECHNICAL_EVALUATION_SUBMITTED',
                description: "Penilai {$evaluator->name} mengemukakan skor teknikal {$total}% bagi {$bid->blind_code}",
                auditable: $bid,
                newValues: ['score' => $total, 'passed' => $isPassed],
                userId: $evaluator->id
            );

            return $evaluation;
        });
    }

    /**
     * Fasa 2: Penilaian Kewangan / Komersial & Pengiraan Markah Keseluruhan
     */
    public function evaluateFinancial(Bid $bid, User $evaluator, array $scores, ?string $comments = null): Evaluation
    {
        $tender = $bid->tender;

        if ($bid->technical_score === null || $bid->technical_score < $tender->min_technical_score) {
            throw new \RuntimeException("Bidaan {$bid->blind_code} tidak lulus ambang teknikal ({$tender->min_technical_score}%). Penilaian kewangan tidak dibenarkan.");
        }

        // Standard financial formula: Lowest price gets 100%, others scored proportionally
        $qualifiedBids = $tender->bids()
            ->whereNotNull('decrypted_bid_amount')
            ->where('decrypted_bid_amount', '>', 0)
            ->where('technical_score', '>=', $tender->min_technical_score)
            ->get();

        $lowestPrice = $qualifiedBids->min('decrypted_bid_amount');
        $bidPrice = (float) $bid->decrypted_bid_amount;

        $financialScore = 0;
        if ($bidPrice > 0 && $lowestPrice > 0) {
            $financialScore = round(($lowestPrice / $bidPrice) * 100, 2);
        }

        return DB::transaction(function () use ($bid, $tender, $evaluator, $financialScore, $comments) {
            $evaluation = Evaluation::updateOrCreate(
                [
                    'tender_id' => $tender->id,
                    'bid_id' => $bid->id,
                    'evaluator_user_id' => $evaluator->id,
                    'evaluation_type' => 'financial',
                ],
                [
                    'score_criteria_1' => $financialScore,
                    'score_criteria_2' => 0,
                    'score_criteria_3' => 0,
                    'total_score' => $financialScore,
                    'is_passed' => true,
                    'comments' => $comments,
                    'completed_at' => now(),
                    'digital_sign_hash' => hash('sha256', "FIN|{$bid->id}|{$evaluator->id}|{$financialScore}|".now()->toIso8601String()),
                ]
            );

            // Combined scoring: 70% Technical + 30% Financial
            $techScore = (float) $bid->technical_score;
            $combinedTotal = round(($techScore * 0.70) + ($financialScore * 0.30), 2);

            $bid->update([
                'financial_score' => $financialScore,
                'total_score' => $combinedTotal,
            ]);

            AuditTrail::log(
                eventType: 'FINANCIAL_EVALUATION_COMPLETED',
                description: "Penilaian kewangan bagi {$bid->blind_code}: Skor Komersial {$financialScore}%, Skor Keseluruhan {$combinedTotal}%",
                auditable: $bid,
                newValues: ['financial_score' => $financialScore, 'total_score' => $combinedTotal],
                userId: $evaluator->id
            );

            return $evaluation;
        });
    }

    /**
     * Muktamadkan Keputusan Award Tender
     */
    public function awardTender(Tender $tender, Bid $winningBid, ?string $awardNotes = null, ?User $awarder = null): void
    {
        DB::transaction(function () use ($tender, $winningBid, $awardNotes, $awarder) {
            $tender->update([
                'status' => 'awarded',
                'awarded_bid_id' => $winningBid->id,
                'awarded_at' => now(),
                'award_notes' => $awardNotes,
            ]);

            // Update winning and losing bids
            $winningBid->update(['status' => 'awarded']);

            $tender->bids()
                ->where('id', '!=', $winningBid->id)
                ->update(['status' => 'rejected']);

            // Auto-generate official evaluation report and SST PDF
            $this->pdfService->generateEvaluationReportPdf($tender);
            $this->pdfService->generateSstLetterPdf($tender);

            AuditTrail::log(
                eventType: 'TENDER_AWARDED',
                description: "Tender {$tender->reference_no} telah diawardkan kepada {$winningBid->vendor->company_name} (RM ".number_format($winningBid->decrypted_bid_amount, 2).')',
                auditable: $tender,
                newValues: [
                    'awarded_vendor' => $winningBid->vendor->company_name,
                    'contract_amount' => $winningBid->decrypted_bid_amount,
                ],
                userId: $awarder?->id ?? auth()->id()
            );

            // Automatically dispatch cold storage archive job to MinIO!
            ArchiveTenderToMinioJob::dispatch($tender->id);
        });
    }
}
