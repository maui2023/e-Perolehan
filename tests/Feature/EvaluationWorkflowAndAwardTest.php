<?php

namespace Tests\Feature;

use App\Models\AuditTrail;
use App\Models\Bid;
use App\Models\BidDocument;
use App\Models\Tender;
use App\Models\TenderDocument;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvaluationWorkflowAndAwardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('private');
    }

    public function test_bid_document_download_access_control(): void
    {
        $tender = Tender::where('reference_no', 'TDR-2026-0002')->first();
        $this->assertTrue($tender->isBoxOpened());

        $bid = $tender->bids()->where('blind_code', 'PEMBIDA-A')->first();
        $this->assertNotNull($bid);

        // Put fake files in storage
        Storage::disk('private')->put('bids/test/tech.pdf', 'dummy technical content');
        Storage::disk('private')->put('bids/test/fin.pdf', 'dummy financial content');

        $techDoc = BidDocument::create([
            'bid_id' => $bid->id,
            'type' => 'technical_proposal',
            'file_path' => 'bids/test/tech.pdf',
            'file_name' => 'tech.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'sha256_checksum' => hash('sha256', 'dummy technical content'),
            'is_financial' => false,
            'disk' => 'private',
        ]);

        $finDoc = BidDocument::create([
            'bid_id' => $bid->id,
            'type' => 'financial_proposal',
            'file_path' => 'bids/test/fin.pdf',
            'file_name' => 'fin.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'sha256_checksum' => hash('sha256', 'dummy financial content'),
            'is_financial' => true,
            'disk' => 'private',
        ]);

        $vendorUser = $bid->vendor->user;
        $otherVendorUser = User::where('role', 'vendor')->where('id', '!=', $vendorUser->id)->first();
        $penilaiTeknikal = User::where('role', 'penilai_teknikal')->first();
        $penilaiKewangan = User::where('role', 'penilai_kewangan')->first();
        $admin = User::where('role', 'admin')->first();

        // 1. Owning vendor can download their own documents
        $response = $this->actingAs($vendorUser)->get(route('bids.documents.download', $techDoc->id));
        $response->assertOk();

        // 2. Other competing vendor CANNOT download
        $response = $this->actingAs($otherVendorUser)->get(route('bids.documents.download', $techDoc->id));
        $response->assertForbidden();

        // 3. Penilai teknikal can download technical document
        $response = $this->actingAs($penilaiTeknikal)->get(route('bids.documents.download', $techDoc->id));
        $response->assertOk();

        // 4. Penilai teknikal is STRICTLY FORBIDDEN from downloading financial document
        $response = $this->actingAs($penilaiTeknikal)->get(route('bids.documents.download', $finDoc->id));
        $response->assertForbidden();

        // 5. Penilai kewangan can download financial document (after box opened)
        $response = $this->actingAs($penilaiKewangan)->get(route('bids.documents.download', $finDoc->id));
        $response->assertOk();

        // 6. Admin can download documents after box opened
        $response = $this->actingAs($admin)->get(route('bids.documents.download', $finDoc->id));
        $response->assertOk();
    }

    public function test_bid_document_cannot_be_downloaded_before_box_is_opened(): void
    {
        // An active tender that is published and box not opened
        $tender = Tender::where('status', 'published')->first();
        $this->assertFalse($tender->isBoxOpened());

        $vendor = Vendor::where('compliance_status', 'verified')->first();

        Storage::disk('private')->put('bids/sealed/tech.pdf', 'dummy sealed content');

        $bid = Bid::create([
            'tender_id' => $tender->id,
            'vendor_id' => $vendor->id,
            'bid_reference' => 'BID-SEALED-TEST',
            'bid_amount' => 50000,
            'completion_duration_months' => 6,
            'status' => 'sealed',
            'submitted_at' => now(),
        ]);

        $doc = BidDocument::create([
            'bid_id' => $bid->id,
            'type' => 'technical_proposal',
            'file_path' => 'bids/sealed/tech.pdf',
            'file_name' => 'tech.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'sha256_checksum' => hash('sha256', 'dummy sealed content'),
            'is_financial' => false,
            'disk' => 'private',
        ]);

        $penilaiTeknikal = User::where('role', 'penilai_teknikal')->first();
        $admin = User::where('role', 'admin')->first();

        // Both evaluator and admin cannot download before box is unlocked
        $response = $this->actingAs($penilaiTeknikal)->get(route('bids.documents.download', $doc->id));
        $response->assertForbidden();

        $response = $this->actingAs($admin)->get(route('bids.documents.download', $doc->id));
        $response->assertForbidden();
    }

    public function test_tender_document_download_rules(): void
    {
        $tender = Tender::where('status', 'published')->where('document_price', '>', 0)->first();

        Storage::disk('private')->put('tenders/test/spec.pdf', 'tender spec content');
        Storage::disk('private')->put('tenders/test/public.pdf', 'public notice content');

        $restrictedDoc = TenderDocument::create([
            'tender_id' => $tender->id,
            'title' => 'Jadual Spesifikasi Lengkap',
            'file_path' => 'tenders/test/spec.pdf',
            'file_name' => 'spec.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
            'is_public' => false,
            'disk' => 'private',
        ]);

        $publicDoc = TenderDocument::create([
            'tender_id' => $tender->id,
            'title' => 'Notis Ringkas Awam',
            'file_path' => 'tenders/test/public.pdf',
            'file_name' => 'public.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'is_public' => true,
            'disk' => 'private',
        ]);

        // Unauthenticated user can download public doc
        $response = $this->get(route('documents.download', $publicDoc->id));
        $response->assertOk();

        // Unauthenticated user cannot download restricted doc
        $response = $this->get(route('documents.download', $restrictedDoc->id));
        $response->assertRedirect('/vendor/login');

        // Vendor who has NOT purchased cannot download restricted doc
        $unpaidVendorUser = Vendor::whereDoesntHave('purchases', fn ($q) => $q->where('tender_id', $tender->id)->where('status', 'paid'))->first()->user;
        $response = $this->actingAs($unpaidVendorUser)->get(route('documents.download', $restrictedDoc->id));
        $response->assertForbidden();
    }

    public function test_vendor_bank_and_qualification_fields(): void
    {
        $vendor = Vendor::first();

        $vendor->update([
            'bank_name' => 'Maybank Berhad',
            'bank_account_number' => '564212345678',
            'bank_account_holder' => $vendor->company_name,
            'mof_codes' => '210101, 210102',
            'cidb_grade' => 'G7',
        ]);

        $vendor->refresh();

        $this->assertEquals('Maybank Berhad', $vendor->bank_name);
        $this->assertEquals('564212345678', $vendor->bank_account_number);
        $this->assertEquals('210101, 210102', $vendor->mof_codes);
        $this->assertEquals('G7', $vendor->cidb_grade);
    }

    public function test_admin_can_verify_and_reject_vendor_compliance(): void
    {
        $admin = User::where('role', 'admin')->first();
        $vendor = Vendor::where('compliance_status', 'pending')->first();

        if (! $vendor) {
            $vendor = Vendor::first();
            $vendor->update(['compliance_status' => 'pending']);
        }

        // Verify vendor
        $vendor->update([
            'compliance_status' => 'verified',
            'compliance_verified_at' => now(),
            'verified_by_user_id' => $admin->id,
        ]);

        AuditTrail::log(
            eventType: 'VENDOR_COMPLIANCE_VERIFIED',
            description: "Pematuhan pembekal {$vendor->company_name} diluluskan",
            auditable: $vendor,
            userId: $admin->id
        );

        $vendor->refresh();
        $this->assertTrue($vendor->isVerified());
        $this->assertEquals($admin->id, $vendor->verified_by_user_id);

        // Reject vendor with notes
        $vendor->update([
            'compliance_status' => 'rejected',
            'compliance_notes' => 'Sijil SSM tamat tempoh.',
            'verified_by_user_id' => $admin->id,
        ]);

        AuditTrail::log(
            eventType: 'VENDOR_COMPLIANCE_REJECTED',
            description: "Pematuhan pembekal {$vendor->company_name} ditolak",
            auditable: $vendor,
            newValues: ['compliance_notes' => 'Sijil SSM tamat tempoh.'],
            userId: $admin->id
        );

        $vendor->refresh();
        $this->assertEquals('rejected', $vendor->compliance_status);
        $this->assertEquals('Sijil SSM tamat tempoh.', $vendor->compliance_notes);
    }
}
