<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEdit ? 'Edit Student' : 'Add New Student' }}
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Student Code *</label>
            <input v-model="form.student_code" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="{ 'border-red-300': errors.student_code }" />
            <p v-if="errors.student_code" class="text-red-500 text-xs mt-1">{{ errors.student_code[0] }}</p>
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

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parent/Guardian Name *</label>
            <input v-model="form.parent_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parent/Guardian Phone *</label>
            <input v-model="form.parent_phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
            <select v-model="form.current_class_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select a class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Enrollment Date *</label>
            <input v-model="form.enrollment_date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
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
              {{ isEdit ? 'Update Student' : 'Create Student' }}
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
  student: Object,
  classes: {
    type: Array,
    default: () => []
  },
  loading: Boolean
})

const emit = defineEmits(['close', 'submit'])

const isEdit = computed(() => !!props.student?.user_id)

const emptyForm = () => ({
  first_name: '',
  last_name: '',
  email: '',
  student_code: '',
  date_of_birth: '',
  gender: '',
  address: '',
  parent_name: '',
  parent_phone: '',
  current_class_id: '',
  enrollment_date: '',
  password: '',
  password_confirmation: ''
})

const form = ref(emptyForm())
const errors = ref({})

watch(() => props.student, (s) => {
  if (s) {
    form.value = {
      first_name: s.user?.first_name || '',
      last_name: s.user?.last_name || '',
      email: s.user?.email || '',
      student_code: s.student_code || '',
      date_of_birth: s.date_of_birth || '',
      gender: s.gender || '',
      address: s.address || '',
      parent_name: s.parent_name || '',
      parent_phone: s.parent_phone || '',
      current_class_id: s.current_class_id || '',
      enrollment_date: s.enrollment_date || '',
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
