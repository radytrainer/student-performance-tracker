<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use Laravel\Sanctum\Sanctum;

class StudentNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_list_update_delete_notes(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create(['role' => 'admin']);
        $studentUser = User::factory()->create(['role' => 'student']);
        \App\Models\Student::updateOrCreate(
            ['user_id' => $studentUser->id],
            ['student_code' => 'STU001', 'enrollment_date' => now()]
        );
        Sanctum::actingAs($admin);

        // Admin must supply a valid teacher_id (FK to teachers)
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        \App\Models\Teacher::updateOrCreate(['user_id' => $teacherUser->id], ['teacher_code' => 'T999', 'hire_date' => now()]);

        // Create
        $create = $this->postJson('/api/admin/notes', [
            'student_id' => $studentUser->id,
            'teacher_id' => $teacherUser->id,
            'title' => 'Behavior',
            'note' => 'Needs improvement',
            'is_private' => true
        ])->assertStatus(201);
        $id = $create->json('data.id');

        // List
        $this->getJson('/api/admin/notes')->assertStatus(200);

        // Update
        $this->putJson("/api/admin/notes/{$id}", [ 'title' => 'Updated' ])->assertStatus(200);

        // Delete
        $this->deleteJson("/api/admin/notes/{$id}")->assertStatus(200);
    }
}
