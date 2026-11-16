<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ForbiddenErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_accessing_admin_sees_custom_bootstrap_403_page(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($vendor)->get('/admin');

        $response->assertForbidden();
        $response->assertDontSee('Ralat 403 Forbidden');
        $response->assertSee('403 — Akses Ditolak');
        $response->assertSee('Pembekal / Kontraktor Berdaftar');
        $response->assertSee('Dashboard Pembekal');
        $response->assertSee(url('/vendor'));
        $response->assertSee('content="3;url='.url('/vendor').'"', false);
        $response->assertSee('Peralihan Automatik (3 Saat)');
        $response->assertSee('countdown-seconds');
        $response->assertSee('bootstrap.min.css', false);
        $response->assertSee('bootstrap-icons', false);
        $response->assertSee('Log Keluar / Tukar Akaun');
    }

    public function test_penilai_accessing_admin_sees_custom_bootstrap_403_page(): void
    {
        $penilai = User::factory()->create([
            'role' => 'penilai_teknikal',
            'is_active' => true,
        ]);

        $response = $this->actingAs($penilai)->get('/admin');

        $response->assertForbidden();
        $response->assertSee('403 — Akses Ditolak');
        $response->assertSee('Jawatankuasa Penilai Teknikal');
        $response->assertSee('Dashboard Penilai');
        $response->assertSee(url('/penilai'));
        $response->assertSee('bootstrap.min.css', false);
    }

    public function test_admin_accessing_vendor_sees_custom_bootstrap_403_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/vendor');

        $response->assertForbidden();
        $response->assertSee('403 — Akses Ditolak');
        $response->assertSee('Pentadbir Sistem (Admin)');
        $response->assertSee('Dashboard Pentadbir');
        $response->assertSee(url('/admin'));
        $response->assertSee('bootstrap.min.css', false);
    }

    public function test_guest_403_renders_login_link_and_bootstrap_assets(): void
    {
        $view = view('errors.403')->render();

        $this->assertStringContainsString('403 — Akses Ditolak', $view);
        $this->assertStringNotContainsString('Ralat 403 Forbidden', $view);
        $this->assertStringContainsString('Pelawat (Belum Log Masuk)', $view);
        $this->assertStringContainsString('Laman Log Masuk', $view);
        $this->assertStringContainsString('content="3;url=', $view);
        $this->assertStringContainsString('Peralihan Automatik (3 Saat)', $view);
        $this->assertStringContainsString('countdown-seconds', $view);
        $this->assertStringContainsString('bootstrap.min.css', $view);
        $this->assertStringContainsString('bootstrap-icons', $view);
    }

    public function test_custom_exception_message_is_displayed(): void
    {
        $customMessage = 'Akses dokumen bidaan bertutup disekat oleh kawalan kriptografi sehingga pembukaan peti.';
        $exception = new HttpException(403, $customMessage);

        $view = view('errors.403', ['exception' => $exception])->render();

        $this->assertStringContainsString('403 — Akses Ditolak', $view);
        $this->assertStringContainsString('Maklumat Penolakan:', $view);
        $this->assertStringContainsString($customMessage, $view);
    }

    public function test_unified_login_route_redirects_guest_to_vendor_login(): void
    {
        $response = $this->get('/login');

        $response->assertRedirect('/vendor/login');
    }

    public function test_unified_login_route_redirects_vendor_to_vendor_dashboard(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($vendor)->get('/login');

        $response->assertRedirect('/vendor');
    }

    public function test_unified_login_route_redirects_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/login');

        $response->assertRedirect('/admin');
    }

    public function test_unified_login_route_redirects_penilai_to_penilai_dashboard(): void
    {
        $penilai = User::factory()->create([
            'role' => 'penilai_teknikal',
            'is_active' => true,
        ]);

        $response = $this->actingAs($penilai)->get('/login');

        $response->assertRedirect('/penilai');
    }

    public function test_authenticated_vendor_visiting_admin_login_redirects_to_vendor_dashboard(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $response = $this->actingAs($vendor)->get('/admin/login');

        $response->assertRedirect('/vendor');
    }
}
