<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Profile</h1>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="p-6">
        <div class="flex items-center space-x-6 mb-6">
          <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2a4 4 0 100 8 4 4 0 000-8zM4 14a6 6 0 1112 0v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ user.name }}</h2>
            <p class="text-gray-600">{{ user.email }}</p>
            <span class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full mt-1">
              {{ user.role }}
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input 
                  v-model="user.name" 
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  :disabled="!editing"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                  v-model="user.email" 
                  type="email" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  :disabled="!editing"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input 
                  v-model="user.phone" 
                  type="tel" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  :disabled="!editing"
                />
              </div>
            </div>
          </div>

          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Settings</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <input 
                  :value="user.role" 
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50"
                  disabled
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Member Since</label>
                <input 
                  :value="formatDate(user.createdAt)" 
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50"
                  disabled
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Last Login</label>
                <input 
                  :value="formatDate(user.lastLogin)" 
                  type="text" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50"
                  disabled
                />
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
          <button 
            v-if="!editing"
            @click="editing = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Edit Profile
          </button>
          <template v-else>
            <button 
              @click="cancelEdit"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
            >
              Cancel
            </button>
            <button 
              @click="saveProfile"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
            >
              Save Changes
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const editing = ref(false)
const originalUser = ref({})

const user = reactive({
  name: '',
  email: '',
  phone: '',
  role: '',
  createdAt: '',
  lastLogin: ''
})

onMounted(async () => {
  await loadProfile()
})

const loadProfile = async () => {
  try {
    const profile = authStore.user
    Object.assign(user, profile)
    originalUser.value = { ...profile }
  } catch (error) {
    console.error('Failed to load profile:', error)
  }
}

const saveProfile = async () => {
  try {
    // TODO: Implement profile update functionality
    editing.value = false
    originalUser.value = { ...user }
  } catch (error) {
    console.error('Failed to save profile:', error)
  }
}

const cancelEdit = () => {
  Object.assign(user, originalUser.value)
  editing.value = false
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}
</script>
