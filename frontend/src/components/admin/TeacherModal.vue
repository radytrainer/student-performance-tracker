<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ teacher?.user_id ? 'Edit Teacher' : 'Add New Teacher' }}
          </h3>
          <span v-if="teacher?.teacher_code" class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
            {{ teacher.teacher_code }}
          </span>
        </div>
        <div class="flex items-center space-x-3">
          <!-- Real-time status indicator -->
          <div v-if="isRealTimeConnected" class="flex items-center text-green-600">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
            <span class="text-xs">Live</span>
          </div>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'basic'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'basic' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Basic Information
          </button>
          <button
            @click="activeTab = 'professional'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'professional' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Professional Details
          </button>
          <button
            @click="activeTab = 'assignments'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'assignments' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Assignments
          </button>
          <button
            v-if="teacher?.user_id"
            @click="activeTab = 'analytics'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'analytics' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Analytics
          </button>
        </nav>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Basic Information Tab -->
        <div v-show="activeTab === 'basic'" class="space-y-6">
          <!-- Profile Picture -->
          <div class="flex items-center space-x-4">
            <div class="relative">
              <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-100">
                <img v-if="profileImagePreview || teacher?.user?.profile_picture" 
                     :src="profileImagePreview || resolveImage(teacher?.user?.profile_picture)" 
                     :alt="form.first_name + ' ' + form.last_name"
                     class="h-full w-full object-cover">
                <div v-else class="h-full w-full flex items-center justify-center text-gray-400">
                  <i class="fas fa-user text-2xl"></i>
                </div>
              </div>
              <button
                type="button"
                @click="$refs.fileInput?.click()"
                class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-1.5 hover:bg-blue-700"
              >
                <i class="fas fa-camera text-xs"></i>
              </button>
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleImageUpload"
              />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Profile Picture</p>
              <p class="text-xs text-gray-500">JPG, PNG up to 2MB</p>
            </div>
          </div>

          <!-- Personal Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                First Name *
              </label>
              <input
                v-model="form.first_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-300': errors.first_name }"
              />
              <p v-if="errors.first_name" class="text-red-600 text-xs mt-1">{{ errors.first_name }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Last Name *
              </label>
              <input
                v-model="form.last_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-300': errors.last_name }"
              />
              <p v-if="errors.last_name" class="text-red-600 text-xs mt-1">{{ errors.last_name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Email Address *
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-300': errors.email }"
              />
              <p v-if="errors.email" class="text-red-600 text-xs mt-1">{{ errors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Phone Number
              </label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Teacher Code
                <button type="button" @click="generateTeacherCode" class="text-blue-600 hover:text-blue-800 ml-2">
                  <i class="fas fa-sync-alt text-xs"></i> Generate
                </button>
              </label>
              <input
                v-model="form.teacher_code"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., TCH001"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>

          <!-- Password (for new teachers) -->
          <div v-if="!teacher?.user_id" class="border-t pt-6">
            <h4 class="text-md font-medium text-gray-900 mb-4">Account Credentials</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                <div class="relative">
                  <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    :required="!teacher?.user_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                    :class="{ 'border-red-300': errors.password }"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400"></i>
                  </button>
                </div>
                <p v-if="errors.password" class="text-red-600 text-xs mt-1">{{ errors.password }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  :required="!teacher?.user_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-300': errors.password_confirmation }"
                />
                <p v-if="errors.password_confirmation" class="text-red-600 text-xs mt-1">{{ errors.password_confirmation }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Professional Details Tab -->
        <div v-show="activeTab === 'professional'" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
              <select
                v-model="form.department_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Employment Type</label>
              <select
                v-model="form.employment_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select Type</option>
                <option value="full_time">Full Time</option>
                <option value="part_time">Part Time</option>
                <option value="contract">Contract</option>
                <option value="substitute">Substitute</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Qualification</label>
              <input
                v-model="form.qualification"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="e.g., M.Ed Mathematics"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Years of Experience</label>
              <input
                v-model="form.years_experience"
                type="number"
                min="0"
                max="50"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Join Date</label>
              <input
                v-model="form.join_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Salary (Optional)</label>
              <input
                v-model="form.salary"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bio / Notes</label>
            <textarea
              v-model="form.bio"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Additional information about the teacher..."
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Skills & Specializations</label>
            <div class="flex flex-wrap gap-2 mb-2">
              <span
                v-for="(skill, index) in form.skills"
                :key="index"
                class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm flex items-center"
              >
                {{ skill }}
                <button
                  type="button"
                  @click="removeSkill(index)"
                  class="ml-1 text-blue-600 hover:text-blue-800"
                >
                  <i class="fas fa-times text-xs"></i>
                </button>
              </span>
            </div>
            <div class="flex">
              <input
                v-model="newSkill"
                @keyup.enter="addSkill"
                type="text"
                placeholder="Add a skill and press Enter"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <button
                type="button"
                @click="addSkill"
                class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700"
              >
                Add
              </button>
            </div>
          </div>
        </div>

        <!-- Assignments Tab -->
        <div v-show="activeTab === 'assignments'" class="space-y-6">
          <!-- Subject Assignment -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Teaching Subjects</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-4">
              <label v-for="subject in subjects" :key="subject.id" class="flex items-center space-x-2 text-sm">
                <input
                  type="checkbox"
                  :value="subject.id"
                  v-model="form.subject_ids"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span>{{ subject.subject_name }}</span>
              </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Selected: {{ form.subject_ids.length }} subjects
            </p>
          </div>

          <!-- Class Assignment -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Assigned Classes</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-4">
              <label v-for="classItem in availableClasses" :key="classItem.id" class="flex items-center space-x-2 text-sm">
                <input
                  type="checkbox"
                  :value="classItem.id"
                  v-model="form.class_ids"
                  class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                />
                <span>{{ classItem.name }}</span>
              </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Selected: {{ form.class_ids.length }} classes
            </p>
          </div>
        </div>

        <!-- Analytics Tab (only for existing teachers) -->
        <div v-show="activeTab === 'analytics' && teacher?.user_id" class="space-y-6">
          <div v-if="analyticsLoading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          
          <div v-else-if="analytics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Statistics Cards -->
            <div class="bg-blue-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-blue-900">Total Classes</h4>
              <p class="text-2xl font-bold text-blue-600">{{ analytics.total_classes || 0 }}</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-green-900">Total Students</h4>
              <p class="text-2xl font-bold text-green-600">{{ analytics.total_students || 0 }}</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-purple-900">Subjects Teaching</h4>
              <p class="text-2xl font-bold text-purple-600">{{ analytics.total_subjects || 0 }}</p>
            </div>
            
            <div class="bg-orange-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-orange-900">Average Grade</h4>
              <p class="text-2xl font-bold text-orange-600">{{ analytics.average_grade || 'N/A' }}</p>
            </div>
          </div>

          <!-- Performance Chart -->
          <div v-if="analytics?.performance_data" class="bg-white border rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Student Performance Trends</h4>
            <!-- Chart would be implemented here with a charting library -->
            <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
              <p class="text-gray-500">Performance chart visualization would go here</p>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between border-t pt-6">
          <div class="flex items-center space-x-2 text-sm text-gray-500">
            <i class="fas fa-info-circle"></i>
            <span>Fields marked with * are required</span>
          </div>
          
          <div class="flex items-center space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            
            <!-- Export/Import Actions (for existing teachers) -->
            <div v-if="teacher?.user_id" class="flex items-center space-x-2">
              <button
                type="button"
                @click="exportTeacherData"
                class="px-3 py-2 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors text-sm"
              >
                <i class="fas fa-download mr-1"></i>
                Export
              </button>
            </div>
            
            <button
              type="submit"
              :disabled="loading || !isFormValid"
              class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
              {{ teacher?.user_id ? 'Update Teacher' : 'Create Teacher' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { adminAPI } from '@/api/admin'
import { resolveImageUrl } from '@/utils/imageUrl'

const props = defineProps({
  show: Boolean,
  teacher: Object,
  subjects: Array,
  loading: Boolean,
  departments: Array,
  availableClasses: Array
})

const emit = defineEmits(['close', 'submit', 'refresh'])

// State
const activeTab = ref('basic')
const showPassword = ref(false)
const profileImagePreview = ref(null)
const newSkill = ref('')
const errors = ref({})
const analyticsLoading = ref(false)
const analytics = ref(null)
const isRealTimeConnected = ref(false)

// Form data
const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  teacher_code: '',
  phone: '',
  department_id: '',
  status: 'active',
  employment_type: '',
  qualification: '',
  years_experience: 0,
  join_date: '',
  salary: '',
  bio: '',
  skills: [],
  subject_ids: [],
  class_ids: [],
  password: '',
  password_confirmation: '',
  profile_picture: null
})

// Computed properties
const isFormValid = computed(() => {
  const basicValid = form.first_name && form.last_name && form.email
  
  if (!props.teacher?.user_id) {
    // New teacher - password required
    return basicValid && form.password && form.password === form.password_confirmation
  }
  
  // Editing existing teacher
  return basicValid
})

// Methods
const closeModal = () => {
  emit('close')
  resetForm()
}

const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (Array.isArray(form[key])) {
      form[key] = []
    } else if (key === 'status') {
      form[key] = 'active'
    } else if (key === 'years_experience') {
      form[key] = 0
    } else {
      form[key] = ''
    }
  })
  
  profileImagePreview.value = null
  errors.value = {}
  activeTab.value = 'basic'
}

const generateTeacherCode = () => {
  const timestamp = Date.now().toString().slice(-4)
  const random = Math.floor(Math.random() * 100).toString().padStart(2, '0')
  form.teacher_code = `TCH${timestamp}${random}`
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      errors.value.profile_picture = 'Image size must be less than 2MB'
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      profileImagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    form.profile_picture = file
  }
}

const addSkill = () => {
  if (newSkill.value.trim() && !form.skills.includes(newSkill.value.trim())) {
    form.skills.push(newSkill.value.trim())
    newSkill.value = ''
  }
}

const removeSkill = (index) => {
  form.skills.splice(index, 1)
}

const loadAnalytics = async () => {
  if (!props.teacher?.user_id) return
  
  try {
    analyticsLoading.value = true
    const response = await adminAPI.getTeacherAnalytics(props.teacher.user_id)
    analytics.value = response.data
  } catch (error) {
    console.error('Failed to load teacher analytics:', error)
  } finally {
    analyticsLoading.value = false
  }
}

const exportTeacherData = async () => {
  try {
    const response = await adminAPI.exportTeachers('csv', {
      teacher_id: props.teacher.user_id
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.download = `teacher-${props.teacher.teacher_code || props.teacher.user_id}.csv`
    link.click()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Failed to export teacher data:', error)
  }
}

const submitForm = () => {
  if (!isFormValid.value) return
  
  // Clear previous errors
  errors.value = {}
  
  // Validation
  if (form.password && form.password !== form.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match'
    return
  }
  
  const teacherData = {
    first_name: form.first_name,
    last_name: form.last_name,
    email: form.email,
    teacher_code: form.teacher_code,
    phone: form.phone,
    department_id: form.department_id || null,
    status: form.status,
    employment_type: form.employment_type,
    qualification: form.qualification,
    years_experience: form.years_experience || null,
    join_date: form.join_date || null,
    salary: form.salary || null,
    bio: form.bio,
    skills: form.skills,
    subject_ids: form.subject_ids,
    class_ids: form.class_ids,
    role: 'teacher'
  }

  // Add password for new teachers
  if (!props.teacher?.user_id) {
    teacherData.password = form.password
    teacherData.password_confirmation = form.password_confirmation
  }

  // Add profile picture if uploaded
  if (form.profile_picture) {
    teacherData.profile_picture = form.profile_picture
  }

  emit('submit', teacherData)
}

const resolveImage = (imagePath) => {
  return resolveImageUrl(imagePath)
}

// Watch for prop changes
watch(() => props.show, (newVal) => {
  if (newVal) {
    if (props.teacher?.user_id) {
      // Editing existing teacher
      form.first_name = props.teacher.user?.first_name || ''
      form.last_name = props.teacher.user?.last_name || ''
      form.email = props.teacher.user?.email || ''
      form.teacher_code = props.teacher.teacher_code || ''
      form.phone = props.teacher.user?.phone || ''
      form.department_id = props.teacher.department_id || ''
      form.status = props.teacher.status || 'active'
      form.employment_type = props.teacher.employment_type || ''
      form.qualification = props.teacher.qualification || ''
      form.years_experience = props.teacher.years_experience || 0
      form.join_date = props.teacher.join_date || ''
      form.salary = props.teacher.salary || ''
      form.bio = props.teacher.bio || ''
      form.skills = props.teacher.skills || []
      form.subject_ids = props.teacher.subjects?.map(s => s.id) || []
      form.class_ids = props.teacher.classes?.map(c => c.id) || []
      form.password = ''
      form.password_confirmation = ''
      
      // Load analytics for existing teacher
      if (activeTab.value === 'analytics') {
        loadAnalytics()
      }
    } else {
      // Creating new teacher
      resetForm()
    }
  }
})

// Watch for tab changes
watch(activeTab, (newTab) => {
  if (newTab === 'analytics' && props.teacher?.user_id && !analytics.value) {
    loadAnalytics()
  }
})

// Simulate real-time connection (would be actual WebSocket in production)
onMounted(() => {
  setTimeout(() => {
    isRealTimeConnected.value = true
  }, 1000)
})
</script>
