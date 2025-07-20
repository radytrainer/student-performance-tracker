// Quick Authentication System Test Script
// Run this in browser console to test validation schemas

import * as yup from 'yup';

// Test Login Schema
const signInSchema = yup.object({
  email: yup.string().email('Please enter a valid email').required('Email is required'),
  password: yup.string().min(6, 'Password must be at least 6 characters').required('Password is required')
});

// Test Register Schema  
const registerSchema = yup.object({
  first_name: yup.string().required('First name is required'),
  last_name: yup.string().required('Last name is required'),
  username: yup.string().min(3, 'Username must be at least 3 characters').required('Username is required'),
  email: yup.string().email('Please enter a valid email').required('Email is required'),
  password: yup.string().min(6, 'Password must be at least 6 characters').required('Password is required'),
  confirmPassword: yup.string()
    .oneOf([yup.ref('password')], 'Passwords must match')
    .required('Confirm password is required'),
  role: yup.string().oneOf(['student', 'teacher'], 'Please select a valid role').required('Role is required')
});

// Test Cases
const testCases = {
  validLogin: {
    email: 'test@example.com',
    password: 'password123'
  },
  invalidLogin: {
    email: 'invalid-email',
    password: '123'
  },
  validRegister: {
    first_name: 'John',
    last_name: 'Doe', 
    username: 'johndoe',
    email: 'john@example.com',
    password: 'password123',
    confirmPassword: 'password123',
    role: 'student'
  },
  invalidRegister: {
    first_name: '',
    last_name: '',
    username: 'ab',
    email: 'invalid',
    password: '123',
    confirmPassword: 'different',
    role: 'admin' // Should fail
  }
};

// Run Tests
console.log('🧪 Testing Authentication Validation Schemas...\n');

// Test Valid Login
try {
  await signInSchema.validate(testCases.validLogin);
  console.log('✅ Valid Login Test: PASSED');
} catch (error) {
  console.log('❌ Valid Login Test: FAILED', error.message);
}

// Test Invalid Login
try {
  await signInSchema.validate(testCases.invalidLogin, { abortEarly: false });
  console.log('❌ Invalid Login Test: FAILED (should have thrown errors)');
} catch (error) {
  console.log('✅ Invalid Login Test: PASSED (caught validation errors)');
  error.inner.forEach(err => console.log(`  - ${err.path}: ${err.message}`));
}

// Test Valid Register
try {
  await registerSchema.validate(testCases.validRegister);
  console.log('✅ Valid Register Test: PASSED');
} catch (error) {
  console.log('❌ Valid Register Test: FAILED', error.message);
}

// Test Invalid Register
try {
  await registerSchema.validate(testCases.invalidRegister, { abortEarly: false });
  console.log('❌ Invalid Register Test: FAILED (should have thrown errors)');
} catch (error) {
  console.log('✅ Invalid Register Test: PASSED (caught validation errors)');
  error.inner.forEach(err => console.log(`  - ${err.path}: ${err.message}`));
}

console.log('\n🎯 Schema validation tests completed!');
