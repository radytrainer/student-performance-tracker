<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">School Details</h1>
        <p class="text-gray-600">Code: {{ school?.code }}</p>
      </div>
      <button @click="$router.back()" class="px-4 py-2 bg-gray-200 rounded">Back</button>
    </div>

    <div v-if="!school" class="py-12 text-center text-gray-500">Loading...</div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Overview</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Name:</span> {{ school.name }}</div>
            <div><span class="text-gray-500">Email:</span> {{ school.email || '—' }}</div>
            <div><span class="text-gray-500">Status:</span> {{ school.status }}</div>
            <div><span class="text-gray-500">Users:</span> {{ school.users_count || 0 }}</div>
            <div><span class="text-gray-500">Admins:</span> {{ school.admins_count || 0 }}</div>
          </div>
        </div>

        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Sub Admins</h2>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-xs text-gray-500">
              <tr>
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in subAdmins" :key="u.id" class="border-t">
                <td class="px-4 py-2">{{ u.first_name }} {{ u.last_name }}</td>
                <td class="px-4 py-2">{{ u.email }}</td>
                <td class="px-4 py-2">{{ u.is_active ? 'Active' : 'Inactive' }}</td>
              </tr>
              <tr v-if="subAdmins.length===0"><td colspan="3" class="px-4 py-6 text-center text-gray-500">No sub-admins</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="space-y-6">
        <div class="bg-white border rounded p-4">
          <h2 class="text-lg font-semibold mb-3">Actions</h2>
          <button @click="openCreateSubAdmin" class="w-full px-4 py-2 bg-green-600 text-white rounded">Add Sub Admin</button>
        </div>
      </div>
    </div>

    <div v-if="showSubAdminModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Add Sub Admin</h3>
          <button @click="closeSubAdminModal" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"/></button>
        </div>
        <form @submit.prevent="createSubAdmin" class="space-y-3">
          <input v-model="subAdminForm.username" placeholder="Username" class="w-full px-3 py-2 border rounded" required />
          <input v-model="subAdminForm.email" placeholder="Email" type="email" class="w-full px-3 py-2 border rounded" required />
          <div class="grid grid-cols-2 gap-3">
            <input v-model="subAdminForm.first_name" placeholder="First name" class="px-3 py-2 border rounded" required />
            <input v-model="subAdminForm.last_name" placeholder="Last name" class="px-3 py-2 border rounded" required />
          </div>
          <input v-model="subAdminForm.password" placeholder="Password" type="password" class="w-full px-3 py-2 border rounded" required minlength="6" />
          <div class="flex justify-end space-x-2">
            <button type="button" @click="closeSubAdminModal" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { superAdminAPI } from '@/api/superAdmin'
import { useRoute } from 'vue-router'

const route = useRoute()
const schoolId = route.params.id
const school = ref(null)
const subAdmins = ref([])
const showSubAdminModal = ref(false)
const subAdminForm = ref({ username: '', email: '', first_name: '', last_name: '', password: '' })

const load = async () => {
  const s = await superAdminAPI.getSchool(schoolId)
  school.value = s.data?.data || s.data
  const sa = await superAdminAPI.getSubAdmins(schoolId)
  subAdmins.value = sa.data?.data || sa.data || []
}

const openCreateSubAdmin = () => { showSubAdminModal.value = true }
const closeSubAdminModal = () => { showSubAdminModal.value = false }
const createSubAdmin = async () => {
  await superAdminAPI.createSubAdmin(schoolId, subAdminForm.value)
  closeSubAdminModal()
  await load()
}

onMounted(load)
</script>
