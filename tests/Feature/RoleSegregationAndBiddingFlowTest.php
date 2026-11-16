<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Tender;
use App\Models\TenderPurchase;
use App\Models\User;
use App\Models\Vendor;
use App\Services\SealedBiddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleSegregationAndBiddingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('private');
    }

    public function test_only_admin_and_urus_setia_can_create_tenders(): void
    {
        $admin = User::where('role', 'admin')->first();
        $urusSetia = User::where('role', 'urus_setia')->first();
        $vendorUser = User::where('role', 'vendor')->first();
        $penilaiUser = User::where('role', 'penilai_teknikal')->first();

        // Admin & Urus Setia allowed
        $this->assertTrue(Gate::forUser($admin)->allows('create', Tender::class));
        $this->assertTrue(Gate::forUser($urusSetia)->allows('create', Tender::class));

        // Vendor & Penilai strictly forbidden
        $this->assertFalse(Gate::forUser($vendorUser)->allows('create', Tender::class));
        $this->assertFalse(Gate::forUser($penilaiUser)->allows('create', Tender::class));
    }

    public function test_vendor_and_penilai_cannot_update_or_delete_tenders(): void
    {
        $admin = User::where('role', 'admin')->first();
        $vendorUser = User::where('role', 'vendor')->first();
        $penilaiUser = User::where('role', 'penilai_teknikal')->first();
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();

        // Admin can update & delete
        $this->assertTrue(Gate::forUser($admin)->allows('update', $tender));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $tender));

        // Vendor forbidden
        $this->assertFalse(Gate::forUser($vendorUser)->allows('update', $tender));
        $this->assertFalse(Gate::forUser($vendorUser)->allows('delete', $tender));

        // Penilai forbidden
        $this->assertFalse(Gate::forUser($penilaiUser)->allows('update', $tender));
        $this->assertFalse(Gate::forUser($penilaiUser)->allows('delete', $tender));
    }

    public function test_vendor_cannot_access_tender_create_page_in_vendor_panel(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();

        // Create page does not exist in vendor panel
        $response = $this->actingAs($vendorUser)->get('/vendor/tenders/create');
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_only_verified_vendors_can_create_bids(): void
    {
        $admin = User::where('role', 'admin')->first();
        $urusSetia = User::where('role', 'urus_setia')->first();
        $penilai = User::where('role', 'penilai_teknikal')->first();
        $verifiedVendorUser = User::where('role', 'vendor')->whereHas('vendor', function ($q) {
            $q->where('compliance_status', 'verified');
        })->first();

        // Admin, Urus Setia, and Penilai can NEVER create bids
        $this->assertFalse(Gate::forUser($admin)->allows('create', Bid::class));
        $this->assertFalse(Gate::forUser($urusSetia)->allows('create', Bid::class));
        $this->assertFalse(Gate::forUser($penilai)->allows('create', Bid::class));

        // Verified Vendor is allowed
        $this->assertTrue(Gate::forUser($verifiedVendorUser)->allows('create', Bid::class));

        // Unverified Vendor is forbidden
        $unverifiedVendor = Vendor::where('compliance_status', 'pending')->first();
        if ($unverifiedVendor) {
            $this->assertFalse(Gate::forUser($unverifiedVendor->user)->allows('create', Bid::class));
        }
    }

    public function test_vendor_bid_creation_encrypts_price_with_aes_256(): void
    {
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();
        $vendor = Vendor::where('compliance_status', 'verified')->first();

        // Ensure purchase
        TenderPurchase::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendor->id],
            [
                'purchase_reference' => 'TEST-PURCHASE-AES',
                'amount' => $tender->document_price,
                'status' => 'paid',
                'paid_at' => now(),
                'idempotency_key' => 'idemp-aes-1',
            ]
        );

        $bid = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor->id,
            'bid_amount' => 450000.00,
            'completion_duration_months' => 12,
            'technical_summary' => 'Penyelesaian rangkaian & pelayan komprehensif.',
            'status' => 'draft',
        ]);

        $this->assertNotNull($bid);
        $this->assertEquals('draft', $bid->status);

        // Price is encrypted at rest and decrypted_bid_amount is null
        $this->assertNotEmpty($bid->encrypted_bid_amount);
        $this->assertNull($bid->decrypted_bid_amount);

        // Can be decrypted
        $this->assertEquals(450000.00, (float) Crypt::decryptString($bid->encrypted_bid_amount));
    }

    public function test_vendor_can_update_draft_bid_but_cannot_update_once_sealed(): void
    {
        $vendor = Vendor::where('compliance_status', 'verified')->first();
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();

        $bid = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor->id,
            'bid_amount' => 380000.00,
            'completion_duration_months' => 6,
            'status' => 'draft',
        ]);

        // Vendor can update while in draft
        $this->assertTrue(Gate::forUser($vendor->user)->allows('update', $bid));
        $this->assertTrue(Gate::forUser($vendor->user)->allows('delete', $bid));

        // Lock/seal the bid
        $bid->status = 'sealed';
        $bid->submitted_at = now();
        $bid->sha256_package_hash = hash('sha256', 'package_test');
        $bid->save();

        // Once sealed, NO ONE (including the vendor) can update or delete
        $this->assertFalse(Gate::forUser($vendor->user)->allows('update', $bid));
        $this->assertFalse(Gate::forUser($vendor->user)->allows('delete', $bid));

        $admin = User::where('role', 'admin')->first();
        $this->assertFalse(Gate::forUser($admin)->allows('update', $bid));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $bid));
    }

    public function test_vendor_cannot_view_or_update_another_vendors_bid(): void
    {
        $vendor1 = Vendor::where('compliance_status', 'verified')->first();
        $vendor2 = Vendor::where('compliance_status', 'verified')->where('id', '!=', $vendor1->id)->first();
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();

        $bid1 = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor1->id,
            'bid_amount' => 500000.00,
            'status' => 'draft',
        ]);

        // Vendor 1 can view & update their own bid
        $this->assertTrue(Gate::forUser($vendor1->user)->allows('view', $bid1));
        $this->assertTrue(Gate::forUser($vendor1->user)->allows('update', $bid1));

        // Vendor 2 CANNOT view or update Vendor 1's bid
        $this->assertFalse(Gate::forUser($vendor2->user)->allows('view', $bid1));
        $this->assertFalse(Gate::forUser($vendor2->user)->allows('update', $bid1));
    }

    public function test_admin_can_view_bids_list_and_sealed_price_is_protected(): void
    {
        $admin = User::where('role', 'admin')->first();
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();
        $vendor = Vendor::where('compliance_status', 'verified')->first();

        $bid = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor->id,
            'bid_amount' => 520000.00,
            'status' => 'sealed',
            'submitted_at' => now(),
            'sha256_package_hash' => hash('sha256', 'hash_test_sealed'),
        ]);

        // Admin is allowed to view bids list and view the bid record
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Bid::class));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $bid));

        // Price is sealed: decrypted_bid_amount is null
        $this->assertNull($bid->decrypted_bid_amount);

        // Accessing admin bids table
        $response = $this->actingAs($admin)->get('/admin/bids');
        $response->assertSuccessful();
        $response->assertSee($bid->bid_reference);
        $response->assertSee('TERKUNCI (AES-256)');
    }

    public function test_dual_witness_unlocking_reveals_decrypted_price_to_evaluators_and_admin(): void
    {
        $tender = Tender::where('reference_no', 'TDR-2026-0001')->first();
        $vendor1 = Vendor::where('company_name', 'like', '%Bina Maju%')->first();
        $vendor2 = Vendor::where('company_name', 'like', '%ICT Global%')->first();

        $bid1 = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor1->id,
            'bid_amount' => 410000.00,
            'status' => 'sealed',
            'submitted_at' => now(),
        ]);

        $bid2 = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor2->id,
            'bid_amount' => 395000.00,
            'status' => 'sealed',
            'submitted_at' => now(),
        ]);

        $saksi1 = User::where('email', 'saksi1@organisasi.my')->first();
        $saksi2 = User::where('email', 'saksi2@organisasi.my')->first();
        $service = app(SealedBiddingService::class);

        // Witness 1 affirms
        $res1 = $service->affirmWitnessOpening($tender, $saksi1);
        $this->assertEquals('awaiting_second_witness', $res1['status']);

        // Witness 2 affirms -> box unlocked!
        $res2 = $service->affirmWitnessOpening($tender, $saksi2);
        $this->assertEquals('box_unlocked', $res2['status']);

        $bid1->refresh();
        $bid2->refresh();

        // Bids are now opened with decrypted prices and blind codes assigned
        $this->assertEquals('opened', $bid1->status);
        $this->assertEquals(410000.00, (float) $bid1->decrypted_bid_amount);
        $this->assertNotEmpty($bid1->blind_code);

        $this->assertEquals('opened', $bid2->status);
        $this->assertEquals(395000.00, (float) $bid2->decrypted_bid_amount);
        $this->assertNotEmpty($bid2->blind_code);

        // Now in Admin panel, the decrypted price is displayed
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/bids');
        $response->assertSuccessful();
        $response->assertSee('RM 410,000.00');
        $response->assertSee('RM 395,000.00');
    }
}
