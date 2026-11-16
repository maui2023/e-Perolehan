<?php

namespace Tests\Feature;

use App\Filament\Vendor\Pages\VendorProfile;
use App\Filament\Vendor\Resources\Tenders\Pages\ViewTender;
use App\Models\AuditTrail;
use App\Models\OrganizationSetting;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorMobileViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OrganizationSetting::create([
            'org_name' => 'Perbadanan Pengurusan Perolehan Bersepadu',
            'org_short_name' => 'P3B',
            'official_email' => 'perolehan@p3b.gov.my',
            'anti_bribery_policy' => 'Polisi Sifar Rasuah Seksyen 17A SPRM',
            'tender_code_prefix' => 'TDR',
            'is_active' => true,
        ]);
    }

    public function test_vendor_portal_renders_mobile_head_meta_tags(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/vendor');

        $response->assertStatus(200);
        $response->assertSee('viewport-fit=cover', false);
        $response->assertSee('mobile-web-app-capable', false);
        $response->assertSee('theme-color', false);
    }

    public function test_vendor_portal_renders_mobile_bottom_navigation_bar_with_four_tabs(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/vendor');

        $response->assertStatus(200);
        $response->assertSee('id="vendor-mobile-bottom-nav"', false);
        $response->assertSee('Utama', false);
        $response->assertSee('Tender', false);
        $response->assertSee('Bidaan', false);
        $response->assertSee('Profil', false);
        $response->assertSee(route('filament.vendor.pages.dashboard'), false);
        $response->assertSee(route('filament.vendor.resources.tenders.index'), false);
        $response->assertSee(route('filament.vendor.resources.bids.index'), false);
        $response->assertSee(route('filament.vendor.auth.profile'), false);
    }

    public function test_guest_cannot_see_vendor_mobile_bottom_nav_on_login_page(): void
    {
        $response = $this->get('/vendor/login');

        $response->assertStatus(200);
        $response->assertDontSee('id="vendor-mobile-bottom-nav"', false);
    }

    public function test_admin_panel_does_not_render_vendor_mobile_bottom_nav(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertDontSee('id="vendor-mobile-bottom-nav"', false);
    }

    public function test_vendor_can_access_profile_page(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/vendor/profile');

        $response->assertStatus(200);
        $response->assertSee('Profil Syarikat', false);
    }

    public function test_vendor_profile_updates_vendor_details_and_logs_audit(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $vendor = $user->vendor;

        // Perform Livewire profile update or direct model logic verified through VendorProfile handler
        $profilePage = new VendorProfile;

        // Verify reflection methods and updating contract
        $updateData = [
            'name' => 'Ahmad Pengurus',
            'email' => $user->email,
            'phone' => '019-8765432',
            'company_name' => 'Wawasan Murni Kejuruteraan Sdn Bhd',
            'registration_number' => 'SSM-2026-WMK',
            'tax_identification_number' => 'C99887766',
            'company_email' => 'info@wawasanmurni.com',
            'company_phone' => '03-88889999',
            'address' => 'No 15, Jalan Usahawan 2',
            'city' => 'Cyberjaya',
            'state' => 'Selangor',
            'postcode' => '63000',
            'mof_registration_number' => 'MOF-357-112233',
            'cidb_registration_number' => 'CIDB-G7-445566',
            'bumiputera_status' => true,
            'agree_anti_bribery' => true,
        ];

        // Invoke handleRecordUpdate via reflection
        $method = new \ReflectionMethod(VendorProfile::class, 'handleRecordUpdate');
        $method->setAccessible(true);
        $method->invoke($profilePage, $user, $updateData);

        $vendor->refresh();
        $this->assertEquals('Wawasan Murni Kejuruteraan Sdn Bhd', $vendor->company_name);
        $this->assertEquals('SSM-2026-WMK', $vendor->registration_number);
        $this->assertEquals('MOF-357-112233', $vendor->mof_registration_number);
        $this->assertEquals('CIDB-G7-445566', $vendor->cidb_registration_number);
        $this->assertTrue((bool) $vendor->bumiputera_status);
        $this->assertNotNull($vendor->anti_bribery_agreed_at);

        // Verify audit log
        $this->assertTrue(
            AuditTrail::where('event_type', 'vendor_profile_updated')
                ->where('user_id', $user->id)
                ->exists()
        );
    }

    public function test_vendor_tender_and_bid_pages_render_successfully(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $responseTenders = $this->actingAs($user)->get('/vendor/tenders');
        $responseTenders->assertStatus(200);
        $responseTenders->assertSee('id="vendor-mobile-bottom-nav"', false);

        $responseBids = $this->actingAs($user)->get('/vendor/bids');
        $responseBids->assertStatus(200);
        $responseBids->assertSee('id="vendor-mobile-bottom-nav"', false);
    }

    public function test_vendor_view_tender_page_renders_masuk_bidaan_action_at_bottom_and_removes_header_actions(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $tender = Tender::create([
            'reference_no' => 'TDR-TEST-0001',
            'title' => 'Tender Ujian Sistem Bidaan Percuma',
            'slug' => 'tender-ujian-sistem-bidaan-percuma',
            'category' => 'supply',
            'procurement_type' => 'open',
            'description' => 'Keterangan tender ujian',
            'scope_of_work' => 'Skop kerja tender ujian',
            'eligibility_criteria' => 'Syarat kelayakan tender ujian',
            'document_price' => 0,
            'published_at' => now()->subDay(),
            'closing_at' => now()->addDays(14),
            'status' => 'published',
        ]);

        $this->actingAs($user);

        $test = Livewire::test(ViewTender::class, ['record' => $tender->id]);
        $html = $test->html();

        // Ensure header actions are empty (no buttons at top)
        $this->assertFalse(str_contains($html, 'fi-header-actions'));

        // Ensure Masuk Bidaan button is present and '(Hantar Cadangan)' is removed
        $this->assertTrue(str_contains($html, 'Masuk Bidaan'));
        $this->assertFalse(str_contains($html, '(Hantar Cadangan)'));

        // Ensure button appears at the bottom of the page (after reference number and form fields)
        $posRef = strpos($html, 'Nombor Rujukan');
        $posBtn = strpos($html, 'Masuk Bidaan');
        $this->assertNotFalse($posRef);
        $this->assertNotFalse($posBtn);
        $this->assertGreaterThan($posRef, $posBtn);
    }

    public function test_vendor_view_tender_page_renders_purchase_action_at_bottom_when_document_has_fee(): void
    {
        $user = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $tender = Tender::create([
            'reference_no' => 'TDR-TEST-0002',
            'title' => 'Tender Ujian Sistem Bidaan Berbayar',
            'slug' => 'tender-ujian-sistem-bidaan-berbayar',
            'category' => 'service',
            'procurement_type' => 'open',
            'description' => 'Keterangan tender berbayar',
            'scope_of_work' => 'Skop kerja tender berbayar',
            'eligibility_criteria' => 'Syarat kelayakan tender berbayar',
            'document_price' => 50,
            'published_at' => now()->subDay(),
            'closing_at' => now()->addDays(14),
            'status' => 'published',
        ]);

        $this->actingAs($user);

        $test = Livewire::test(ViewTender::class, ['record' => $tender->id]);
        $html = $test->html();

        // Ensure header actions are empty (no buttons at top)
        $this->assertFalse(str_contains($html, 'fi-header-actions'));

        // Ensure Beli Dokumen button is present at the bottom
        $this->assertTrue(str_contains($html, 'Beli Dokumen Tender (AffanPay)'));

        $posRef = strpos($html, 'Nombor Rujukan');
        $posBtn = strpos($html, 'Beli Dokumen Tender (AffanPay)');
        $this->assertNotFalse($posRef);
        $this->assertNotFalse($posBtn);
        $this->assertGreaterThan($posRef, $posBtn);
    }
}
