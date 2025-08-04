<template>
  <div class="w-full h-full flex flex-col">
    <!-- Header - Sticky Text Only -->
    <div class="sticky top-0 text-center py-4 mb-4 z-10">
      <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
        Registration
      </h2>
      <p class="text-gray-300 text-sm">Fill in your details</p>

      <!-- Progress indicator -->
      <div class="flex items-center justify-center mt-3 space-x-3">
        <!-- Step 1 -->
        <div
          :class="[
            'w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 border-2',
            currentStep >= 1 
              ? 'bg-blue-500 border-blue-500' 
              : 'bg-transparent border-white/30'
          ]"
        >
          <svg v-if="currentStep > 1" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
          <span v-else class="text-white text-sm font-bold">1</span>
        </div>
        
        <!-- Connecting line -->
        <div 
          :class="[
            'w-12 h-0.5 transition-all duration-300',
            currentStep === 2 ? 'bg-blue-500' : 'bg-white/30'
          ]"
        ></div>
        
        <!-- Step 2 -->
        <div
          :class="[
            'w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 border-2',
            currentStep === 2 
              ? 'bg-blue-500 border-blue-500' 
              : 'bg-transparent border-white/30'
          ]"
        >
          <span class="text-white text-sm font-bold">2</span>
        </div>
      </div>
      <p class="text-gray-400 text-xs mt-2">Step {{ currentStep }} of 2</p>
    </div>

    <!-- Scrollable Form Content -->
    <div class="flex-1 overflow-y-auto scrollbar-hide px-1">
      <!-- Alert Messages -->
      <div v-if="authError || hasValidationErrors" class="mb-4 space-y-3">
        <!-- Auth Error Display -->
        <div
          v-if="authError"
          class="p-4 bg-red-500/20 border border-red-500/50 rounded-lg flex items-start space-x-3"
        >
          <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div>
            <h4 class="text-red-400 font-medium text-sm">Authentication Error</h4>
            <p class="text-red-300 text-sm mt-1">{{ authError }}</p>
          </div>
        </div>

        <!-- Validation Errors Summary -->
        <div
          v-if="hasValidationErrors"
          class="p-4 bg-orange-500/20 border border-orange-500/50 rounded-lg flex items-start space-x-3"
        >
          <svg class="w-5 h-5 text-orange-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <h4 class="text-orange-400 font-medium text-sm">Please fix the following errors:</h4>
            <ul class="text-orange-300 text-sm mt-1 space-y-1">
              <li v-for="error in validationErrorMessages" :key="error.field">
                • {{ error.message }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <form class="space-y-4 md:space-y-5" @submit.prevent="handleSubmit">
        <!-- Step 1: Basic Information -->
        <div v-if="currentStep === 1" class="space-y-4 md:space-y-5">
          <!-- Name Fields Row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- First Name -->
            <div class="space-y-1">
              <label class="text-sm font-medium text-gray-200"
                >First Name</label
              >
              <input
                v-model="formData.first_name"
                type="text"
                placeholder="First Name"
                :class="[
                  'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                  hasError('first_name')
                    ? 'border-red-400 ring-2 ring-red-400/50'
                    : 'hover:border-white/30',
                ]"
              />
              <p v-if="hasError('first_name')" class="text-red-400 text-xs">
                {{ getErrorMessage("first_name") }}
              </p>
            </div>

            <!-- Last Name -->
            <div class="space-y-1">
              <label class="text-sm font-medium text-gray-200">Last Name</label>
              <input
                v-model="formData.last_name"
                type="text"
                placeholder="Last Name"
                :class="[
                  'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                  hasError('last_name')
                    ? 'border-red-400 ring-2 ring-red-400/50'
                    : 'hover:border-white/30',
                ]"
              />
              <p v-if="hasError('last_name')" class="text-red-400 text-xs">
                {{ getErrorMessage("last_name") }}
              </p>
            </div>
          </div>

          <!-- Username -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200">Username</label>
            <input
              v-model="formData.username"
              type="text"
              placeholder="Choose a username"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('username')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <p v-if="hasError('username')" class="text-red-400 text-xs">
              {{ getErrorMessage("username") }}
            </p>
          </div>

          <!-- Email -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200">Email</label>
            <input
              v-model="formData.email"
              type="email"
              placeholder="yourname@email.com"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('email')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <p v-if="hasError('email')" class="text-red-400 text-xs">
              {{ getErrorMessage("email") }}
            </p>
          </div>

          <!-- Gender -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-200">Gender</label>
            <div class="flex flex-row space-x-6">
              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                  v-model="formData.gender"
                  type="radio"
                  value="male"
                  class="hidden"
                />
                <div
                  :class="[
                    'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200',
                    formData.gender === 'male'
                      ? 'border-blue-400 bg-blue-400'
                      : 'border-white/30',
                  ]"
                >
                  <div
                    v-if="formData.gender === 'male'"
                    class="w-2 h-2 bg-white rounded-full"
                  ></div>
                </div>
                <span class="text-white">Male</span>
              </label>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                  v-model="formData.gender"
                  type="radio"
                  value="female"
                  class="hidden"
                />
                <div
                  :class="[
                    'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200',
                    formData.gender === 'female'
                      ? 'border-blue-400 bg-blue-400'
                      : 'border-white/30',
                  ]"
                >
                  <div
                    v-if="formData.gender === 'female'"
                    class="w-2 h-2 bg-white rounded-full"
                  ></div>
                </div>
                <span class="text-white">Female</span>
              </label>
            </div>
          </div>

          <!-- Date of Birth -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200"
              >Date of Birth</label
            >
            <input
              v-model="formData.date_of_birth"
              type="date"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('date_of_birth')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <p v-if="hasError('date_of_birth')" class="text-red-400 text-xs">
              {{ getErrorMessage("date_of_birth") }}
            </p>
          </div>

          <!-- Navigation Buttons for Step 1 -->
          <div class="flex justify-end pt-4">
            <button
              type="button"
              @click="nextStep"
              class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-2.5 px-6 rounded-xl font-medium transition-colors duration-200"
            >
              Next
            </button>
          </div>
        </div>

        <!-- Step 2: Additional Information -->
        <div v-if="currentStep === 2" class="space-y-4 md:space-y-5">
          <!-- City -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200">City</label>
            <input
              v-model="formData.city"
              type="text"
              placeholder="Dhaka"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('city')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <p v-if="hasError('city')" class="text-red-400 text-xs">
              {{ getErrorMessage("city") }}
            </p>
          </div>

          <!-- Country -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200">Country</label>
            <input
              v-model="formData.country"
              type="text"
              placeholder="Bangladesh"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('country')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <p v-if="hasError('country')" class="text-red-400 text-xs">
              {{ getErrorMessage("country") }}
            </p>
          </div>

          <!-- Role Selection (Subscription) -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-200"
              >Subscription</label
            >
            <div class="flex flex-wrap gap-2">
              <button
                v-for="role in availableRoles"
                :key="role.value"
                type="button"
                @click="formData.role = role.value"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  formData.role === role.value
                    ? 'bg-blue-600 text-white'
                    : 'bg-white/10 text-white hover:bg-white/20 border border-white/20',
                ]"
              >
                {{ role.label }}
              </button>
            </div>
            <p v-if="hasError('role')" class="text-red-400 text-xs">
              {{ getErrorMessage("role") }}
            </p>
          </div>

          <!-- Password -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200">Password</label>
            <input
              v-model="formData.password"
              type="password"
              placeholder="Create a password"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('password')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <p v-if="hasError('password')" class="text-red-400 text-xs">
              {{ getErrorMessage("password") }}
            </p>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200"
              >Confirm Password</label
            >
            <input
              v-model="formData.confirmPassword"
              type="password"
              placeholder="Confirm your password"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('confirmPassword')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
              ]"
            />
            <p v-if="hasError('confirmPassword')" class="text-red-400 text-xs">
              {{ getErrorMessage("confirmPassword") }}
            </p>
          </div>

          <!-- Navigation Buttons for Step 2 -->
          <div
            class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 pt-4"
          >
            <button
              type="button"
              @click="previousStep"
              class="flex-1 bg-white/10 backdrop-blur-sm border border-white/20 text-white py-2.5 px-4 rounded-xl font-medium hover:bg-white/20 transition-colors duration-200"
            >
              PREVIOUS
            </button>
            <button
              type="submit"
              :disabled="isLoading"
              class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-2.5 px-4 rounded-xl font-medium transition-colors duration-200 disabled:opacity-50"
            >
              {{ isLoading ? "Creating..." : "SUBMIT" }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, ref, computed } from "vue";

// Current step for pagination
const currentStep = ref(1);

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
    default: () => ({
      first_name: "",
      last_name: "",
      username: "",
      email: "",
      gender: "",
      date_of_birth: "",
      city: "",
      country: "",
      role: "student",
      password: "",
      confirmPassword: "",
    }),
  },
  authError: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["submit", "update:data"]);

const formData = reactive({
  first_name: props.initialData.first_name || "",
  last_name: props.initialData.last_name || "",
  username: props.initialData.username || "",
  email: props.initialData.email || "",
  gender: props.initialData.gender || "",
  date_of_birth: props.initialData.date_of_birth || "",
  city: props.initialData.city || "",
  country: props.initialData.country || "",
  role: props.initialData.role || "student",
  password: props.initialData.password || "",
  confirmPassword: props.initialData.confirmPassword || "",
});

// Available roles for registration (excluding admin)
const availableRoles = [
  { value: "student", label: "Student" },
  { value: "teacher", label: "Teacher" },
];

// Watch for changes and emit to parent
watch(
  formData,
  (newData) => {
    emit("update:data", { ...newData });
  },
  { deep: true }
);

// Navigation functions
const nextStep = () => {
  if (currentStep.value < 2) {
    currentStep.value++;
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const handleSubmit = () => {
  emit("submit", { ...formData });
};

const hasError = (field) => {
  return !!props.errors[field];
};

const getErrorMessage = (field) => {
  return props.errors[field] || "";
};

// Computed properties for alert system
const hasValidationErrors = computed(() => {
  return Object.keys(props.errors).length > 0;
});

const validationErrorMessages = computed(() => {
  const fieldNames = {
    first_name: 'First Name',
    last_name: 'Last Name',
    username: 'Username',
    email: 'Email',
    gender: 'Gender',
    date_of_birth: 'Date of Birth',
    city: 'City',
    country: 'Country',
    role: 'Subscription',
    password: 'Password',
    confirmPassword: 'Confirm Password'
  };

  return Object.entries(props.errors).map(([field, message]) => ({
    field,
    message: `${fieldNames[field] || field}: ${message}`
  }));
});
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
