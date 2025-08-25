<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminImportUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_csv_file(): void
    {
        $this->artisan('migrate');
        Storage::fake('public');
        $admin = User::factory()->create([ 'role' => 'admin' ]);
        Sanctum::actingAs($admin);

        $file = UploadedFile::fake()->create('students.csv', 1, 'text/plain');
        $resp = $this->postJson('/api/admin/import/upload-file', [ 'file' => $file, 'label' => 'test' ]);
        $resp->assertStatus(200);
        $resp->assertJsonPath('success', true);
    }
}
