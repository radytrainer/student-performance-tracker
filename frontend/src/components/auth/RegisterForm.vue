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
            getStep1ValidationState(),
          ]"
        >
          <svg
            v-if="isStep1Completed && currentStep > 1"
            class="w-4 h-4 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="3"
              d="M5 13l4 4L19 7"
            ></path>
          </svg>
          <span v-else class="text-white text-sm font-bold">1</span>
        </div>

        <!-- Connecting line -->
        <div
          :class="[
            'w-12 h-0.5 transition-all duration-300',
            isStep1Completed && currentStep === 2
              ? 'bg-blue-500'
              : 'bg-white/30',
          ]"
        ></div>

        <!-- Step 2 -->
        <div
          :class="[
            'w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 border-2',
            currentStep === 2 && isStep1Completed
              ? 'bg-blue-500 border-blue-500'
              : 'bg-transparent border-white/30',
          ]"
        >
          <span class="text-white text-sm font-bold">2</span>
        </div>
      </div>
      <p class="text-gray-400 text-xs mt-2">Step {{ currentStep }} of 2</p>
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
          <h4 class="text-red-400 font-medium text-sm">Registration Error</h4>
          <p class="text-red-300 text-sm mt-1">{{ authError }}</p>
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

          <!-- Step 1 Validation Error Message -->
          <div
            v-if="attemptedNextStep && !validateStep1()"
            class="p-3 bg-red-500/20 border border-red-500/50 rounded-lg flex items-start space-x-2"
          >
            <svg
              class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
            <div>
              <h4 class="text-red-400 font-medium text-sm">
                Please complete all required fields
              </h4>
              <p class="text-red-300 text-sm mt-1">
                Fill in all the required information before proceeding to the
                next step.
              </p>
            </div>
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

          <!-- School Selection -->
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-200"
              >School <span class="text-red-400">*</span></label
            >
            <select
              v-model="formData.school"
              :disabled="isLoadingSchools"
              :class="[
                'w-full px-3 py-2.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all duration-200',
                hasError('school')
                  ? 'border-red-400 ring-2 ring-red-400/50'
                  : 'hover:border-white/30',
                isLoadingSchools ? 'opacity-50 cursor-not-allowed' : '',
              ]"
            >
              <option value="" disabled class="bg-gray-800 text-gray-300">
                {{
                  isLoadingSchools ? "Loading schools..." : "Select your school"
                }}
              </option>
              <option
                v-for="school in availableSchools"
                :key="school.id"
                :value="school.id"
                class="bg-gray-800 text-white"
              >
                {{ school.name }}
              </option>
            </select>
            <p v-if="hasError('school')" class="text-red-400 text-xs">
              {{ getErrorMessage("school") }}
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
import { reactive, watch, ref, computed, onMounted } from "vue";

// Current step for pagination
const currentStep = ref(1);

// Step validation states
const stepValidationErrors = ref({});
const attemptedNextStep = ref(false);

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
      school: "",
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
  school: props.initialData.school || "",
  role: props.initialData.role || "student",
  password: props.initialData.password || "",
  confirmPassword: props.initialData.confirmPassword || "",
});

// Available roles for registration (excluding admin)
const availableRoles = [
  { value: "student", label: "Student" },
  { value: "teacher", label: "Teacher" },
];

// Available schools for registration (will be loaded from API)
const availableSchools = ref([]);
const isLoadingSchools = ref(false);

// Fetch schools from API
const fetchSchools = async () => {
  isLoadingSchools.value = true;
  try {
    // Use the public schools API for registration
    const response = await fetch("/api/schools", {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    if (response.ok) {
      const result = await response.json();
      const schools = result.data || [];
      availableSchools.value = schools;
    } else {
      // Fallback to real schools from our database + Cambodia schools
      availableSchools.value = [
        // Real schools from our system (will be populated first from API)
        { id: 1, name: "Lincoln High School" },
        { id: 2, name: "Washington Elementary" },
        { id: 3, name: "Roosevelt Academy" },
        // Cambodia schools as additional options
        { id: "101", name: "Passerelles Numeriques Cambodia (PNC)" },
        { id: "102", name: "Pour un Sourire d'Enfant (PSE)" },
        { id: "103", name: "Royal University of Phnom Penh (RUPP)" },
        { id: "104", name: "Institute of Technology of Cambodia (ITC)" },
        { id: "105", name: "American University of Phnom Penh (AUPP)" },
        { id: "106", name: "Western University (Cambodia)" },
        { id: "107", name: "Asia Euro University" },
        { id: "108", name: "Norton University" },
      ];
    }
  } catch (error) {
    console.error("Failed to fetch schools:", error);
    // Fallback to our real schools + Cambodia schools on error
    availableSchools.value = [
      // Real schools from our system
      { id: 1, name: "Lincoln High School" },
      { id: 2, name: "Washington Elementary" },
      { id: 3, name: "Roosevelt Academy" },
      // Cambodia schools as additional options
      { id: "101", name: "Passerelles Numeriques Cambodia (PNC)" },
      { id: "102", name: "Pour un Sourire d'Enfant (PSE)" },
      { id: "103", name: "Royal University of Phnom Penh (RUPP)" },
      { id: "104", name: "Institute of Technology of Cambodia (ITC)" },
      { id: "105", name: "American University of Phnom Penh (AUPP)" },
      { id: "106", name: "Western University (Cambodia)" },
      { id: "107", name: "Asia Euro University" },
      { id: "108", name: "Norton University" },
    ];
  } finally {
    isLoadingSchools.value = false;
  }
};

// Fetch schools when component mounts
onMounted(() => {
  fetchSchools();
});

// Watch for changes and emit to parent
watch(
  formData,
  (newData) => {
    emit("update:data", { ...newData });
  },
  { deep: true }
);

// Step validation functions
const validateStep1 = () => {
  const errors = {};

  if (!formData.first_name.trim()) {
    errors.first_name = "First name is required";
  }

  if (!formData.last_name.trim()) {
    errors.last_name = "Last name is required";
  }

  if (!formData.username.trim()) {
    errors.username = "Username is required";
  }

  if (!formData.email.trim()) {
    errors.email = "Email is required";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
    errors.email = "Please enter a valid email address";
  }

  if (!formData.gender) {
    errors.gender = "Please select your gender";
  }

  if (!formData.date_of_birth) {
    errors.date_of_birth = "Date of birth is required";
  }

  stepValidationErrors.value = errors;
  return Object.keys(errors).length === 0;
};

const isStep1Completed = computed(() => {
  return validateStep1();
});

const getStep1ValidationState = () => {
  if (attemptedNextStep.value && !validateStep1()) {
    return "bg-red-500 border-red-500"; // Red for validation errors
  }
  if (currentStep.value >= 1) {
    if (isStep1Completed.value && currentStep.value > 1) {
      return "bg-green-500 border-green-500"; // Green for completed
    }
    return "bg-blue-500 border-blue-500"; // Blue for current/active
  }
  return "bg-transparent border-white/30"; // Default inactive
};

// Navigation functions
const nextStep = () => {
  attemptedNextStep.value = true;

  if (currentStep.value === 1) {
    if (!validateStep1()) {
      // Validation failed, show error message
      return;
    }
  }

  if (currentStep.value < 2) {
    currentStep.value++;
    attemptedNextStep.value = false; // Reset for next step
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
    attemptedNextStep.value = false; // Reset validation attempt
  }
};

const handleSubmit = () => {
  emit("submit", { ...formData });
};

const hasError = (field) => {
  return !!props.errors[field] || !!stepValidationErrors.value[field];
};

const getErrorMessage = (field) => {
  const error = props.errors[field] || stepValidationErrors.value[field];
  if (!error) return "";

  // Define user-friendly error messages
  const friendlyMessages = {
    // Email errors
    email: {
      required: "Email is required",
      invalid: "Please enter a valid email address",
      taken: "This email is already registered. Try logging in instead.",
      not_found:
        "This email is not registered yet. Please check your email or create a new account.",
    },
    // Password errors
    password: {
      required: "Password is required",
      too_short: "Password must be at least 8 characters long",
      weak: "Password must contain letters and numbers",
      incorrect: "Incorrect password",
    },
    confirmPassword: {
      required: "Please confirm your password",
      no_match: "Passwords do not match",
    },
    // Other field errors
    first_name: {
      required: "First name is required",
      invalid: "Please enter a valid first name",
    },
    last_name: {
      required: "Last name is required",
      invalid: "Please enter a valid last name",
    },
    username: {
      required: "Username is required",
      taken: "This username is already taken",
      invalid: "Username can only contain letters, numbers, and underscores",
    },
    gender: {
      required: "Please select your gender",
    },
    date_of_birth: {
      required: "Date of birth is required",
      invalid: "Please enter a valid date",
    },
    city: {
      required: "City is required",
    },
    country: {
      required: "Country is required",
    },
    school: {
      required: "Please select your school",
      invalid: "Please select a valid school from the list",
    },
    role: {
      required: "Please select your subscription type",
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
