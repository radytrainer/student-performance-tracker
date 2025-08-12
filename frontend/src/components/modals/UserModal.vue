<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEdit ? 'Edit User' : 'Add New User' }}
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Profile Picture -->
        <div class="flex justify-center mb-6">
          <div class="relative">
            <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
              <img
                v-if="previewImage"
                :src="previewImage"
                class="w-full h-full object-cover"
                alt="Profile preview"
              />
              <span v-else class="text-gray-400 text-2xl">
                {{ form.first_name ? form.first_name.charAt(0).toUpperCase() : '?' }}
              </span>
            </div>
            <label class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-2 cursor-pointer hover:bg-blue-700">
              <i class="fas fa-camera text-sm"></i>
              <input
                type="file"
                accept="image/*"
                @change="handleImageChange"
                class="hidden"
              />
            </label>
          </div>
        </div>

        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              First Name *
            </label>
            <input
              v-model="form.first_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-300': errors.first_name }"
            />
            <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">
              {{ errors.first_name[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Last Name *
            </label>
            <input
              v-model="form.last_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-300': errors.last_name }"
            />
            <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">
              {{ errors.last_name[0] }}
            </p>
          </div>
        </div>

        <!-- Account Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Username *
            </label>
            <input
              v-model="form.username"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-300': errors.username }"
            />
            <p v-if="errors.username" class="text-red-500 text-xs mt-1">
              {{ errors.username[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Email *
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-300': errors.email }"
            />
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">
              {{ errors.email[0] }}
            </p>
          </div>
        </div>

        <!-- Role Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Role *
          </label>
          <select
            v-model="form.role"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-300': errors.role }"
          >
            <option value="">Select a role</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
          </select>
          <p v-if="errors.role" class="text-red-500 text-xs mt-1">
            {{ errors.role[0] }}
          </p>
        </div>

        <!-- School Selection -->
        <div v-if="true">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            School *
          </label>
          <select
            v-model="form.school_id"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-300': errors.school_id }"
          >
            <option value="">Select a school</option>
            <option 
              v-for="school in schools" 
              :key="school.id" 
              :value="school.id"
            >
              {{ school.name }} ({{ school.code }})
            </option>
          </select>
          <p v-if="errors.school_id" class="text-red-500 text-xs mt-1">
            {{ errors.school_id[0] }}
          </p>
        </div>

        <!-- Password -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ isEdit ? 'New Password (leave blank to keep current)' : 'Password *' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!isEdit"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-300': errors.password }"
            />
            <p v-if="errors.password" class="text-red-500 text-xs mt-1">
              {{ errors.password[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ isEdit ? 'Confirm New Password' : 'Confirm Password *' }}
            </label>
            <input
              v-model="form.password_confirmation"
              type="password"
              :required="!isEdit && form.password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Status (only for edit) -->
        <div v-if="isEdit" class="flex items-center">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="is_active"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          />
          <label for="is_active" class="ml-2 text-sm text-gray-700">
            User is active
          </label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            :disabled="loading"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center">
              <i class="fas fa-spinner fa-spin mr-2"></i>
              {{ isEdit ? 'Updating...' : 'Creating...' }}
            </span>
            <span v-else>
              {{ isEdit ? 'Update User' : 'Create User' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { superAdminAPI } from '@/api/superAdmin'
import { adminAPI } from '@/api/admin'

const props = defineProps({
  show: Boolean,
  user: Object,
  loading: Boolean
})

const emit = defineEmits(['close', 'submit'])

const { isSuperAdmin } = useAuth()

const isEdit = computed(() => !!props.user?.id)

const form = ref({
  first_name: '',
  last_name: '',
  username: '',
  email: '',
  role: '',
  school_id: '',
  password: '',
  password_confirmation: '',
  is_active: true,
  profile_picture: null
})

const errors = ref({})
const previewImage = ref('')
const schools = ref([])

// Show school selection for super admin or when no school is pre-assigned
const showSchoolSelection = computed(() => {
  console.log('showSchoolSelection check:', {
    isSuperAdmin: isSuperAdmin.value,
    schoolsLength: schools.value.length,
    schools: schools.value
  })
  return isSuperAdmin.value || (schools.value.length > 0)
})

// Watch for user prop changes to populate form
watch(() => props.user, (newUser) => {
  if (newUser) {
    form.value = {
      first_name: newUser.first_name || '',
      last_name: newUser.last_name || '',
      username: newUser.username || '',
      email: newUser.email || '',
      role: newUser.role || '',
      school_id: newUser.school_id || '',
      password: '',
      password_confirmation: '',
      is_active: newUser.is_active ?? true,
      profile_picture: null
    }
    previewImage.value = newUser.profile_picture || ''
  } else {
    // Reset form for new user
    form.value = {
      first_name: '',
      last_name: '',
      username: '',
      email: '',
      role: '',
      school_id: '',
      password: '',
      password_confirmation: '',
      is_active: true,
      profile_picture: null
    }
    previewImage.value = ''
  }
  errors.value = {}
}, { immediate: true })

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.value.profile_picture = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      previewImage.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleSubmit = () => {
  errors.value = {}
  
  // Basic validation
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    errors.value.password = ['Passwords do not match']
    return
  }

  // Remove password_confirmation from submission data
  const submitData = { ...form.value }
  delete submitData.password_confirmation

  // Remove empty password for edits
  if (isEdit.value && !submitData.password) {
    delete submitData.password
  }

  emit('submit', submitData)
}

const setErrors = (newErrors) => {
  errors.value = newErrors
}

// Load schools based on user permissions
const loadSchools = async () => {
  try {
    const { user } = useAuth()
    console.log('loadSchools called')
    console.log('Current user:', user.value)
    console.log('isSuperAdmin computed:', isSuperAdmin.value)
    console.log('User is_super_admin field:', user.value?.is_super_admin)
    
    // Check both the computed property and direct field access
    const isSuper = isSuperAdmin.value || user.value?.is_super_admin
    console.log('Final isSuper check:', isSuper)
    
    if (isSuper) {
      // Super admin can see all schools from database
      console.log('Loading schools for super admin...')
      const response = await superAdminAPI.getSchools()
      console.log('Schools API response:', response.data)
      schools.value = response.data.data?.data || response.data.data || []
      console.log('Schools loaded from database:', schools.value)
    } else {
      // For testing: load schools even for regular users
      console.log('Not super admin, but loading schools for testing...')
      try {
        const response = await superAdminAPI.getSchools()
        schools.value = response.data.data?.data || response.data.data || []
        console.log('Schools loaded for testing:', schools.value)
      } catch (error) {
        // Minimal fallback if API completely fails
        schools.value = []
        console.log('API failed, no schools loaded')
      }
    }
  } catch (error) {
    console.error('Error loading schools:', error)
    schools.value = []
  }
}

// Load schools when component mounts or when show prop changes
watch(() => props.show, (newShow) => {
  if (newShow) {
    loadSchools()
  }
}, { immediate: true })

defineExpose({
  setErrors
})
</script>
