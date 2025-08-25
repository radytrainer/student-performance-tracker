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
        Student::create(['user_id' => $studentUser->id, 'student_code' => 'STU001']);
        Sanctum::actingAs($admin);

        // Create
        $create = $this->postJson('/api/admin/notes', [
            'student_id' => $studentUser->id,
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
