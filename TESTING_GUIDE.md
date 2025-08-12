# Hierarchical Admin System - Testing Guide

## 🔍 Quick Verification Steps

### 1. **Database Verification**
```bash
# Check if migrations ran successfully
cd backend
php artisan migrate:status

# Verify super admin was set
php artisan tinker --execute="
\$user = App\Models\User::where('email', 'admin@school.com')->first();
echo 'Super Admin: ' . \$user->email . PHP_EOL;
echo 'Is Super Admin: ' . (\$user->is_super_admin ? 'YES' : 'NO') . PHP_EOL;
echo 'School ID: ' . (\$user->school_id ?? 'NULL (correct for super admin)') . PHP_EOL;
"

# Check schools and their users
php artisan tinker --execute="
echo 'Schools:' . PHP_EOL;
foreach(App\Models\School::with('users')->get() as \$school) {
    echo '- ' . \$school->name . ' (' . \$school->code . ') - Users: ' . \$school->users->count() . PHP_EOL;
}
"
```

### 2. **Backend API Testing**

#### Start the backend server:
```bash
cd backend
php artisan serve
```

#### Test Super Admin Login:
```bash
# Login as super admin
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@school.com",
    "password": "admin123"
  }'
```
**Expected Response:** `{"token": "...", "user": {"is_super_admin": true, "school_id": null}}`

#### Test Super Admin Schools Access:
```bash
# Replace YOUR_TOKEN with the token from login response
curl -X GET http://localhost:8000/api/super-admin/schools \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected Response:** List of all schools

#### Test Super Admin Stats:
```bash
curl -X GET http://localhost:8000/api/super-admin/stats \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected Response:** System-wide statistics

### 3. **Frontend Testing**

#### Start the frontend:
```bash
cd frontend  # or wherever your frontend is
npm run dev
```

#### Test Login Flow:
1. **Navigate to:** `http://localhost:3000/login`
2. **Login with:** `admin@school.com` / `admin123`
3. **Expected:** Redirect to `/super-admin/dashboard`
4. **Verify:** You see "Super Admin Dashboard" with school management interface

#### Test Super Admin Interface:
1. **Dashboard should show:**
   - Total Schools count
   - Total Users count
   - Sub Admins count
   - Students count
2. **You should be able to:**
   - View all schools
   - Add new schools
   - Create sub-admins for schools

### 4. **Data Isolation Testing**

#### Create a Sub-Admin via API:
```bash
# First, create a new school
curl -X POST http://localhost:8000/api/super-admin/schools \
  -H "Authorization: Bearer YOUR_SUPER_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test School",
    "code": "TEST001",
    "email": "test@testschool.edu",
    "status": "active"
  }'

# Then create a sub-admin for that school (replace SCHOOL_ID)
curl -X POST http://localhost:8000/api/super-admin/schools/SCHOOL_ID/sub-admins \
  -H "Authorization: Bearer YOUR_SUPER_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "subadmin1",
    "email": "subadmin1@testschool.edu", 
    "password": "password123",
    "first_name": "Sub",
    "last_name": "Admin"
  }'
```

#### Test Sub-Admin Login:
```bash
# Login as sub-admin
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "subadmin1@testschool.edu",
    "password": "password123"
  }'
```

#### Test Data Isolation:
```bash
# Sub-admin tries to access super-admin routes (should fail)
curl -X GET http://localhost:8000/api/super-admin/schools \
  -H "Authorization: Bearer SUB_ADMIN_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected Response:** `403 Forbidden`

```bash
# Sub-admin accesses users (should only see their school's users)
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer SUB_ADMIN_TOKEN" \
  -H "Content-Type: application/json"
```
**Expected Response:** Only users from their school

## 🚨 Common Issues & Solutions

### Issue 1: "Super admin not found"
**Solution:**
```bash
cd backend
php artisan app:set-super-admin admin@school.com
```

### Issue 2: "SchoolIsolation trait errors"
**Solution:** Make sure all controllers using the trait import it properly:
```php
use App\Traits\SchoolIsolation;
```

### Issue 3: "Frontend shows 404 for super-admin routes"
**Solution:** Check that the router configuration includes super admin routes and the component exists.

### Issue 4: "Permission denied for super admin"
**Solution:** Verify the `hasPermission` function in `useAuth.js` handles function-based permissions.

## 📊 Expected Behavior Summary

| User Type | Can Access | Cannot Access |
|-----------|------------|---------------|
| **Super Admin** | All schools, All users, Create schools, Create sub-admins | N/A (full access) |
| **Sub-Admin** | Own school users only, Own school classes only | Other schools' data, Super admin functions |
| **Regular Users** | Own data only | Admin functions, Other users' data |

## 🔧 Debug Commands

### Check User Roles:
```bash
cd backend
php artisan tinker --execute="
App\Models\User::all()->each(function(\$u) {
    echo \$u->email . ' - Role: ' . \$u->role . ' - Super Admin: ' . (\$u->is_super_admin ? 'YES' : 'NO') . ' - School: ' . (\$u->school_id ?? 'NULL') . PHP_EOL;
});
"
```

### Check School Associations:
```bash
php artisan tinker --execute="
App\Models\School::with('users', 'classes')->get()->each(function(\$s) {
    echo \$s->name . ' - Users: ' . \$s->users->count() . ' - Classes: ' . \$s->classes->count() . PHP_EOL;
});
"
```

### Reset System (if needed):
```bash
# WARNING: This will reset all data
php artisan migrate:fresh --seed
php artisan db:seed --class=SchoolSeeder
php artisan app:update-school-associations
php artisan app:set-super-admin admin@school.com
```
