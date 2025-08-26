<template>
  <div class="w-full h-full flex flex-col">
    <!-- Header - Sticky Text Only -->
    <div class="sticky top-0 text-center py-4 mb-4 z-10">
      <h2 class="text-3xl font-bold text-white mb-2">Welcome Back</h2>
      <p class="text-gray-300 text-sm">Sign in to your account</p>
    </div>

    <!-- Scrollable Form Content -->
    <div class="flex-1 overflow-y-auto scrollbar-hide px-1">
      <!-- Auth Error Display -->
      <div
        v-if="authError"
        class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-lg flex items-start space-x-3"
      >
        <svg
          class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
        <div>
          <h4 class="text-red-400 font-medium text-sm">Login Error</h4>
          <p class="text-red-300 text-sm mt-1">{{ authError }}</p>
        </div>
      </div>

      <form class="space-y-6" @submit.prevent="handleSubmit">
        <!-- Email -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-200">Email Address</label>
          <div class="relative">
            <input
              v-model="formData.email"
              type="email"
              placeholder="your@email.com"
              :class="[
                'w-full px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('email')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <svg
                class="h-5 w-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                />
              </svg>
            </div>
          </div>
          <p
            v-if="hasError('email')"
            class="text-red-400 text-sm flex items-center"
          >
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
            {{ getErrorMessage("email") }}
          </p>
        </div>

        <!-- Password -->
        <div class="space-y-2">
          <div
            class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-1 sm:space-y-0"
          >
            <label class="text-sm font-medium text-gray-200">Password</label>
            <button
              type="button"
              class="text-xs text-blue-400 hover:text-blue-300 transition-colors"
            >
              Forgot password?
            </button>
          </div>
          <div class="relative">
            <input
              v-model="formData.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter your password"
              autocomplete="current-password"
              :class="[
                'w-full px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('password')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors"
            >
              <svg
                v-if="!showPassword"
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>
              <svg
                v-else
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                />
              </svg>
            </button>
          </div>
          <p
            v-if="hasError('password')"
            class="text-red-400 text-sm flex items-center"
          >
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
            {{ getErrorMessage("password") }}
          </p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
          <label class="flex items-center space-x-2 cursor-pointer">
            <input
              v-model="rememberMe"
              type="checkbox"
              class="w-4 h-4 text-blue-600 bg-white/10 border-white/20 rounded focus:ring-blue-500 focus:ring-2"
            />
            <span class="text-sm text-gray-300">Remember me</span>
          </label>
        </div>

        <!-- Login Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <span v-if="!isLoading" class="flex items-center justify-center">
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
              />
            </svg>
            Sign In
          </span>
          <span v-else class="flex items-center justify-center">
            <svg
              class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            Signing in...
          </span>
        </button>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-white/20"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-transparent text-gray-400"
              >Or continue with</span
            >
          </div>
        </div>

        <!-- Social Login Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button
            type="button"
            @click="handleSocialLogin('google')"
            class="flex justify-center items-center px-4 py-2 border border-white/20 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-colors duration-200"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24">
              <path
                fill="currentColor"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
              />
              <path
                fill="currentColor"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
              />
              <path
                fill="currentColor"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
              />
              <path
                fill="currentColor"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
              />
            </svg>
          </button>
          <button
            type="button"
            @click="handleSocialLogin('facebook')"
            class="flex justify-center items-center px-4 py-2 border border-white/20 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-colors duration-200"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
              />
            </svg>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, ref, computed } from "vue";

const props = defineProps({
  errors: {
    type: Object,
    default: () => ({}),
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  initialData: {
    type: Object,
    default: () => ({ email: "", password: "" }),
  },
  authError: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["submit", "update:data", "social-login"]);

const formData = reactive({
  email: props.initialData.email,
  password: props.initialData.password,
});

const showPassword = ref(false);
const rememberMe = ref(false);

// Watch for changes and emit to parent
watch(
  formData,
  (newData) => {
    emit("update:data", { ...newData });
  },
  { deep: true }
);

const handleSubmit = () => {
  emit("submit", { ...formData });
};

const hasError = (field) => {
  return !!props.errors[field];
};

const getErrorMessage = (field) => {
  const error = props.errors[field];
  if (!error) return "";

  // Define user-friendly error messages for login
  const friendlyMessages = {
    email: {
      required: "Email is required",
      invalid: "Please enter a valid email address",
      not_found:
        "This email is not registered. Would you like to create an account instead?",
      incorrect: "Incorrect email address",
    },
    password: {
      required: "Password is required",
      incorrect: "Incorrect password",
      too_short: "Password is too short",
      invalid: "Invalid password format",
    },
  };

  // Try to get a friendly message, otherwise use the original error
  const fieldMessages = friendlyMessages[field];
  if (
    fieldMessages &&
    fieldMessages[error.toLowerCase().replace(/\s+/g, "_")]
  ) {
    return fieldMessages[error.toLowerCase().replace(/\s+/g, "_")];
  }

  return error;
};

const handleSocialLogin = (provider) => {
  emit("social-login", provider);
};
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}
</style>
