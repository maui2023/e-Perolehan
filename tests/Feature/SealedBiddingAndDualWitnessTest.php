<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Tender;
use App\Models\TenderPurchase;
use App\Models\User;
use App\Models\Vendor;
use App\Services\SealedBiddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SealedBiddingAndDualWitnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('private');
    }

    public function test_vendor_can_submit_sealed_bid_with_encrypted_amount(): void
    {
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();
        $vendor = Vendor::where('compliance_status', 'verified')->first();

        // Ensure purchase is made
        TenderPurchase::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendor->id],
            [
                'purchase_reference' => 'TEST-PURCHASE-B1',
                'amount' => $tender->document_price,
                'status' => 'paid',
                'paid_at' => now(),
                'idempotency_key' => 'idemp-b1',
            ]
        );

        $service = app(SealedBiddingService::class);

        $data = [
            'bid_amount' => 420000.00,
            'completion_duration_months' => 12,
            'technical_summary' => 'Cadangan penyelesaian lengkap server.',
        ];

        $files = [
            'technical_proposal' => UploadedFile::fake()->create('Cadangan_Teknikal.pdf', 1024, 'application/pdf'),
            'financial_proposal' => UploadedFile::fake()->create('Cadangan_Kewangan.pdf', 512, 'application/pdf'),
        ];

        $bid = $service->submitBid($vendor, $tender, $data, $files);

        $this->assertNotNull($bid);
        $this->assertEquals('sealed', $bid->status);

        // Price MUST be encrypted at rest and decrypted_bid_amount MUST be null
        $this->assertNotEmpty($bid->encrypted_bid_amount);
        $this->assertNull($bid->decrypted_bid_amount);

        // Can be decrypted using Crypt
        $decrypted = (float) Crypt::decryptString($bid->encrypted_bid_amount);
        $this->assertEquals(420000.00, $decrypted);

        // Package hash SHA-256 fingerprint exists
        $this->assertNotNull($bid->sha256_package_hash);
        $this->assertEquals(64, strlen($bid->sha256_package_hash));

        // Documents saved in private storage and recorded in BidDocument
        $this->assertEquals(2, $bid->documents()->count());
        $techDoc = $bid->documents()->where('type', 'technical_proposal')->first();
        $this->assertNotNull($techDoc);
        $this->assertEquals('private', $techDoc->disk);
        $this->assertFalse($techDoc->is_financial);

        $finDoc = $bid->documents()->where('type', 'financial_proposal')->first();
        $this->assertNotNull($finDoc);
        $this->assertTrue($finDoc->is_financial);
    }

    public function test_dual_witness_box_unlock_protocol(): void
    {
        // Use Tender 1 (published / open) and close it for opening test
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();
        $vendor1 = Vendor::where('company_name', 'like', '%Bina Maju%')->first();
        $vendor2 = Vendor::where('company_name', 'like', '%ICT Global%')->first();

        // Create 2 sealed bids
        $bid1 = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor1->id,
            'bid_amount' => 410000.00,
            'status' => 'sealed',
            'submitted_at' => now(),
            'sha256_package_hash' => hash('sha256', 'pkg1'),
        ]);

        $bid2 = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor2->id,
            'bid_amount' => 395000.00,
            'status' => 'sealed',
            'submitted_at' => now(),
            'sha256_package_hash' => hash('sha256', 'pkg2'),
        ]);

        $tender->update(['status' => 'closed', 'closing_at' => now()->subHour()]);

        $saksi1 = User::where('email', 'saksi1@organisasi.my')->first();
        $saksi2 = User::where('email', 'saksi2@organisasi.my')->first();

        $service = app(SealedBiddingService::class);

        // 1st witness affirms
        $res1 = $service->affirmWitnessOpening($tender, $saksi1, 'Saksi 1 bersedia.');
        $this->assertEquals('awaiting_second_witness', $res1['status']);
        $this->assertEquals(1, $res1['witnesses_count']);
        $this->assertFalse($tender->fresh()->isBoxOpened());

        // 2nd witness affirms -> triggers automatic box unlock!
        $res2 = $service->affirmWitnessOpening($tender, $saksi2, 'Saksi 2 mengesahkan.');
        $this->assertEquals('box_unlocked', $res2['status']);
        $this->assertEquals(2, $res2['witnesses_count']);

        $tender->refresh();
        $this->assertTrue($tender->isBoxOpened());
        $this->assertEquals('box_opened', $tender->status);
        $this->assertNotNull($tender->dual_witness_opened_at);

        // Bids are now decrypted and assigned blind codes
        $bid1->refresh();
        $bid2->refresh();

        $this->assertEquals('opened', $bid1->status);
        $this->assertEquals(410000.00, (float) $bid1->decrypted_bid_amount);
        $this->assertStringStartsWith('PEMBIDA-', $bid1->blind_code);

        $this->assertEquals('opened', $bid2->status);
        $this->assertEquals(395000.00, (float) $bid2->decrypted_bid_amount);
        $this->assertStringStartsWith('PEMBIDA-', $bid2->blind_code);

        // Opening slip PDF is generated
        $this->assertNotNull($tender->opening_slip_pdf_path);
    }
}
