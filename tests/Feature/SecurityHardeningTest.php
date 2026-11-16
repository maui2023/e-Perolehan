<?php

namespace Tests\Feature;

use App\Models\OrganizationSetting;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_affanpay_webhook_rejects_missing_signature_when_secret_is_configured(): void
    {
        config(['affanpay.webhook_secret' => 'super_secret_webhook_token_123']);

        $payload = [
            'status' => 'paid',
            'external_ref' => 'TEST-PURCHASE-01',
            'payment_ref' => 'PAY-123',
            'amount' => 50.00,
        ];

        // 1. Without signature header: MUST be rejected with 401
        $responseWithoutSig = $this->postJson(route('affanpay.webhook'), $payload);
        $responseWithoutSig->assertStatus(401);
        $responseWithoutSig->assertJsonPath('result.status', 'invalid_signature');

        // 2. With wrong signature: MUST be rejected with 401
        $responseWrongSig = $this->withHeader('X-AffanPay-Signature', 'wrong_token')
            ->postJson(route('affanpay.webhook'), $payload);
        $responseWrongSig->assertStatus(401);
        $responseWrongSig->assertJsonPath('result.status', 'invalid_signature');
    }

    public function test_cannot_download_opening_slip_before_box_is_opened(): void
    {
        $admin = User::where('role', 'admin')->first();
        $tender = Tender::where('status', 'published')->first();
        $this->assertFalse($tender->isBoxOpened());

        $response = $this->actingAs($admin)->get(route('tenders.opening-slip', $tender->id));
        $response->assertStatus(403);
    }

    public function test_vendor_cannot_affirm_as_box_witness(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();
        $tender = Tender::where('status', 'closed')->first() ?? Tender::first();

        $response = $this->actingAs($vendorUser)->post(route('witness.affirm', $tender->id), [
            'witness_remarks' => 'Percubaan bypass',
        ]);

        $response->assertStatus(403);
    }

    public function test_public_search_escapes_wildcard_characters(): void
    {
        $response = $this->get(route('portal.home', ['q' => '%_\\']));
        $response->assertSuccessful();
    }

    public function test_organization_setting_current_handles_cached_attributes_and_recovers_from_incomplete_class(): void
    {
        OrganizationSetting::clearCurrentCache();

        // 1. Initial retrieval creates or fetches from DB and caches attributes
        $setting = OrganizationSetting::current();
        $this->assertInstanceOf(OrganizationSetting::class, $setting);
        $this->assertNotEmpty($setting->org_name);
        $this->assertIsString($setting->org_acronym);

        // 2. Cache contains raw array attributes (safe from serializable_classes restrictions)
        $cachedValue = Cache::get('organization_setting_current');
        $this->assertIsArray($cachedValue);

        // 3. Clear in-process memoization and retrieve again from cache
        OrganizationSetting::clearCurrentCache();
        Cache::forever('organization_setting_current', $cachedValue);
        $fromCache = OrganizationSetting::current();
        $this->assertInstanceOf(OrganizationSetting::class, $fromCache);
        $this->assertEquals($setting->org_name, $fromCache->org_name);

        // 4. Simulate __PHP_Incomplete_Class corruption in cache (e.g. from previous object serialization)
        OrganizationSetting::clearCurrentCache();
        $incomplete = unserialize('O:30:"App\Models\OrganizationSetting":0:{}', ['allowed_classes' => false]);
        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $incomplete);
        Cache::forever('organization_setting_current', $incomplete);

        // Must self-heal and return a valid OrganizationSetting instance without throwing TypeError
        $recovered = OrganizationSetting::current();
        $this->assertInstanceOf(OrganizationSetting::class, $recovered);
        $this->assertEquals($setting->org_name, $recovered->org_name);
    }
}
