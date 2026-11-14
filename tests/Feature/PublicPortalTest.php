<?php

namespace Tests\Feature;

use App\Models\Tender;
use App\Models\TenderPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Senarai Iklan Tender Semasa');
        $response->assertSee('Semua Kategori');
        $response->assertSee('Bekalan');
        $response->assertSee('Perkhidmatan');
        $response->assertSee('Kerja / Pembinaan');
        $response->assertSee('Perkhidmatan Perunding');
    }

    public function test_category_filtering_works(): void
    {
        $supplyResponse = $this->get('/?category=supply');
        $supplyResponse->assertStatus(200);

        $serviceResponse = $this->get('/?category=service');
        $serviceResponse->assertStatus(200);

        $workResponse = $this->get('/?category=work');
        $workResponse->assertStatus(200);

        $consultancyResponse = $this->get('/?category=consultancy');
        $consultancyResponse->assertStatus(200);
    }

    public function test_search_functionality(): void
    {
        $response = $this->get('/?q=Pelayan');
        $response->assertStatus(200);
    }

    public function test_public_tender_show_page(): void
    {
        $tender = Tender::first();
        $this->assertNotNull($tender);

        $response = $this->get(route('tenders.public-show', $tender->slug));
        $response->assertStatus(200);
        $response->assertSee($tender->reference_no);
        $response->assertSee($tender->title);
    }

    public function test_public_notice_pdf_download(): void
    {
        $tender = Tender::first();
        $this->assertNotNull($tender);

        $response = $this->get(route('tenders.notice-pdf', $tender->id));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_document_verification_page(): void
    {
        $purchase = TenderPurchase::where('status', 'paid')->first();
        if ($purchase) {
            $response = $this->get(route('verify.document', ['type' => 'receipt', 'ref' => $purchase->purchase_reference]));
            $response->assertStatus(200);
            $response->assertSee('DOKUMEN SAH & TULEN');
            $response->assertSee($purchase->purchase_reference);
        }

        $tender = Tender::where('status', 'awarded')->first();
        if ($tender) {
            $response = $this->get(route('verify.document', ['type' => 'sst', 'ref' => $tender->reference_no]));
            $response->assertStatus(200);
            $response->assertSee('DOKUMEN SAH & TULEN');
            $response->assertSee($tender->reference_no);
        }
    }

    public function test_responsive_organization_branding_displays_lpku_on_mobile_and_full_name_on_desktop(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Mobile view displays acronym with md:hidden
        $response->assertSee('md:hidden', false);
        $response->assertSee('LPKU');

        // Desktop view displays full name with hidden md:inline
        $response->assertSee('hidden md:inline', false);
        $response->assertSee('Lembaga Perolehan &amp; Kontrak Universal', false);
    }
}
