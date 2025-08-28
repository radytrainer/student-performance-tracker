<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal Header -->
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ teacher?.user_id ? 'Edit Teacher' : 'Add New Teacher' }}
        </h3>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Teacher Form -->
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Personal Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
            <input
              v-model="form.first_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
            <input
              v-model="form.last_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Teacher Code</label>
            <input
              v-model="form.teacher_code"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., TCH001"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
            <input
              v-model="form.phone"
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Professional Information -->
        <div class="border-t pt-6">
          <h4 class="text-md font-medium text-gray-900 mb-4">Professional Information</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              <label class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Teaching Subjects</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3">
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
          </div>
        </div>

        <!-- Password (for new teachers) -->
        <div v-if="!teacher?.user_id" class="border-t pt-6">
          <h4 class="text-md font-medium text-gray-900 mb-4">Account Credentials</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
              <input
                v-model="form.password"
                type="password"
                :required="!teacher?.user_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                :required="!teacher?.user_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end space-x-3 border-t pt-6">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !isFormValid"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
            {{ teacher?.user_id ? 'Update Teacher' : 'Create Teacher' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  teacher: Object,
  subjects: Array,
  loading: Boolean
})

const emit = defineEmits(['close', 'submit'])

// Mock departments for now
const departments = ref([
  { id: 1, name: 'Mathematics' },
  { id: 2, name: 'Science' },
  { id: 3, name: 'English' },
  { id: 4, name: 'History' },
  { id: 5, name: 'Physical Education' },
  { id: 6, name: 'Arts' }
])

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  teacher_code: '',
  phone: '',
  department_id: '',
  status: 'active',
  subject_ids: [],
  password: '',
  password_confirmation: ''
})

const isFormValid = computed(() => {
  const basicValid = form.first_name && form.last_name && form.email
  
  if (!props.teacher?.user_id) {
    // New teacher - password required
    return basicValid && form.password && form.password === form.password_confirmation
  }
  
  // Editing existing teacher
  return basicValid
})

const closeModal = () => {
  emit('close')
}

const submitForm = () => {
  if (!isFormValid.value) return
  
  const teacherData = {
    first_name: form.first_name,
    last_name: form.last_name,
    email: form.email,
    teacher_code: form.teacher_code,
    phone: form.phone,
    department_id: form.department_id || null,
    status: form.status,
    subject_ids: form.subject_ids,
    role: 'teacher'
  }

  // Add password for new teachers
  if (!props.teacher?.user_id) {
    teacherData.password = form.password
    teacherData.password_confirmation = form.password_confirmation
  }

  emit('submit', teacherData)
}

// Reset form when modal opens/closes
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
      form.subject_ids = props.teacher.subjects?.map(s => s.id) || []
      form.password = ''
      form.password_confirmation = ''
    } else {
      // Creating new teacher
      Object.keys(form).forEach(key => {
        if (Array.isArray(form[key])) {
          form[key] = []
        } else {
          form[key] = key === 'status' ? 'active' : ''
        }
      })
    }
  }
})
</script>
