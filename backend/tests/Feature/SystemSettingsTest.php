<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    private array $defaults = [
        'schoolName' => 'Riverside High School',
        'academicYear' => '2024-2025',
        'timeZone' => 'America/New_York',
        'gradingScale' => 'letter',
        'passingGrade' => 60,
        'requiredAttendance' => 75,
        'allowLateSubmissions' => true,
        'emailNotifications' => true,
        'smsNotifications' => false,
        'parentNotifications' => true,
        'sessionTimeout' => 120,
        'requireTwoFactor' => false,
        'enforcePasswordPolicy' => true,
        'backupFrequency' => 'daily',
    ];

    public function test_admin_gets_default_settings_on_fresh_db(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $resp = $this->getJson('/api/admin/settings');
        $resp->assertStatus(200)
             ->assertJson(['success' => true])
             ->assertJsonPath('data.schoolName', $this->defaults['schoolName'])
             ->assertJsonPath('data.gradingScale', $this->defaults['gradingScale'])
             ->assertJsonPath('data.passingGrade', $this->defaults['passingGrade']);
    }

    public function test_admin_can_update_and_persist_settings(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $update = [
            'schoolName' => 'Central City High',
            'passingGrade' => 65,
            'allowLateSubmissions' => false,
        ];

        $this->putJson('/api/admin/settings', $update)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->getJson('/api/admin/settings')
            ->assertStatus(200)
            ->assertJsonPath('data.schoolName', 'Central City High')
            ->assertJsonPath('data.passingGrade', 65)
            ->assertJsonPath('data.allowLateSubmissions', false);
    }

    public function test_reset_restores_defaults(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Change a few values
        $this->putJson('/api/admin/settings', [
            'schoolName' => 'Tmp School',
            'passingGrade' => 70,
        ])->assertStatus(200);

        // Reset
        $this->postJson('/api/admin/settings/reset')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify defaults
        $this->getJson('/api/admin/settings')
            ->assertStatus(200)
            ->assertJsonPath('data.schoolName', $this->defaults['schoolName'])
            ->assertJsonPath('data.passingGrade', $this->defaults['passingGrade']);
    }

    public function test_non_admin_forbidden(): void
    {
        $this->artisan('migrate');
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher);

        $this->getJson('/api/admin/settings')->assertStatus(403);
        $this->putJson('/api/admin/settings', ['schoolName' => 'X'])->assertStatus(403);
        $this->postJson('/api/admin/settings/reset')->assertStatus(403);
    }
}
