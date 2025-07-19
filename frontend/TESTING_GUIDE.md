# Authentication System Testing Guide

## Prerequisites
1. Make sure the development server is running
2. The backend API is not implemented yet, so we'll test frontend validation and UI functionality

## Starting the Development Server

```bash
cd frontend
npm run dev
```

The app should be available at `http://localhost:5173` (or the port shown in terminal)

## Testing Scenarios

### 1. 🔍 **Login Form Validation Testing**

#### Test Invalid Email Formats:
1. Go to login form
2. Enter invalid emails and click Login:
   - `invalid-email` → Should show "Please enter a valid email"
   - `test@` → Should show "Please enter a valid email"
   - Leave empty → Should show "Email is required"

#### Test Password Validation:
1. Enter valid email: `test@example.com`
2. Test password scenarios:
   - Leave empty → Should show "Password is required"
   - Enter `123` → Should show "Password must be at least 6 characters"
   - Enter `123456` → Should pass validation

#### Test Valid Login:
1. Enter: `admin@school.com` and `admin123`
2. Click Login
3. **Expected Result**: Form should attempt login (will show API error since backend isn't ready)

---

### 2. 📝 **Register Form Testing**

#### Access Register Form:
1. Click "Let's register here" link to switch to register form
2. Verify all fields are present:
   - First Name
   - Last Name  
   - Username
   - Email
   - Role Selection (dropdown)
   - Password
   - Confirm Password

#### Test Field Validations:

**First Name:**
- Leave empty → "First name is required"
- Enter valid name → No error

**Last Name:**
- Leave empty → "Last name is required"
- Enter valid name → No error

**Username:**
- Leave empty → "Username is required"
- Enter `ab` → "Username must be at least 3 characters"
- Enter `johndoe` → No error

**Email:**
- Leave empty → "Email is required"
- Enter `invalid` → "Please enter a valid email"
- Enter `john@example.com` → No error

**Role Selection:**
- **Verify dropdown only shows**: Student and Teacher
- **Verify default selection**: Should default to "Student"
- **Verify admin restriction note**: Should show text about admin accounts

**Password:**
- Leave empty → "Password is required"
- Enter `123` → "Password must be at least 6 characters"
- Enter `password123` → No error

**Confirm Password:**
- Leave empty → "Confirm password is required"
- Enter different password → "Passwords must match"
- Enter matching password → No error

---

### 3. 🎯 **Role Selection Testing**

#### Test Role Dropdown:
1. Click the role dropdown
2. **Verify options**:
   - ✅ Student (should be default)
   - ✅ Teacher
   - ❌ Admin (should NOT be present)

#### Test Default Role:
1. Refresh the register form
2. **Verify**: Role should automatically be set to "Student"
3. **Verify**: Info text shows admin restriction notice

---

### 4. 🔄 **Form Switching Testing**

#### Test Form Toggle:
1. Start on Login form
2. Click "Let's register here" → Should switch to Register form
3. Click "Login here" → Should switch back to Login form
4. **Verify**: Form data is preserved when switching
5. **Verify**: Validation errors are cleared when switching

---

### 5. 🚫 **Error Display Testing**

#### Test Validation Errors:
1. Try to submit empty forms
2. **Verify**: Red error messages appear under each field
3. **Verify**: Input borders turn red for invalid fields
4. **Verify**: Buttons show loading state during submission

#### Test Multiple Validation Errors:
1. Fill register form with invalid data:
   - First Name: (empty)
   - Username: `ab`
   - Email: `invalid-email`
   - Password: `123`
   - Confirm Password: `different`
2. Click Register
3. **Verify**: All errors show simultaneously

---

### 6. 🎨 **UI/UX Testing**

#### Test Visual Elements:
1. **Form styling**: Glassmorphism effect, background video
2. **Button states**: 
   - Normal state
   - Hover effect (gradient change)
   - Disabled state during loading
   - Loading text ("Logging in..." / "Creating Account...")
3. **Error styling**: Red borders and error messages
4. **Role dropdown**: Custom arrow, proper options

#### Test Responsive Design:
1. Test on different screen sizes
2. Test mobile view
3. **Verify**: Layout adapts properly

---

### 7. 🛣️ **Navigation & Routing Testing**

#### Test Route Protection:
1. Try to access protected routes directly:
   - `/admin/dashboard`
   - `/teacher/dashboard` 
   - `/student/dashboard`
2. **Expected**: Should redirect to login page

#### Test Role-Based Dashboard Creation:
1. Navigate to dashboard routes manually:
   - `http://localhost:5173/admin/dashboard`
   - `http://localhost:5173/teacher/dashboard`
   - `http://localhost:5173/student/dashboard`
2. **Verify**: Each dashboard has different content

---

## 🔧 **Testing with Backend API (When Available)**

### Mock API Testing:
If you want to test the full flow, you can temporarily modify the auth store to mock API responses:

```javascript
// In stores/auth.js - temporary for testing
const login = async (credentials) => {
  try {
    isLoading.value = true
    error.value = null
    
    // Mock API response
    user.value = {
      id: 1,
      email: credentials.email,
      role: 'student', // Change this to test different roles
      first_name: 'John',
      last_name: 'Doe'
    }
    isAuthenticated.value = true
    
    // Role-based redirect
    const redirectPath = getRedirectPath(user.value?.role)
    router.push(redirectPath)
  } catch (err) {
    error.value = 'Login failed'
  } finally {
    isLoading.value = false
  }
}
```

### Test Different User Roles:
1. Mock login with `role: 'student'` → Should redirect to `/student/dashboard`
2. Mock login with `role: 'teacher'` → Should redirect to `/teacher/dashboard`  
3. Mock login with `role: 'admin'` → Should redirect to `/admin/dashboard`

---

## ✅ **Expected Results Checklist**

### Login Form:
- [ ] Email validation works
- [ ] Password validation works  
- [ ] Loading states work
- [ ] Error messages display properly

### Register Form:
- [ ] All field validations work
- [ ] Role dropdown shows only Student/Teacher
- [ ] Default role is Student
- [ ] Password confirmation works
- [ ] Admin restriction notice shows

### Navigation:
- [ ] Form switching works
- [ ] Route protection works
- [ ] Role-based dashboards exist

### UI/UX:
- [ ] Validation errors show with red styling
- [ ] Loading buttons work
- [ ] Responsive design works
- [ ] Form preserves data during switches

---

## 🐛 **Common Issues to Check**

1. **Console Errors**: Check browser dev tools for any JavaScript errors
2. **Network Errors**: API calls will fail (expected without backend)
3. **Validation**: Make sure Yup validation triggers properly
4. **Routing**: Ensure Vue Router navigation works correctly
5. **State Management**: Verify Pinia store updates properly

---

## 📝 **Test Report Template**

Use this template to report results:

```
## Test Results

### Login Form Validation: ✅/❌
- Email validation: ✅/❌
- Password validation: ✅/❌
- Error display: ✅/❌

### Register Form: ✅/❌  
- Field validations: ✅/❌
- Role selection: ✅/❌
- Default role: ✅/❌

### UI/UX: ✅/❌
- Visual styling: ✅/❌
- Responsive design: ✅/❌
- Loading states: ✅/❌

### Issues Found:
1. [Description of any issues]
2. [Description of any issues]
```
