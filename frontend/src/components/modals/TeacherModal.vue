<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEdit ? 'Edit Teacher' : 'Add New Teacher' }}
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
            <input v-model="form.first_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300': errors.first_name }" />
            <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
            <input v-model="form.last_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300': errors.last_name }" />
            <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">{{ errors.last_name[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input v-model="form.email" type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300': errors.email }" />
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teacher Code *</label>
            <input v-model="form.teacher_code" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300': errors.teacher_code }" />
            <p v-if="errors.teacher_code" class="text-red-500 text-xs mt-1">{{ errors.teacher_code[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
            <input v-model="form.date_of_birth" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
            <select v-model="form.gender" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
            <input v-model="form.phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
            <select v-model="form.department" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select Department</option>
              <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Name</label>
            <input v-model="form.emergency_contact_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact Phone</label>
            <input v-model="form.emergency_contact_phone" type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
            <input v-model="form.hire_date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Qualification</label>
            <input v-model="form.qualification" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Bachelor's in Education" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" v-if="!isEdit">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
            <input v-model="form.password" type="password" :required="!isEdit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300': errors.password }" />
            <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
            <input v-model="form.password_confirmation" type="password" :required="!isEdit && form.password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t">
          <button type="button" @click="$emit('close')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" :disabled="loading">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50" :disabled="loading">
            <span v-if="loading" class="flex items-center">
              <i class="fas fa-spinner fa-spin mr-2"></i>
              {{ isEdit ? 'Updating...' : 'Creating...' }}
            </span>
            <span v-else>
              {{ isEdit ? 'Update Teacher' : 'Create Teacher' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  teacher: Object,
  departments: {
    type: Array,
    default: () => ['Mathematics', 'Science', 'English', 'History', 'Physical Education', 'Arts', 'Music', 'Computer Science', 'Languages', 'Other']
  },
  loading: Boolean
})

const emit = defineEmits(['close', 'submit'])

const isEdit = computed(() => !!props.teacher?.user_id)

const emptyForm = () => ({
  first_name: '',
  last_name: '',
  email: '',
  teacher_code: '',
  date_of_birth: '',
  gender: '',
  phone: '',
  department: '',
  address: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  hire_date: '',
  qualification: '',
  password: '',
  password_confirmation: ''
})

const form = ref(emptyForm())
const errors = ref({})

watch(() => props.teacher, (t) => {
  if (t) {
    form.value = {
      first_name: t.user?.first_name || '',
      last_name: t.user?.last_name || '',
      email: t.user?.email || '',
      teacher_code: t.teacher_code || '',
      date_of_birth: t.date_of_birth || '',
      gender: t.gender || '',
      phone: t.phone || '',
      department: t.department || '',
      address: t.address || '',
      emergency_contact_name: t.emergency_contact_name || '',
      emergency_contact_phone: t.emergency_contact_phone || '',
      hire_date: t.hire_date || '',
      qualification: t.qualification || '',
      password: '',
      password_confirmation: ''
    }
  } else {
    form.value = emptyForm()
  }
  errors.value = {}
}, { immediate: true })

const handleSubmit = () => {
  errors.value = {}
  if (!isEdit.value) {
    if (form.value.password && form.value.password !== form.value.password_confirmation) {
      errors.value.password = ['Passwords do not match']
      return
    }
  }
  const submitData = { ...form.value }
  if (isEdit.value) {
    delete submitData.password
    delete submitData.password_confirmation
  }
  emit('submit', submitData)
}
</script>
