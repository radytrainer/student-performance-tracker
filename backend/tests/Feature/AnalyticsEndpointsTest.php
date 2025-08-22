<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AnalyticsEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_correlation_and_heatmap(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create([ 'role' => 'admin' ]);
        Sanctum::actingAs($admin);

        $resp1 = $this->getJson('/api/analytics/correlation?class_id=1');
        $resp1->assertStatus(200);

        $resp2 = $this->getJson('/api/analytics/heatmap?class_id=1');
        $resp2->assertStatus(200);
    }
}
