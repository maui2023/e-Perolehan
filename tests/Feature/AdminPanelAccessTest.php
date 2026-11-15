<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
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
}
