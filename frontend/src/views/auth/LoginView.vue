"// ... existing code ...
// ... existing code ...

<template>
  <div class="min-h-screen bg-gray-900 relative">
    <!-- Glassmorphism Background Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 via-blue-900/20 to-green-900/20 backdrop-blur-sm"></div>

    <!-- Main Container -->
    <div class="relative w-screen h-screen rounded-3xl overflow-hidden shadow-2xl backdrop-blur-lg bg-white/5">
      <!-- Background Video -->
      <video
        autoplay
        muted
        loop
        playsinline
        class="absolute inset-0 w-full h-full object-cover"
        style="z-index: 1;"
      >
        <source src="/src/videos/loginBackground.mp4" type="video/mp4">
        <!-- Fallback background image if video fails to load -->
        <div 
          class="absolute inset-0 bg-cover bg-center"
          style="background-image: url('https://placehold.co/1200x600/4a5568/4a5568?text=Person+Working+Background')"
        ></div>
      </video>

      <!-- Content Container -->
      <div class="relative h-full" style="z-index: 10;">
        <div class="flex h-full">
          <!-- Left Section -->
          <div class="flex-1 relative">
            <!-- Subtle overlay for left section -->
            <div class="absolute inset-0 bg-black bg-opacity-20 backdrop-blur-sm" style="z-index: 2;"></div>

            <div class="relative h-full flex items-center justify-center p-12" style="z-index: 3;">
              <div class="max-w-md">
                <h1 class="text-5xl font-bold text-white mb-6 leading-tight drop-shadow-lg">
                  Let's Get Started
                </h1>
                <p class="text-white text-lg leading-relaxed opacity-90 drop-shadow-md">
                  Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
                  Maecenas placerat ultricies libero eu pharetra. Vestibulum a 
                  ultricies augue.
                </p>
              </div>
            </div>
          </div>

          <!-- Right Section -->
          <div class="flex-1 relative">
            <!-- Semi-transparent overlay for right section -->
            <div class="absolute inset-0 bg-black bg-opacity-70" style="z-index: 2;"></div>

            <!-- Form Content -->
            <div class="relative h-full flex items-center justify-center p-8" style="z-index: 10;">
              <div class="w-full max-w-sm">
                <!-- Dynamic Form Title -->
                <h2 class="text-3xl font-bold text-white mb-8 text-center">
                  {{ isSignIn ? 'Login' : 'Register' }}
                </h2>

                <!-- Auth Error Display -->
                <div v-if="authStore.error" class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg">
                  <p class="text-red-400 text-sm text-center">{{ authStore.error }}</p>
                </div>

                <!-- Login Form -->
                <form v-if="isSignIn" class="space-y-6" @submit.prevent="handleSignIn">
                  <!-- Your Email -->
                  <div class="relative">
                    <input
                      v-model="signInData.email"
                      type="email"
                      placeholder="Your Email"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(signInErrors, 'email') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(signInErrors, 'email')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(signInErrors, 'email') }}
                    </p>
                  </div>

                  <!-- Password -->
                  <div class="relative">
                    <input
                      v-model="signInData.password"
                      type="password"
                      placeholder="Password"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(signInErrors, 'password') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(signInErrors, 'password')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(signInErrors, 'password') }}
                    </p>
                  </div>

                  <!-- Login Button -->
                  <button
                    type="submit"
                    :disabled="authStore.isLoading"
                    class="w-full text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 mt-8 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 gradient-button disabled:opacity-50"
                  >
                    {{ authStore.isLoading ? 'Logging in...' : 'Login' }}
                  </button>
                </form>

                <!-- Register Form -->
                <form v-else class="space-y-4" @submit.prevent="handleRegister">
                  <!-- First Name -->
                  <div class="relative">
                    <input
                      v-model="registerData.first_name"
                      type="text"
                      placeholder="First Name"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(registerErrors, 'first_name') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(registerErrors, 'first_name')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(registerErrors, 'first_name') }}
                    </p>
                  </div>

                  <!-- Last Name -->
                  <div class="relative">
                    <input
                      v-model="registerData.last_name"
                      type="text"
                      placeholder="Last Name"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(registerErrors, 'last_name') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(registerErrors, 'last_name')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(registerErrors, 'last_name') }}
                    </p>
                  </div>

                  <!-- Username -->
                  <div class="relative">
                    <input
                      v-model="registerData.username"
                      type="text"
                      placeholder="Username"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(registerErrors, 'username') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(registerErrors, 'username')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(registerErrors, 'username') }}
                    </p>
                  </div>

                  <!-- Your Email -->
                  <div class="relative">
                    <input
                      v-model="registerData.email"
                      type="email"
                      placeholder="Your Email"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(registerErrors, 'email') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(registerErrors, 'email')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(registerErrors, 'email') }}
                    </p>
                  </div>

                  <!-- Role Selection -->
                  <div class="relative">
                    <select
                      v-model="registerData.role"
                      :class="[
                        'w-full bg-transparent text-white border-0 border-b pb-2 focus:outline-none transition-colors duration-200 appearance-none',
                        hasError(registerErrors, 'role') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    >
                      <option 
                        v-for="role in availableRoles" 
                        :key="role.value" 
                        :value="role.value"
                        class="text-gray-800"
                      >
                        {{ role.label }}
                      </option>
                    </select>
                    <p v-if="hasError(registerErrors, 'role')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(registerErrors, 'role') }}
                    </p>
                    <p class="text-gray-400 text-xs mt-1">
                      Default: Student (Admin accounts are assigned by system administrators)
                    </p>
                  </div>

                  <!-- Create Password -->
                  <div class="relative">
                    <input
                      v-model="registerData.password"
                      type="password"
                      placeholder="Create Password"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(registerErrors, 'password') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(registerErrors, 'password')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(registerErrors, 'password') }}
                    </p>
                  </div>

                  <!-- Repeat Password -->
                  <div class="relative">
                    <input
                      v-model="registerData.confirmPassword"
                      type="password"
                      placeholder="Repeat Password"
                      :class="[
                        'w-full bg-transparent text-white placeholder-gray-400 border-0 border-b pb-2 focus:outline-none transition-colors duration-200',
                        hasError(registerErrors, 'confirmPassword') ? 'border-red-500' : 'border-gray-500 focus:border-green-500'
                      ]"
                    />
                    <p v-if="hasError(registerErrors, 'confirmPassword')" class="text-red-400 text-sm mt-1">
                      {{ getErrorMessage(registerErrors, 'confirmPassword') }}
                    </p>
                  </div>

                  <!-- Register Button -->
                  <button
                    type="submit"
                    :disabled="authStore.isLoading"
                    class="w-full text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 mt-8 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 gradient-button disabled:opacity-50"
                  >
                    {{ authStore.isLoading ? 'Creating Account...' : 'Register' }}
                  </button>
                </form>

                <!-- Social Media Icons -->
                <div class="flex items-center justify-center mt-8">
                  <!-- OR with lines -->
                  <div class="flex items-center w-full">
                    <div class="flex-1 h-px bg-gray-500"></div>
                    <span class="text-white font-medium px-4">OR</span>
                    <div class="flex-1 h-px bg-gray-500"></div>
                  </div>
                </div>

                <!-- Social Icons Row -->
                <div class="flex justify-center space-x-4 mt-6">
                  <!-- Facebook -->
                  <button 
                    @click="handleSocialLogin('facebook')"
                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-lg"
                    aria-label="Continue with Facebook"
                  >
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                  </button>

                  <!-- Twitter -->
                  <button 
                    @click="handleSocialLogin('twitter')"
                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 shadow-lg"
                    aria-label="Continue with Twitter"
                  >
                    <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                  </button>

                  <!-- Google -->
                  <button 
                    @click="handleSocialLogin('google')"
                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 shadow-lg"
                    aria-label="Continue with Google"
                  >
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                      <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                      <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                      <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                      <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                  </button>
                </div>

                <!-- Form Toggle Link -->
                <div class="text-center mt-6">
                  <p class="text-gray-300">
                    {{ isSignIn ? "Don't have account? " : "Already have an account? " }}
                    <a 
                      href="#" 
                      @click.prevent="isSignIn = !isSignIn" 
                      class="font-medium transition-colors duration-200 focus:outline-none focus:underline gradient-text"
                    >
                      {{ isSignIn ? "Let's register here" : "Login here" }}
                    </a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
     
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import * as yup from 'yup'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()

// Form toggle state
const isSignIn = ref(true)
const authStore = useAuthStore()

// Form data
const signInData = reactive({
  email: '',
  password: ''
})

const registerData = reactive({
  first_name: '',
  last_name: '',
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  role: 'student' // Default to student
})

// Validation errors
const signInErrors = ref({})
const registerErrors = ref({})

// Validation schemas
const signInSchema = yup.object({
  email: yup.string().email('Please enter a valid email').required('Email is required'),
  password: yup.string().min(6, 'Password must be at least 6 characters').required('Password is required')
})

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
})

// Available roles for registration (excluding admin)
const availableRoles = [
  { value: 'student', label: 'Student' },
  { value: 'teacher', label: 'Teacher' }
]

// Sign In handler
const handleSignIn = async () => {
  try {
    signInErrors.value = {}
    await signInSchema.validate(signInData, { abortEarly: false })
    
    await authStore.login({
      email: signInData.email,
      password: signInData.password
    })
  } catch (error) {
    if (error.inner) {
      // Validation errors
      error.inner.forEach(err => {
        signInErrors.value[err.path] = err.message
      })
    } else {
      // API errors
      console.error('Login error:', error)
    }
  }
}

// Register handler
const handleRegister = async () => {
  try {
    registerErrors.value = {}
    await registerSchema.validate(registerData, { abortEarly: false })
    
    const userData = {
      first_name: registerData.first_name,
      last_name: registerData.last_name,
      username: registerData.username,
      email: registerData.email,
      password: registerData.password,
      role: registerData.role
    }
    
    await authStore.register(userData)
  } catch (error) {
    if (error.inner) {
      // Validation errors
      error.inner.forEach(err => {
        registerErrors.value[err.path] = err.message
      })
    } else {
      // API errors
      console.error('Registration error:', error)
    }
  }
}

// Social login handler
const handleSocialLogin = (provider) => {
  console.log(`Social login with ${provider}`)
  // Add your social login logic here
}

// Helper to get error message
const getErrorMessage = (errors, field) => {
  return errors[field] || ''
}

// Helper to check if field has error
const hasError = (errors, field) => {
  return !!errors[field]
}

// Check if user is already authenticated when component mounts
onMounted(() => {
  if (authStore.isAuthenticated) {
    // Redirect to appropriate dashboard based on user role
    const redirectPath = route.query.redirect || authStore.getRedirectPath(authStore.user?.role)
    router.replace(redirectPath)
  }
})

// Watch for authentication state changes
watch(() => authStore.isAuthenticated, (isAuth) => {
  if (isAuth) {
    // Redirect to appropriate dashboard based on user role
    const redirectPath = route.query.redirect || authStore.getRedirectPath(authStore.user?.role)
    router.replace(redirectPath)
  }
})
</script>

<style scoped>
/* Custom styles for input focus states */
input:focus {
  outline: none;
  border-color: #4ade80;
}

/* Video background styling */
video {
  object-fit: cover;
  object-position: center;
}

/* Enhanced text shadows for better readability */
.drop-shadow-lg {
  text-shadow: 0 10px 15px rgba(0, 0, 0, 0.3);
}

.drop-shadow-md {
  text-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

/* Glassmorphism effects */
.backdrop-blur-lg {
  backdrop-filter: blur(16px);
}

.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Gradient Button Styling */
.gradient-button {
  background: linear-gradient(to right, #6717CD, #296FF9);
  box-shadow: 0 4px 6px rgba(103, 23, 205, 0.25);
  transition: background 0.3s ease, transform 0.2s ease;
}

.gradient-button:hover {
  background: linear-gradient(to right, #5a14b8, #2460db);
  box-shadow: 0 6px 8px rgba(103, 23, 205, 0.35);
  transform: translateY(-1px);
  transform: translateY(-2px);
}

.gradient-button:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(103, 23, 205, 0.2);
}

/* Gradient Text Styling */
.gradient-text {
  background: linear-gradient(to right, #6717CD, #296FF9);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-decoration: none;
}

.gradient-text:hover {
  background: linear-gradient(to right, #5a14b8, #2460db);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-decoration: underline;
}

/* Form transition animations */
form {
  transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .min-h-screen > div:last-child {
    height: 100vh;
    min-height: 100vh;
  }
}

@media (max-width: 768px) {
  .min-h-screen > div:last-child {
    height: 100vh;
    min-height: 100vh;
    border-radius: 0;
  }
  
  .flex {
    flex-direction: column;
  }
  
  .flex-1 {
    flex: none;
    min-height: 50vh;
  }
  
  .text-5xl {
    font-size: 2.5rem;
  }
  
  .p-12 {
    padding: 2rem;
  }
  
  .p-8 {
    padding: 1.5rem;
  }
  
  /* Adjust social icons for mobile */
  .space-x-4 > * + * {
    margin-left: 1rem;
  }
}

@media (max-width: 640px) {
  .rounded-3xl {
    border-radius: 0;
  }
  
  .space-x-4 > * + * {
    margin-left: 0.75rem;
  }
  
  .w-12.h-12 {
    width: 2.5rem;
    height: 2.5rem;
  }
}

/* Performance optimization for video */
video {
  will-change: transform;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

/* Ensure proper layering */
.relative {
  position: relative;
}

/* Enhanced shadow for the main container */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Form transition animations */
form {
  transition: opacity 0.3s ease-in-out;
  transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
}

/* Select dropdown styling */
select {
  background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='white' viewBox='0 0 20 20'%3E%3Cpath d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5rem 1.5rem;
  padding-right: 2.5rem;
}

select option {
  background-color: #374151;
  color: white;
}

/* Error styling */
.border-red-500 {
  border-color: #ef4444 !important;
}
</style>