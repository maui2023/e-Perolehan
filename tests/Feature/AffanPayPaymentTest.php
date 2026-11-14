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

        // 1st webhook call: processes and marks as paid
        $response1 = $this->postJson(route('affanpay.webhook'), $payload);
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
        $response2 = $this->postJson(route('affanpay.webhook'), $payload);
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
}
