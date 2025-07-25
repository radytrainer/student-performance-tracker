<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Foundation\Application;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing User Model...\n";

try {
    // Test database connection
    $users = User::take(5)->get();
    echo "Found " . $users->count() . " users in database\n";
    
    foreach ($users as $user) {
        echo "- {$user->first_name} {$user->last_name} ({$user->email}) - Role: {$user->role}\n";
    }
    
    echo "User model test successful!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
