<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Laravel\Sanctum\Sanctum;

class SystemSettingsMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_triggers_and_notifies_admin(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/settings/backup')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Using sync queue by default, notification should be created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'System backup completed',
        ]);
    }

    public function test_maintenance_triggers_and_notifies_admin(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/settings/maintenance')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Maintenance completed',
        ]);
    }
}
