// ... existing code ... // ... existing code ...

<template>
  <div class="min-h-screen bg-gray-900 relative">
    <!-- Glassmorphism Background Overlay -->
    <div
      class="absolute inset-0 bg-gradient-to-br from-purple-900/20 via-blue-900/20 to-green-900/20 backdrop-blur-sm"
    ></div>

    <!-- Main Container -->
    <div
      class="relative w-screen h-screen rounded-3xl overflow-hidden shadow-2xl backdrop-blur-lg bg-white/5"
    >
      <!-- Background Video -->
      <video
        autoplay
        muted
        loop
        playsinline
        class="absolute inset-0 w-full h-full object-cover"
        style="z-index: 1"
      >
        <source src="/src/videos/loginBackground.mp4" type="video/mp4" />
        <!-- Fallback background image if video fails to load -->
        <div
          class="absolute inset-0 bg-cover bg-center"
          style="
            background-image: url('https://placehold.co/1200x600/4a5568/4a5568?text=Person+Working+Background');
          "
        ></div>
      </video>

      <!-- Content Container -->
      <div class="relative h-full" style="z-index: 10">
        <div class="flex h-full">
          <!-- Left Section -->
          <div class="flex-1 relative">
            <!-- Subtle overlay for left section -->
            <div
              class="absolute inset-0 bg-black bg-opacity-20 backdrop-blur-sm"
              style="z-index: 2"
            ></div>

            <div
              class="relative h-full flex items-center justify-center p-12"
              style="z-index: 3"
            >
              <div class="max-w-md">
                <h1
                  class="text-5xl font-bold text-white mb-6 leading-tight drop-shadow-lg"
                >
                  Let's Get Started
                </h1>
                <p
                  class="text-white text-lg leading-relaxed opacity-90 drop-shadow-md"
                >
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
            <div
              class="absolute inset-0 bg-black bg-opacity-70"
              style="z-index: 2"
            ></div>

            <!-- Form Content -->
            <div
              class="relative h-full flex flex-col items-center justify-center p-4 md:p-20 overflow-y-auto scrollbar-hide"
              style="z-index: 10"
            >
              <div class="w-full max-w-md-[90vh] bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-4 md:p-6 border border-white/20 mb-4 h-[80vh] flex flex-col">
                <!-- Login Form -->
                <LoginForm
                  v-if="isSignIn"
                  :errors="signInErrors"
                  :is-loading="authStore.isLoading"
                  :initial-data="signInData"
                  :auth-error="authStore.error"
                  @submit="handleSignIn"
                  @update:data="updateSignInData"
                  @social-login="handleSocialLogin"
                />

                <!-- Register Form -->
                <RegisterForm
                  v-else
                  :errors="registerErrors"
                  :is-loading="authStore.isLoading"
                  :initial-data="registerData"
                  :auth-error="authStore.error"
                  @submit="handleRegister"
                  @update:data="updateRegisterData"
                />
              </div>
              
              <!-- Form Toggle Link - Below the form container -->
              <div class="w-full max-w-md text-center">
                <p class="text-gray-300">
                  {{
                    isSignIn
                      ? "Don't have account? "
                      : "Already have an account? "
                  }}
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
</template>

<script setup>
import { ref, reactive, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import * as yup from "yup";
import { useAuthStore } from "@/stores/auth";
import LoginForm from "@/components/auth/LoginForm.vue";
import RegisterForm from "@/components/auth/RegisterForm.vue";

const router = useRouter();
const route = useRoute();

// Form toggle state
const isSignIn = ref(true);
const authStore = useAuthStore();

// Form data
const signInData = reactive({
  email: "",
  password: "",
});

const registerData = reactive({
  first_name: "",
  last_name: "",
  username: "",
  email: "",
  gender: "",
  date_of_birth: "",
  city: "",
  country: "",
  role: "student", // Default to student
  password: "",
  confirmPassword: "",
});

// Validation errors
const signInErrors = ref({});
const registerErrors = ref({});

// Validation schemas
const signInSchema = yup.object({
  email: yup
    .string()
    .email("Please enter a valid email")
    .required("Email is required"),
  password: yup
    .string()
    .min(6, "Password must be at least 6 characters")
    .required("Password is required"),
});

const registerSchema = yup.object({
  first_name: yup.string().required("First name is required"),
  last_name: yup.string().required("Last name is required"),
  username: yup
    .string()
    .min(3, "Username must be at least 3 characters")
    .required("Username is required"),
  email: yup
    .string()
    .email("Please enter a valid email")
    .required("Email is required"),
  gender: yup
    .string()
    .oneOf(["male", "female"], "Please select a valid gender")
    .required("Gender is required"),
  date_of_birth: yup
    .string()
    .required("Date of birth is required"),
  city: yup.string().required("City is required"),
  country: yup.string().required("Country is required"),
  password: yup
    .string()
    .min(6, "Password must be at least 6 characters")
    .required("Password is required"),
  confirmPassword: yup
    .string()
    .oneOf([yup.ref("password")], "Passwords must match")
    .required("Confirm password is required"),
  role: yup
    .string()
    .oneOf(["student", "teacher"], "Please select a valid role")
    .required("Role is required"),
});

// Update form data methods
const updateSignInData = (data) => {
  Object.assign(signInData, data);
};

const updateRegisterData = (data) => {
  Object.assign(registerData, data);
};

// Sign In handler
const handleSignIn = async (formData) => {
  try {
    signInErrors.value = {};
    await signInSchema.validate(formData, { abortEarly: false });

    await authStore.login({
      email: formData.email,
      password: formData.password,
    });
  } catch (error) {
    if (error.inner) {
      // Validation errors
      error.inner.forEach((err) => {
        signInErrors.value[err.path] = err.message;
      });
    } else {
      // API errors
      console.error("Login error:", error);
    }
  }
};

// Register handler
const handleRegister = async (formData) => {
  try {
    registerErrors.value = {};
    await registerSchema.validate(formData, { abortEarly: false });

    const userData = {
      first_name: formData.first_name,
      last_name: formData.last_name,
      username: formData.username,
      email: formData.email,
      password: formData.password,
      password_confirmation: formData.confirmPassword,
      role: formData.role,
      // Additional fields for role-specific data
      gender: formData.gender,
      date_of_birth: formData.date_of_birth,
      city: formData.city,
      country: formData.country,
    };

    await authStore.register(userData);
  } catch (error) {
    if (error.inner) {
      // Validation errors
      error.inner.forEach((err) => {
        registerErrors.value[err.path] = err.message;
      });
    } else {
      // API errors
      console.error("Registration error:", error);
    }
  }
};

// Social login handler
const handleSocialLogin = async (provider) => {
  try {
    // Instead of popup, redirect to the social login URL directly
    window.location.href = `http://localhost:8000/api/auth/social/${provider}`;
  } catch (error) {
    console.error('Social login error:', error);
  }
};

// Helper to get error message
const getErrorMessage = (errors, field) => {
  return errors[field] || "";
};

// Helper to check if field has error
const hasError = (errors, field) => {
  return !!errors[field];
};

// Check if user is already authenticated when component mounts
onMounted(() => {
  // Check for social login callback with token
  if (route.query.token && route.query.user) {
    try {
      const token = route.query.token;
      const user = JSON.parse(decodeURIComponent(route.query.user));
      
      // Store token and user in auth store
      authStore.setAuthData(token, user);
      
      // Redirect to appropriate dashboard
      const redirectPath = authStore.getRedirectPath(user.role);
      router.replace(redirectPath);
      return;
    } catch (error) {
      console.error('Error processing social login callback:', error);
    }
  }

  // Check for social login errors
  if (route.query.error) {
    console.error('Social login error:', route.query.error, route.query.message);
  }

  if (authStore.isAuthenticated) {
    // Redirect to appropriate dashboard based on user role
    const redirectPath =
      route.query.redirect || authStore.getRedirectPath(authStore.user?.role);
    router.replace(redirectPath);
  }
});

// Watch for authentication state changes
watch(
  () => authStore.isAuthenticated,
  (isAuth) => {
    if (isAuth) {
      // Redirect to appropriate dashboard based on user role
      const redirectPath =
        route.query.redirect || authStore.getRedirectPath(authStore.user?.role);
      router.replace(redirectPath);
    }
  }
);
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
  background: linear-gradient(to right, #6717cd, #296ff9);
  box-shadow: 0 4px 6px rgba(103, 23, 205, 0.25);
  transition: background 0.3s ease, transform 0.2s ease;
}

.gradient-button:hover {
  background: linear-gradient(to right, #5a14b8, #2460db);
  box-shadow: 0 6px 8px rgba(103, 23, 205, 0.35);
  transform: translateY(-1px);
}

.gradient-button:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(103, 23, 205, 0.2);
}

/* Gradient Text Styling */
.gradient-text {
  background: linear-gradient(to right, #6717cd, #296ff9);
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
    min-height: 40vh;
  }

  .text-5xl {
    font-size: 2rem;
  }

  .p-12 {
    padding: 1rem;
  }

  .p-8 {
    padding: 0.75rem;
  }

  /* Adjust social icons for mobile */
  .space-x-4 > * + * {
    margin-left: 1rem;
  }
  
  /* Make form container take more space on mobile */
  .flex-1:nth-child(2) {
    min-height: 60vh;
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
  
  /* Hide left section on very small screens */
  .flex-1:first-child {
    display: none;
  }
  
  .flex-1:nth-child(2) {
    min-height: 100vh;
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

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
</style>
