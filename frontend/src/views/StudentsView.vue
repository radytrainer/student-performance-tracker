<template>
  <div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

      <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">Students</h1>
          <p class="text-sm sm:text-base text-gray-600">Manage and view all student profiles</p>
        </div>
        <button
          @click="showAddModal = true"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 shadow-sm"
        >
          <Plus class="w-5 h-5 mr-2" />
          Add Student
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search students by name or email..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            />
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
          </div>
          <select
            v-model="selectedRole"
            class="px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          >
            <option value="">All Roles</option>
            <option value="Student">Student</option>
            <option value="Graduate">Graduate</option>
            <option value="Alumni">Alumni</option>
          </select>
          <select
            v-model="sortBy"
            class="px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          >
            <option value="name">Sort by Name</option>
            <option value="email">Sort by Email</option>
            <option value="createdAt">Sort by Date</option>
          </select>
          <button
            @click="clearFilters"
            class="px-4 py-3 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
            title="Clear filters"
          >
            <RotateCcw class="w-5 h-5 text-gray-600" />
          </button>
        </div>
      </div>

      <!-- Server pagination controls -->
      <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-600">Page {{ page }} of {{ totalPages }}</div>
        <div class="space-x-2">
          <button @click="prevPage" :disabled="page <= 1" class="px-3 py-1 border rounded-md disabled:opacity-50">Prev</button>
          <button @click="nextPage" :disabled="page >= totalPages" class="px-3 py-1 border rounded-md disabled:opacity-50">Next</button>
        </div>
      </div>

      <!-- Student cards grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="student in filteredStudents"
          :key="student.id"
          class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 cursor-pointer group border border-gray-100"
          @click="openDetails(student)"
        >
          <!-- Header -->
          <div class="relative bg-blue-600 p-6 text-white">
            <div class="flex items-center space-x-4">
              <div
                class="w-16 h-16 rounded-full overflow-hidden bg-white/20 backdrop-blur-sm border-2 border-white/30 flex-shrink-0 flex items-center justify-center"
              >
                <img
                  v-if="student.profileImage"
                  :src="getImageUrl(student.profileImage)"
                  :alt="student.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                  loading="lazy"
                />
                <span v-else class="text-xl font-bold text-white">
                  {{ getInitials(student.name) }}
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold truncate">{{ student.name }}</h3>
                <p class="text-blue-100 text-sm truncate">{{ student.email }}</p>
              </div>
            </div>
            <div class="absolute top-4 right-4">
              <span :class="getStatusBadgeClass(student.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ student.status }}
              </span>
            </div>
          </div>

          <!-- Body -->
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex items-center text-sm text-gray-600">
                <Briefcase class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" />
                <span class="truncate">{{ student.role }}</span>
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <Phone class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" />
                <span class="truncate">{{ student.phone || 'No phone' }}</span>
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <Calendar class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" />
                <span class="truncate">Joined {{ formatDate(student.createdAt) }}</span>
              </div>
            </div>

            <div class="flex gap-2 mt-4">
              <button
                @click.stop="startEdit(student)"
                class="px-3 py-2 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 transition-colors duration-200"
                :title="'Edit ' + student.name"
              >
                <Edit class="w-4 h-4" />
              </button>
              <button
                @click.stop="askDelete(student)"
                class="px-3 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors duration-200"
                :title="'Delete ' + student.name"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="filteredStudents.length === 0 && !loading" class="text-center py-12">
        <Users class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
        <p class="text-gray-500 mb-4">Try adjusting your search or filter criteria.</p>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Loading students...</p>
      </div>
    </div>

    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click.self="showAddModal = false"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-900">Add New Student</h2>
          <button
            @click="showAddModal = false"
            class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
          >
            <X class="w-6 h-6" />
          </button>
        </div>
        <form @submit.prevent="addStudent" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input
              v-model="newStudent.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter student name"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="newStudent.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter email address"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input
              v-model="newStudent.phone"
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter phone number"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select
              v-model="newStudent.role"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select role</option>
              <option value="Student">Student</option>
              <option value="Graduate">Graduate</option>
              <option value="Alumni">Alumni</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="newStudent.status"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select status</option>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="showAddModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
            >
              Add Student
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Student Modal -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click.self="closeEdit"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-xl font-bold text-gray-900">Edit Student</h2>
          <button
            @click="closeEdit"
            class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
          >
            <X class="w-6 h-6" />
          </button>
        </div>
        <form @submit.prevent="saveEdit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input
              v-model="editForm.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter student name"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="editForm.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter email address"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input
              v-model="editForm.phone"
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter phone number"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select
              v-model="editForm.role"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="Student">Student</option>
              <option value="Graduate">Graduate</option>
              <option value="Alumni">Alumni</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="editForm.status"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeEdit"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
            >
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

        <div
          v-if="showDetailsModal && selectedStudent"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
          @click.self="showDetailsModal = false"
        >
          <div class="bg-white rounded-lg shadow-lg max-w-md w-full max-h-[90vh] overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold text-gray-900">{{ selectedStudent.name }}</h2>
              <button
                @click="showDetailsModal = false"
                class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
              >
                <X class="w-6 h-6" />
              </button>
            </div>
            <div class="flex flex-col items-center space-y-4 mb-6">
              <div
                class="w-28 h-28 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center text-4xl font-bold text-gray-600"
              >
                <img
                  v-if="selectedStudent.profileImage"
                  :src="getImageUrl(selectedStudent.profileImage)"
                  :alt="selectedStudent.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                  loading="lazy"
                />
                <span v-else>{{ getInitials(selectedStudent.name) }}</span>
              </div>
              <p class="text-gray-700 text-center max-w-xs">{{ selectedStudent.email }}</p>
            </div>

        <ul class="space-y-3 text-gray-700 text-sm">
          <li>
            <strong>Role:</strong> {{ selectedStudent.role }}
          </li>
          <li>
            <strong>Phone:</strong> {{ selectedStudent.phone || 'No phone' }}
          </li>
          <li>
            <strong>Status:</strong>
            <span :class="getStatusBadgeClass(selectedStudent.status)" class="px-2 py-1 rounded-full text-xs font-medium">
              {{ selectedStudent.status }}
            </span>
          </li>
          <li>
            <strong>Enrollment Date:</strong> {{ formatDate(selectedStudent.createdAt) }}
          </li>
        </ul>

        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="startEdit(selectedStudent); showDetailsModal = false"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
          >
            Edit
          </button>
          <button
            @click="askDelete(selectedStudent); showDetailsModal = false"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200"
          >
            Delete
          </button>
          <button
            @click="showDetailsModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200"
          >
            Close
          </button>
        </div>
      </div>
    </div>

  
    <div
      v-if="showDeleteModal && studentToDelete"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      @click.self="showDeleteModal = false"
    >
      <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6 text-center">
        <AlertTriangle class="w-12 h-12 mx-auto text-red-600 mb-4" />
        <h3 class="text-lg font-bold mb-2">Confirm Delete</h3>
        <p class="mb-4 text-gray-700">
          Are you sure you want to delete
          <strong>{{ studentToDelete.name }}</strong>?
          This action cannot be undone.
        </p>
        <div class="flex justify-center gap-4">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200"
          >
            Cancel
          </button>
          <button
            @click="confirmDelete"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive, watch } from 'vue'
import {
  Plus, Search, Briefcase, Phone, Calendar, Edit, Trash2,
  Users, RotateCcw, X, AlertTriangle
} from 'lucide-vue-next'
import studentsAPI from '@/api/students'

// Add type declaration for import.meta.env
declare global {
  interface ImportMeta {
    env: {
      VITE_API_BASE_URL?: string;
      [key: string]: string | undefined;
    };
  }
}

type Role = 'Student' | 'Graduate' | 'Alumni'
type Status = 'Active' | 'Inactive'

type Student = {
  id: number
  name: string
  email: string
  phone?: string
  role: Role
  status: Status
  createdAt: string // ISO date (YYYY-MM-DD) or date string
  profileImage?: string
}

// Reactive state
const searchQuery = ref<string>('')
const selectedRole = ref<Role | ''>('')
const sortBy = ref<'name' | 'email' | 'createdAt'>('name')

const showAddModal = ref<boolean>(false)
const showEditModal = ref<boolean>(false)
const showDetailsModal = ref<boolean>(false)
const showDeleteModal = ref<boolean>(false)
const loading = ref<boolean>(false)

const selectedStudent = ref<Student | null>(null)
const studentToDelete = ref<Student | null>(null)

// New student form data
const newStudent = reactive<Omit<Student, 'id'>>({
  name: '',
  email: '',
  phone: '',
  role: '' as any,
  status: '' as any,
  createdAt: '',
  profileImage: ''
})


// Edit form state
const editForm = reactive<Student>({
  id: 0,
  name: '',
  email: '',
  phone: '',
  role: 'Student',
  status: 'Active',
  createdAt: '',
  profileImage: ''
})

// Students list loaded from API
const students = ref<Student[]>([])

// Computed: items are server-paginated; client filtering only displays current page
const filteredStudents = computed<Student[]>(() => students.value)

// Methods
function getInitials(name: string): string {
  if (!name) return ''
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function getImageUrl(imagePath: string): string {
  if (!imagePath) return ''
  
  // Handle full URLs
  if (imagePath.startsWith('http')) {
    return imagePath
  }
  
  // Handle relative paths
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
  return `${baseUrl}/storage/${imagePath.replace(/^\/+/, '')}`
}

function handleImageError(event: Event) {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  
  // Find the parent container and show initials fallback
  const parent = img.parentElement
  if (parent) {
    const fallback = document.createElement('span')
    fallback.className = 'text-xl font-bold text-white'
    fallback.textContent = getInitials(img.alt || '')
    parent.appendChild(fallback)
  }
}

function getStatusBadgeClass(status: Status | string): string {
  switch (status) {
    case 'Active':
      return 'bg-green-500/20 text-green-800 border border-green-400/30'
    case 'Inactive':
      return 'bg-red-500/20 text-red-800 border border-red-400/30'
    default:
      return 'bg-gray-500/20 text-gray-800 border border-gray-400/30'
  }
}

function formatDate(dateString: string): string {
  if (!dateString) return 'N/A'
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return 'N/A'
  }
}

function openDetails(student: Student) {
  selectedStudent.value = student
  showDetailsModal.value = true
}

function startEdit(student: Student) {
  editForm.id = student.id
  editForm.name = student.name
  editForm.email = student.email
  editForm.phone = student.phone || ''
  editForm.role = student.role
  editForm.status = student.status
  editForm.createdAt = student.createdAt
  editForm.profileImage = student.profileImage || ''
  showEditModal.value = true
}

function closeEdit() {
  showEditModal.value = false
}

async function saveEdit() {
  if (!editForm.name.trim() || !editForm.email.trim()) return

  try {
    const [firstName, ...restNames] = editForm.name.trim().split(' ')
    const lastName = restNames.join(' ')
    
    const updateData = {
      first_name: firstName,
      last_name: lastName,
      email: editForm.email.trim(),
      parent_phone: editForm.phone.trim(),
      is_active: editForm.status === 'Active'
    }

    await studentsAPI.updateStudent(editForm.id, updateData)
    
    await loadStudents()
    
    showEditModal.value = false
    
    if (selectedStudent.value && selectedStudent.value.id === editForm.id) {
      const updatedStudent = students.value.find(s => s.id === editForm.id)
      if (updatedStudent) {
        selectedStudent.value = { ...updatedStudent }
      }
    }
  } catch (error) {
    console.error('Error updating student:', error)
    alert('Failed to update student. Please try again.')
  }
}

function askDelete(student: Student) {
  studentToDelete.value = student
  showDeleteModal.value = true
}

async function confirmDelete() {
  if (!studentToDelete.value) return

  try {
    await studentsAPI.deleteStudent(studentToDelete.value.id)
    await loadStudents()
  } catch (error) {
    console.error('Error deleting student:', error)
    alert('Failed to delete student. Please try again.')
  } finally {
    showDeleteModal.value = false
    studentToDelete.value = null
  }
}

async function addStudent() {
  if (!newStudent.name.trim() || !newStudent.email.trim()) return

  try {
    const [firstName, ...restNames] = newStudent.name.trim().split(' ')
    const lastName = restNames.join(' ') || 'Student'
    
    // Generate username and student code
    const username = newStudent.email.trim().split('@')[0] + Math.floor(Math.random() * 1000)
    const studentCode = 'STU' + Date.now().toString().slice(-6)
    
    const studentData = {
      first_name: firstName,
      last_name: lastName,
      username: username,
      email: newStudent.email.trim(),
      student_code: studentCode,
      enrollment_date: new Date().toISOString().split('T')[0],
      parent_phone: newStudent.phone?.trim() || '',
      is_active: true
    }

    await studentsAPI.createStudent(studentData)
    
    // Refresh the students list from server
    await loadStudents()

    // Reset form
    newStudent.name = ''
    newStudent.email = ''
    newStudent.phone = ''
    newStudent.role = '' as any
    newStudent.status = '' as any
    newStudent.createdAt = ''
    newStudent.profileImage = ''

    showAddModal.value = false
    
    // Show success message
    alert('Student added successfully!')
  } catch (error) {
    console.error('Error adding student:', error)
    alert('Failed to add student. Please try again.')
  }
}

function clearFilters() {
  searchQuery.value = ''
  selectedRole.value = ''
  sortBy.value = 'name'
}

const prevPage = async () => {
  if (page.value > 1) {
    page.value--
    await loadStudents()
  }
}

const nextPage = async () => {
  if (page.value < totalPages.value) {
    page.value++
    await loadStudents()
  }
}

// Server-side table: fetch paginated students with search/sort
const page = ref<number>(1)
const perPage = ref<number>(12)
const totalPages = ref<number>(1)

async function loadStudents() {
  loading.value = true
  try {
    const params: any = { page: page.value, per_page: perPage.value }
    if (searchQuery.value) params.search = searchQuery.value
    // Map sort key
    let sort_by = 'created_at'
    if (sortBy.value === 'name') sort_by = 'name'
    if (sortBy.value === 'email') sort_by = 'email'
    params.sort_by = sort_by
    params.sort_dir = sort_by === 'created_at' ? 'desc' : 'asc'

    const res = await studentsAPI.getAllStudents(params)
    const payload = res.data?.data
    const rows: any[] = payload?.data || payload || []
    students.value = rows.map((s: any) => ({
      id: s.user_id,
      name: `${s.user?.first_name || ''} ${s.user?.last_name || ''}`.trim() || 'Unknown',
      email: s.user?.email || 'unknown@example.com',
      phone: s.parent_phone || '',
      role: 'Student',
      status: s.user?.is_active ? 'Active' : 'Inactive',
      createdAt: s.user?.created_at || '',
      profileImage: s.user?.profile_picture || ''
    }))
    totalPages.value = payload?.last_page ?? 1
    page.value = payload?.current_page ?? 1
  } catch (error) {
    console.error('Error loading students:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadStudents()
})

watch([searchQuery, sortBy], () => {
  page.value = 1
  loadStudents()
})

</script>

<style scoped>
button:focus-visible {
  outline: 2px solid #3b82f6; /* blue-500 */
  outline-offset: 2px;
}
</style>
