<?php

namespace Tests\Feature;

use App\Models\AuditTrail;
use App\Models\Tender;
use App\Models\TenderPurchase;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffanPayPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_vendor_can_initiate_tender_purchase(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();
        $this->assertNotNull($vendorUser);

        $tender = Tender::where('status', 'published')->where('document_price', '>', 0)->first();
        $this->assertNotNull($tender);

        $response = $this->actingAs($vendorUser)->post(route('tenders.purchase', $tender->id));

        // In simulation mode, should redirect to simulation URL or away
        $response->assertStatus(302);

        $purchase = TenderPurchase::where('tender_id', $tender->id)
            ->where('vendor_id', $vendorUser->vendor->id)
            ->first();

        $this->assertNotNull($purchase);
        $this->assertEquals($tender->document_price, $purchase->amount);
        $this->assertNotNull($purchase->idempotency_key);
    }

    public function test_affanpay_simulation_gateway_page(): void
    {
        $purchase = TenderPurchase::where('status', 'pending')->first();
        if (! $purchase) {
            $tender = Tender::first();
            $vendor = Vendor::first();
            $purchase = TenderPurchase::create([
                'tender_id' => $tender->id,
                'vendor_id' => $vendor->id,
                'purchase_reference' => 'TEST-PURCHASE-01',
                'amount' => 50.00,
                'status' => 'pending',
                'idempotency_key' => 'idemp-01',
            ]);
        }

        $response = $this->get(route('affanpay.simulate', $purchase->purchase_reference));
        $response->assertStatus(200);
        $response->assertSee('AffanPay Sandbox Simulator');
        $response->assertSee($purchase->purchase_reference);
    }

    public function test_affanpay_simulation_success_action(): void
    {
        $purchase = TenderPurchase::create([
            'tender_id' => Tender::first()->id,
            'vendor_id' => Vendor::first()->id,
            'purchase_reference' => 'SIM-TEST-REF-99',
            'amount' => 50.00,
            'status' => 'pending',
            'idempotency_key' => 'idemp-sim-99',
        ]);

        $response = $this->post(route('affanpay.simulate.post', $purchase->purchase_reference), [
            'action' => 'success',
        ]);

        $response->assertStatus(302);

        $purchase->refresh();
        $this->assertTrue($purchase->isPaid());
        $this->assertNotNull($purchase->paid_at);
        $this->assertNotNull($purchase->receipt_pdf_path);
    }

    public function test_affanpay_webhook_processing_and_idempotency(): void
    {
        $purchase = TenderPurchase::create([
            'tender_id' => Tender::first()->id,
            'vendor_id' => Vendor::first()->id,
            'purchase_reference' => 'WEBHOOK-TEST-REF',
            'amount' => 100.00,
            'status' => 'pending',
            'idempotency_key' => 'idemp-wh-01',
        ]);

        $payload = [
            'status' => 'paid',
            'external_ref' => 'WEBHOOK-TEST-REF',
            'payment_ref' => 'AFFAN-PAY-WH-1234',
            'amount' => 100.00,
        ];

        // 1st webhook call: processes and marks as paid with valid webhook signature
        $response1 = $this->withHeader('X-AffanPay-Signature', config('affanpay.webhook_secret'))
            ->postJson(route('affanpay.webhook'), $payload);
        $response1->assertStatus(200);
        $response1->assertJsonPath('success', true);
        $response1->assertJsonPath('result.status', 'paid_processed');

        $purchase->refresh();
        $this->assertTrue($purchase->isPaid());
        $this->assertEquals('AFFAN-PAY-WH-1234', $purchase->affanpay_payment_ref);

        // Verify audit trail logged
        $this->assertTrue(
            AuditTrail::where('event_type', 'AFFANPAY_PAYMENT_SUCCESS')
                ->where('description', 'like', '%WEBHOOK-TEST-REF%')
                ->exists()
        );

        // 2nd webhook call (idempotency check): should return already_paid without re-processing
        $response2 = $this->withHeader('X-AffanPay-Signature', config('affanpay.webhook_secret'))
            ->postJson(route('affanpay.webhook'), $payload);
        $response2->assertStatus(200);
        $response2->assertJsonPath('result.status', 'already_paid');
    }

    public function test_download_receipt_authorization(): void
    {
        $purchase = TenderPurchase::where('status', 'paid')->first();
        $this->assertNotNull($purchase);

        // Guest cannot download receipt
        $guestResponse = $this->get(route('purchases.receipt', $purchase->id));
        $guestResponse->assertStatus(403);

        // Owner vendor can download receipt
        $ownerUser = $purchase->vendor->user;
        $ownerResponse = $this->actingAs($ownerUser)->get(route('purchases.receipt', $purchase->id));
        $ownerResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $ownerResponse->headers->get('content-type'));
    }

    public function test_free_tender_purchase_redirects_directly_to_create_bid_with_tender_id(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();
        $tender = Tender::where('status', 'published')->where('document_price', '<=', 0)->first();

        if (! $tender) {
            $tender = Tender::create([
                'reference_no' => 'TDR-FREE-TEST',
                'title' => 'Tender Percuma Ujian',
                'category' => 'service',
                'status' => 'published',
                'document_price' => 0.00,
                'closing_at' => now()->addDays(14),
            ]);
        }

        $response = $this->actingAs($vendorUser)->get(route('tenders.purchase', $tender->id));

        $expectedUrl = route('filament.vendor.resources.bids.create', ['tender_id' => $tender->id]);
        $response->assertRedirect($expectedUrl);

        $this->assertDatabaseHas('tender_purchases', [
            'tender_id' => $tender->id,
            'vendor_id' => $vendorUser->vendor->id,
            'status' => 'paid',
        ]);
    }

    public function test_already_purchased_tender_redirects_directly_to_create_bid_with_tender_id(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();
        $tender = Tender::where('status', 'published')->first();

        TenderPurchase::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendorUser->vendor->id],
            [
                'purchase_reference' => 'TEST-PURCHASE-ALREADY',
                'amount' => $tender->document_price,
                'status' => 'paid',
                'paid_at' => now(),
                'idempotency_key' => 'idemp-already-1',
            ]
        );

        $response = $this->actingAs($vendorUser)->get(route('tenders.purchase', $tender->id));

        $expectedUrl = route('filament.vendor.resources.bids.create', ['tender_id' => $tender->id]);
        $response->assertRedirect($expectedUrl);
    }

    public function test_return_callback_after_payment_redirects_to_create_bid_with_tender_id(): void
    {
        $vendor = Vendor::first();
        $tender = Tender::first();

        $purchase = TenderPurchase::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor->id,
            'purchase_reference' => 'RETURN-TEST-REF',
            'amount' => 50.00,
            'status' => 'paid',
            'paid_at' => now(),
            'idempotency_key' => 'idemp-return-01',
        ]);

        $response = $this->actingAs($vendor->user)->get(route('affanpay.return', $purchase->purchase_reference));

        $expectedUrl = route('filament.vendor.resources.bids.create', ['tender_id' => $purchase->tender_id]);
        $response->assertRedirect($expectedUrl);
    }

    public function test_vendor_tender_view_action_points_to_purchase_route(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();
        $tender = Tender::where('status', 'published')->where('document_price', '>', 0)->first();

        // Ensure not purchased yet
        TenderPurchase::where('tender_id', $tender->id)
            ->where('vendor_id', $vendorUser->vendor->id)
            ->delete();

        $response = $this->actingAs($vendorUser)->get(route('filament.vendor.resources.tenders.view', $tender->id));
        $response->assertStatus(200);
        $response->assertSee(route('tenders.purchase', $tender->id));
    }

    public function test_create_bid_page_loads_with_tender_id_query_param(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();
        $tender = Tender::where('status', 'published')->first();

        // Ensure purchased
        TenderPurchase::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendorUser->vendor->id],
            [
                'purchase_reference' => 'TEST-PURCHASE-LOAD',
                'amount' => $tender->document_price,
                'status' => 'paid',
                'paid_at' => now(),
                'idempotency_key' => 'idemp-load-1',
            ]
        );

        $response = $this->actingAs($vendorUser)->get(route('filament.vendor.resources.bids.create', ['tender_id' => $tender->id]));
        $response->assertStatus(200);
        $response->assertSee($tender->reference_no);
    }
}
