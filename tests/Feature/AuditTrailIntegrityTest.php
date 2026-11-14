<?php

namespace Tests\Feature;

use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_audit_trail_tamper_evident_hash_chain(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Log 3 sequential events
        $log1 = AuditTrail::log('ACTION_ONE', 'Peristiwa 1 direkodkan', userId: $admin->id);
        $log2 = AuditTrail::log('ACTION_TWO', 'Peristiwa 2 direkodkan', userId: $admin->id);
        $log3 = AuditTrail::log('ACTION_THREE', 'Peristiwa 3 direkodkan', userId: $admin->id);

        $this->assertNotEmpty($log1->hash);
        $this->assertEquals(64, strlen($log1->hash));

        // log2 prev_hash MUST strictly equal log1 hash
        $this->assertEquals($log1->hash, $log2->prev_hash);

        // log3 prev_hash MUST strictly equal log2 hash
        $this->assertEquals($log2->hash, $log3->prev_hash);

        // Verify entire audit chain integrity
        $logs = AuditTrail::orderBy('id', 'asc')->get();
        $this->assertGreaterThanOrEqual(3, $logs->count());

        $prev = null;
        foreach ($logs as $log) {
            if ($prev !== null) {
                $this->assertEquals(
                    $prev->hash,
                    $log->prev_hash,
                    "Integriti rantai hash audit terputus pada Rekod ID {$log->id}"
                );
            }
            $prev = $log;
        }
    }
}
