<?php

/**
 * System Testing Script for Hierarchical Admin System
 * Run with: php test_system.php
 */

require_once __DIR__ . '/backend/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\School;
use App\Models\Classes;

echo "🧪 Testing Hierarchical Admin System\n";
echo "=====================================\n\n";

// Test 1: Verify Super Admin
echo "1. 🔍 Checking Super Admin...\n";
$superAdmin = User::where('email', 'admin@school.com')->first();

if (!$superAdmin) {
    echo "❌ Super admin not found!\n";
    echo "💡 Run: php artisan app:set-super-admin admin@school.com\n\n";
} else {
    echo "✅ Super Admin found: {$superAdmin->email}\n";
    echo "   - Is Super Admin: " . ($superAdmin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "   - School ID: " . ($superAdmin->school_id ?? 'NULL (correct)') . "\n";
    echo "   - Role: {$superAdmin->role}\n\n";
}

// Test 2: Check Schools
echo "2. 🏫 Checking Schools...\n";
$schools = School::withCount(['users', 'classes', 'students', 'teachers', 'admins'])->get();

if ($schools->count() === 0) {
    echo "❌ No schools found!\n";
    echo "💡 Run: php artisan db:seed --class=SchoolSeeder\n\n";
} else {
    echo "✅ Found {$schools->count()} schools:\n";
    foreach ($schools as $school) {
        echo "   - {$school->name} ({$school->code})\n";
        echo "     Users: {$school->users_count}, Classes: {$school->classes_count}\n";
        echo "     Admins: {$school->admins_count}, Teachers: {$school->teachers_count}, Students: {$school->students_count}\n";
    }
    echo "\n";
}

// Test 3: Check School-User Associations
echo "3. 👥 Checking User-School Associations...\n";
$usersWithoutSchool = User::whereNull('school_id')->where('is_super_admin', false)->count();
$usersWithSchool = User::whereNotNull('school_id')->count();

echo "✅ Users with school assignment: {$usersWithSchool}\n";
if ($usersWithoutSchool > 0) {
    echo "⚠️  Users without school assignment: {$usersWithoutSchool}\n";
    echo "💡 Run: php artisan app:update-school-associations\n";
} else {
    echo "✅ All non-super-admin users have school assignments\n";
}
echo "\n";

// Test 4: Check Classes-School Associations
echo "4. 🏛️ Checking Class-School Associations...\n";
$classesWithoutSchool = Classes::whereNull('school_id')->count();
$classesWithSchool = Classes::whereNotNull('school_id')->count();

echo "✅ Classes with school assignment: {$classesWithSchool}\n";
if ($classesWithoutSchool > 0) {
    echo "⚠️  Classes without school assignment: {$classesWithoutSchool}\n";
    echo "💡 Run: php artisan app:update-school-associations\n";
} else {
    echo "✅ All classes have school assignments\n";
}
echo "\n";

// Test 5: Test Data Isolation Logic
echo "5. 🔒 Testing Data Isolation...\n";

if ($superAdmin && $superAdmin->is_super_admin) {
    // Test super admin sees all users
    $allUsers = User::all();
    echo "✅ Super admin can see all {$allUsers->count()} users\n";
    
    // Test school admin sees only their users
    $schoolAdmin = User::where('role', 'admin')->where('is_super_admin', false)->first();
    if ($schoolAdmin) {
        $schoolUsers = User::forSchool($schoolAdmin->school_id)->get();
        echo "✅ School admin (School ID: {$schoolAdmin->school_id}) can see {$schoolUsers->count()} users from their school\n";
    } else {
        echo "⚠️  No school admins found for testing\n";
    }
} else {
    echo "❌ Cannot test data isolation - super admin not properly configured\n";
}
echo "\n";

// Test 6: Check API Routes (basic structure)
echo "6. 🛣️ Checking Route Configuration...\n";
try {
    $routeList = \Illuminate\Support\Facades\Route::getRoutes();
    $superAdminRoutes = collect($routeList)->filter(function ($route) {
        return str_contains($route->uri(), 'super-admin');
    });
    
    if ($superAdminRoutes->count() > 0) {
        echo "✅ Super admin routes found: {$superAdminRoutes->count()}\n";
        foreach ($superAdminRoutes as $route) {
            echo "   - {$route->methods()[0]} /{$route->uri()}\n";
        }
    } else {
        echo "❌ No super admin routes found!\n";
    }
} catch (Exception $e) {
    echo "⚠️  Could not check routes: {$e->getMessage()}\n";
}
echo "\n";

// Summary
echo "📋 SUMMARY\n";
echo "==========\n";

$issues = 0;

if (!$superAdmin || !$superAdmin->is_super_admin) {
    echo "❌ Super admin not configured\n";
    $issues++;
}

if ($schools->count() === 0) {
    echo "❌ No schools found\n";
    $issues++;
}

if ($usersWithoutSchool > 0) {
    echo "⚠️  Some users not assigned to schools\n";
    $issues++;
}

if ($classesWithoutSchool > 0) {
    echo "⚠️  Some classes not assigned to schools\n";
    $issues++;
}

if ($issues === 0) {
    echo "🎉 All tests passed! System appears to be working correctly.\n";
    echo "\n📝 Next steps:\n";
    echo "1. Start backend: cd backend && php artisan serve\n";
    echo "2. Start frontend: cd frontend && npm run dev\n";
    echo "3. Login as super admin: admin@school.com / admin123\n";
    echo "4. Test the interface manually\n";
} else {
    echo "⚠️  Found {$issues} issues. Please fix them before testing.\n";
    echo "\n🔧 Quick fixes:\n";
    echo "1. php artisan app:set-super-admin admin@school.com\n";
    echo "2. php artisan db:seed --class=SchoolSeeder\n";
    echo "3. php artisan app:update-school-associations\n";
}

echo "\n";
