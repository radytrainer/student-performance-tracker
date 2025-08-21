# 🖥️ Frontend Testing Checklist

## Prerequisites
1. ✅ Backend is running: `cd backend && php artisan serve`
2. ✅ Frontend is running: `cd frontend && npm run dev`
3. ✅ Super admin is configured (see TESTING_GUIDE.md)

## 🔐 Authentication Tests

### Super Admin Login
1. **Navigate to:** `http://localhost:3000/login`
2. **Enter credentials:**
   - Email: `admin@school.com`
   - Password: `admin123`
3. **Expected result:** Redirect to `/super-admin/dashboard`
4. **Verify:** Page title shows "Super Admin Dashboard"

### Navigation Test
1. **Check URL:** Should be `/super-admin/dashboard`
2. **Check page content:**
   - ✅ "Super Admin Dashboard" heading
   - ✅ Statistics cards showing counts
   - ✅ "Add School" button visible
   - ✅ Schools list table

## 📊 Dashboard Functionality

### Statistics Cards
Check that these cards display numbers (not 0 unless system is empty):
- 🏫 **Total Schools** 
- 👥 **Total Users**
- 🛡️ **Sub Admins**
- 🎓 **Students**

### Schools Management
1. **View Schools List:**
   - ✅ Table shows existing schools
   - ✅ Each school shows: Name, Code, User counts, Status
   - ✅ Action buttons visible: Edit, Add Sub Admin, View, Delete

2. **Add New School:**
   - ✅ Click "Add School" button
   - ✅ Modal opens with form
   - ✅ Fill in: Name, Code, Email, Status
   - ✅ Click "Save"
   - ✅ Modal closes, school appears in list

3. **Create Sub-Admin:**
   - ✅ Click "Add Sub Admin" button for a school
   - ✅ Modal opens with sub-admin form
   - ✅ Fill in all required fields
   - ✅ Click "Create Sub Admin"
   - ✅ Success message appears

## 🔒 Permission Testing

### Super Admin Permissions
The super admin should see:
- ✅ Super Admin Dashboard
- ✅ All schools regardless of their assignment
- ✅ System-wide statistics
- ✅ Ability to create/edit/delete schools
- ✅ Ability to create sub-admins

### Browser Developer Tools Check
1. **Open Developer Tools (F12)**
2. **Check Console:** No error messages related to permissions
3. **Check Network tab:** API calls to `/super-admin/*` should return 200 status
4. **Check Application/Local Storage:** Should contain auth token

## 🧪 Sub-Admin Testing

### Create and Test Sub-Admin
1. **Create a sub-admin** using the super admin interface
2. **Logout** from super admin
3. **Login with sub-admin credentials**
4. **Expected result:** Redirect to `/admin/dashboard` (not super-admin)
5. **Verify:** Cannot access `/super-admin/dashboard` (should redirect or show error)

### Sub-Admin Limitations
Sub-admin should:
- ✅ Only see users from their school
- ✅ Only see classes from their school
- ❌ NOT see super admin menu items
- ❌ NOT be able to access `/super-admin/*` routes

## 🚨 Error Scenarios

### Invalid Login
1. **Try logging in with wrong credentials**
2. **Expected:** Error message displayed
3. **Verify:** No redirect occurs

### Unauthorized Access
1. **While logged out, try to access:** `/super-admin/dashboard`
2. **Expected:** Redirect to login page

### Permission Denied
1. **As sub-admin, try to access:** `/super-admin/dashboard`
2. **Expected:** Access denied or redirect

## 📱 UI/UX Checks

### Visual Elements
- ✅ Page layouts are responsive
- ✅ Buttons have hover effects
- ✅ Forms validate properly
- ✅ Modals open and close correctly
- ✅ Loading states show when making API calls

### Data Consistency
- ✅ Statistics update after creating schools/users
- ✅ Lists refresh after CRUD operations
- ✅ Success/error messages are clear

## 🔄 End-to-End Workflow

### Complete Super Admin Workflow
1. **Login as super admin** ✅
2. **Create a new school** ✅
3. **Create a sub-admin for that school** ✅
4. **Logout and login as sub-admin** ✅
5. **Verify sub-admin can only see their school's data** ✅
6. **Try to access super admin features (should fail)** ✅
7. **Logout and login back as super admin** ✅
8. **Delete the test school** ✅

## 📝 Common Issues & Solutions

### Issue: "Component not found" error
**Solution:** Check that the SuperAdminDashboard.vue component exists and is properly imported in router

### Issue: Blank page after login
**Solution:** Check browser console for JavaScript errors, verify API endpoints are responding

### Issue: "Permission denied" for super admin
**Solution:** Verify user has `is_super_admin: true` in database

### Issue: Stats showing 0
**Solution:** Make sure schools and users exist in database, check API endpoints

### Issue: Can't create sub-admin
**Solution:** Check API response in network tab, verify school ID is valid

## ✅ Success Criteria

All tests pass when:
- ✅ Super admin can login and access dashboard
- ✅ Super admin can manage all schools
- ✅ Super admin can create sub-admins
- ✅ Sub-admins are restricted to their school only
- ✅ Data isolation works correctly
- ✅ UI is responsive and user-friendly
- ✅ No JavaScript errors in console
- ✅ API calls return expected responses
