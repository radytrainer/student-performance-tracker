<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AdminSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_sorting_params_return_ok(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create([ 'role' => 'admin' ]);
        Sanctum::actingAs($admin);

        $keys = ['name','email','student_code','created_at','gpa','attendance'];
        foreach ($keys as $k) {
            $resp = $this->getJson('/api/admin/students?sort_by='.$k.'&sort_dir=asc');
            $resp->assertStatus(200);
        }
    }

    public function test_subjects_sorting_params_return_ok(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create([ 'role' => 'admin' ]);
        Sanctum::actingAs($admin);

        $keys = ['subject_name','subject_code','department','credit_hours','created_at'];
        foreach ($keys as $k) {
            $resp = $this->getJson('/api/admin/subjects?sort_by='.$k.'&sort_dir=desc');
            $resp->assertStatus(200);
        }
    }

    public function test_classes_sorting_params_return_ok(): void
    {
        $this->artisan('migrate');
        $admin = User::factory()->create([ 'role' => 'admin' ]);
        Sanctum::actingAs($admin);

        $keys = ['class_name','academic_year','room_number','created_at'];
        foreach ($keys as $k) {
            $resp = $this->getJson('/api/admin/classes?sort_by='.$k.'&sort_dir=asc');
            $resp->assertStatus(200);
        }
    }
}
