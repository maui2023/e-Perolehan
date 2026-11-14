<?php

namespace Tests\Feature;

use App\Jobs\ArchiveTenderToMinioJob;
use App\Models\Bid;
use App\Models\Tender;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvaluationAndAwardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('private');
    }

    public function test_double_blind_technical_evaluation_matrix(): void
    {
        $tender = Tender::where('reference_no', 'TDR-2026-0002')->first();
        $this->assertTrue($tender->isBoxOpened());

        $bid = $tender->bids()->where('blind_code', 'PEMBIDA-A')->first();
        $this->assertNotNull($bid);

        $evaluator = User::where('role', 'penilai_teknikal')->first();
        $service = app(EvaluationService::class);

        // Submit technical scores: 36 + 24 + 25 = 85 (above threshold of 75)
        $scores = [
            'score_criteria_1' => 36.0,
            'score_criteria_2' => 24.0,
            'score_criteria_3' => 25.0,
        ];

        $evaluation = $service->evaluateTechnical($bid, $evaluator, $scores, 'Pematuhan teknikal yang baik.');

        $this->assertEquals(85.00, $evaluation->total_score);
        $this->assertTrue($evaluation->is_passed);
        $this->assertNotNull($evaluation->digital_sign_hash);

        $bid->refresh();
        $this->assertEquals('passed_technical', $bid->status);
        $this->assertGreaterThanOrEqual(75, $bid->technical_score);
    }

    public function test_financial_evaluation_and_combined_scoring(): void
    {
        $tender = Tender::where('reference_no', 'TDR-2026-0002')->first();
        $evaluator = User::where('role', 'penilai_kewangan')->first();
        $service = app(EvaluationService::class);

        // Bid A: Price RM 360,000, Tech 82.5%
        // Bid B: Price RM 345,000 (Lowest), Tech 88.0%
        $bidA = $tender->bids()->where('blind_code', 'PEMBIDA-A')->first();
        $bidB = $tender->bids()->where('blind_code', 'PEMBIDA-B')->first();

        // Evaluate Bid B (Lowest price -> 100%)
        $evalB = $service->evaluateFinancial($bidB, $evaluator, []);
        $bidB->refresh();
        $this->assertEquals(100.00, (float) $bidB->financial_score);
        // Combined total: (88 * 0.70) + (100 * 0.30) = 61.6 + 30.0 = 91.60
        $this->assertEquals(91.60, (float) $bidB->total_score);

        // Evaluate Bid A ((345000 / 360000) * 100 = 95.83%)
        $evalA = $service->evaluateFinancial($bidA, $evaluator, []);
        $bidA->refresh();
        $this->assertEquals(95.83, (float) $bidA->financial_score);
        // Combined total: (82.5 * 0.70) + (95.83 * 0.30) = 57.75 + 28.749 = 86.50
        $this->assertEquals(86.50, (float) $bidA->total_score);

        // Winning bidder is Bid B with higher combined score
        $this->assertGreaterThan($bidA->total_score, $bidB->total_score);
    }

    public function test_award_tender_generates_documents_and_dispatches_minio_archive_job(): void
    {
        Queue::fake();

        $tender = Tender::where('reference_no', 'TDR-2026-0002')->first();
        $admin = User::where('role', 'admin')->first();
        $service = app(EvaluationService::class);

        $winningBid = $tender->bids()->where('blind_code', 'PEMBIDA-B')->first();
        $losingBid = $tender->bids()->where('blind_code', 'PEMBIDA-A')->first();

        $service->awardTender($tender, $winningBid, 'Dianugerahkan kepada pembida skor tertinggi.', $admin);

        $tender->refresh();
        $winningBid->refresh();
        $losingBid->refresh();

        $this->assertTrue($tender->isAwarded());
        $this->assertEquals($winningBid->id, $tender->awarded_bid_id);
        $this->assertEquals('awarded', $winningBid->status);
        $this->assertEquals('rejected', $losingBid->status);

        // Official evaluation report and SST PDFs generated
        $this->assertNotNull($tender->evaluation_report_pdf_path);
        $this->assertNotNull($tender->sst_letter_pdf_path);

        // MinIO cold archival job dispatched
        Queue::assertPushed(ArchiveTenderToMinioJob::class, function ($job) use ($tender) {
            return $job->tenderId === $tender->id;
        });
    }
}
