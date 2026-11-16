<?php

namespace Tests\Feature;

use App\Filament\Auth\Pages\CustomLogin;
use App\Filament\Widgets\ProcurementOverview;
use App\Filament\Widgets\ProcurementSpendChart;
use App\Filament\Widgets\RecentTendersTable;
use App\Filament\Widgets\TendersByCategoryChart;
use App\Filament\Widgets\TenderStatusPipelineChart;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_guest_is_redirected_to_vendor_login(): void
    {
        $response = $this->get('/vendor');

        $response->assertRedirect('/vendor/login');
    }

    public function test_guest_is_redirected_to_penilai_login(): void
    {
        $response = $this->get('/penilai');

        $response->assertRedirect('/penilai/login');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertSuccessful();
    }

    public function test_urus_setia_can_access_admin_dashboard(): void
    {
        $urusSetia = User::factory()->create([
            'role' => 'urus_setia',
            'is_active' => true,
        ]);

        $response = $this->actingAs($urusSetia)->get('/admin');

        $response->assertSuccessful();
    }

    public function test_vendor_cannot_access_admin_dashboard(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($vendor)->get('/admin');

        $response->assertForbidden();
    }

    public function test_inactive_admin_cannot_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_login_page_renders_single_logo_and_login_guide(): void
    {
        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertSee('Pentadbir Perolehan', false);
        $response->assertSee('Panduan Log Masuk', false);
        $response->assertSee('admin@organisasi.my', false);

        // Verify only 1 shield logo SVG exists in the content
        $content = $response->getContent();
        preg_match_all('/<svg style=[\'"]width: 20px.*?<\/svg>/is', $content, $svgs);
        $this->assertCount(1, $svgs[0]);
    }

    public function test_admin_can_authenticate_via_livewire_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.test@organisasi.my',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(Login::class)
            ->set('data.email', 'admin.test@organisasi.my')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_penilai_login_page_renders_single_logo(): void
    {
        $response = $this->get('/penilai/login');

        $response->assertSuccessful();
        $response->assertSee('Jawatankuasa Penilaian Perolehan', false);

        $content = $response->getContent();
        preg_match_all('/<svg style=[\'"]width: 20px.*?<\/svg>/is', $content, $svgs);
        $this->assertCount(1, $svgs[0]);
    }

    public function test_vendor_login_page_renders_single_logo(): void
    {
        $response = $this->get('/vendor/login');

        $response->assertSuccessful();
        $response->assertSee('Portal Pembekal', false);

        $content = $response->getContent();
        preg_match_all('/<svg style=[\'"]width: 20px.*?<\/svg>/is', $content, $svgs);
        $this->assertCount(1, $svgs[0]);
    }

    public function test_vendor_credentials_on_admin_login_smart_redirects_to_vendor_panel(): void
    {
        $vendor = User::factory()->create([
            'email' => 'vendor.smart@bina-maju.com.my',
            'password' => bcrypt('password123'),
            'role' => 'vendor',
            'is_active' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CustomLogin::class)
            ->set('data.email', 'vendor.smart@bina-maju.com.my')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(url('/vendor'));

        $this->assertAuthenticatedAs($vendor);
    }

    public function test_penilai_credentials_on_admin_login_smart_redirects_to_penilai_panel(): void
    {
        $penilai = User::factory()->create([
            'email' => 'penilai.smart@organisasi.my',
            'password' => bcrypt('password123'),
            'role' => 'penilai_teknikal',
            'is_active' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CustomLogin::class)
            ->set('data.email', 'penilai.smart@organisasi.my')
            ->set('data.password', 'password123')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(url('/penilai'));

        $this->assertAuthenticatedAs($penilai);
    }

    public function test_admin_dashboard_renders_analytics_widgets(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertSuccessful();
        $response->assertSee('App\Filament\Widgets\ProcurementOverview', false);
        $response->assertSee('App\Filament\Widgets\ProcurementSpendChart', false);
        $response->assertSee('App\Filament\Widgets\TendersByCategoryChart', false);
        $response->assertSee('App\Filament\Widgets\TenderStatusPipelineChart', false);
        $response->assertSee('App\Filament\Widgets\RecentTendersTable', false);

        // Also verify Livewire test mounting for each analytics widget
        Livewire::test(ProcurementOverview::class)->assertSuccessful();
        Livewire::test(ProcurementSpendChart::class)->assertSuccessful();
        Livewire::test(TendersByCategoryChart::class)->assertSuccessful();
        Livewire::test(TenderStatusPipelineChart::class)->assertSuccessful();
        Livewire::test(RecentTendersTable::class)->assertSuccessful();
    }

    public function test_login_page_renders_quick_fill_and_portal_switcher(): void
    {
        $response = $this->get('/admin/login');

        $response->assertSuccessful();
        $response->assertSee('Smart Routing Aktif', false);
        $response->assertSee('fillDemoLogin', false);
        $response->assertSee('/vendor/login', false);
        $response->assertSee('/penilai/login', false);
    }

    public function test_vendor_dashboard_renders_vendor_widgets(): void
    {
        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($vendorUser)->get('/vendor');

        $response->assertSuccessful();
        $response->assertSee('App\Filament\Vendor\Widgets\VendorOverview', false);
        $response->assertSee('App\Filament\Vendor\Widgets\VendorBidsChart', false);
    }

    public function test_penilai_dashboard_renders_penilai_widgets(): void
    {
        $penilai = User::factory()->create([
            'role' => 'penilai_teknikal',
            'is_active' => true,
        ]);

        $response = $this->actingAs($penilai)->get('/penilai');

        $response->assertSuccessful();
        $response->assertSee('App\Filament\Penilai\Widgets\PenilaiOverview', false);
        $response->assertSee('App\Filament\Penilai\Widgets\PenilaiWorkloadChart', false);
    }
}
