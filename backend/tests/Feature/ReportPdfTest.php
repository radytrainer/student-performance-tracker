<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ReportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_request_pdf_report(): void
    {
        $this->artisan('migrate');
        // Create a student user
        $user = User::factory()->create([ 'role' => 'student' ]);
        Sanctum::actingAs($user);

        $resp = $this->postJson('/api/student/reports/generate', [
            'type' => 'academic_summary',
            'period' => 'all_time',
            'format' => 'pdf'
        ]);

        $resp->assertStatus(200);
        // Response may be a download; accept 200
    }
}
