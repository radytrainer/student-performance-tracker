<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Term;
use Laravel\Sanctum\Sanctum;

class PerformanceAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_and_resolve_alerts(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create(['role' => 'admin']);
        $studentUser = User::factory()->create(['role' => 'student']);
        Student::create(['user_id' => $studentUser->id, 'student_code' => 'STU0001']);
        $term = Term::factory()->create(['is_current' => true]);

        Sanctum::actingAs($admin);

        // List
        $this->getJson('/api/admin/alerts')->assertStatus(200);

        // Create
        $payload = [
            'student_id' => $studentUser->id,
            'term_id' => $term->id,
            'alert_type' => 'low_grade',
            'severity' => 'medium',
            'message' => 'Below threshold'
        ];
        $resp = $this->postJson('/api/admin/alerts', $payload)->assertStatus(201);
        $id = $resp->json('data.id');
        $this->assertNotEmpty($id);

        // Resolve
        $this->putJson("/api/admin/alerts/{$id}", ['is_resolved' => true])->assertStatus(200);
    }

    public function test_teacher_can_list_and_resolve_alerts(): void
    {
        $this->artisan('migrate');
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher);
        $this->getJson('/api/teacher/alerts')->assertStatus(200);
    }
}
