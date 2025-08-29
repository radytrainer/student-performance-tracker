<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <button
              @click="$router.back()"
              class="mr-4 p-2 text-gray-400 hover:text-gray-600"
            >
              <i class="fas fa-arrow-left"></i>
            </button>
            <h1 class="text-2xl font-bold text-gray-900">Teacher Details</h1>
          </div>
          
          <div class="flex items-center space-x-3">
            <!-- Real-time indicator -->
            <div v-if="isRealTimeConnected" class="flex items-center text-green-600">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1"></div>
              <span class="text-xs">Live</span>
            </div>
            
            <!-- Action buttons -->
            <button
              @click="refreshData"
              :disabled="loading"
              class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              <i v-if="loading" class="fas fa-spinner fa-spin mr-1"></i>
              <i v-else class="fas fa-sync-alt mr-1"></i>
              Refresh
            </button>
            
            <button
              @click="editTeacher"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm"
            >
              <i class="fas fa-edit mr-1"></i>
              Edit Teacher
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !teacher" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
          <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error loading teacher</h3>
            <p class="text-sm text-red-700 mt-1">{{ error }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Teacher Detail Content -->
    <div v-else-if="teacher" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Teacher Profile -->
        <div class="lg:col-span-1">
          <!-- Profile Card -->
          <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center space-x-4 mb-6">
              <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-100">
                <img v-if="teacher.user?.profile_picture" 
                     :src="resolveImage(teacher.user.profile_picture)" 
                     :alt="teacher.full_name"
                     class="h-full w-full object-cover">
                <div v-else class="h-full w-full flex items-center justify-center text-gray-400">
                  <i class="fas fa-user text-2xl"></i>
                </div>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-900">{{ teacher.full_name }}</h2>
                <p class="text-gray-600">{{ teacher.user?.email }}</p>
                <div class="flex items-center mt-1">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="teacher.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    {{ teacher.status }}
                  </span>
                  <span v-if="teacher.teacher_code" class="ml-2 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                    {{ teacher.teacher_code }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Contact Information -->
            <div class="space-y-3">
              <h3 class="text-sm font-medium text-gray-900">Contact Information</h3>
              <div class="text-sm text-gray-600">
                <div v-if="teacher.user?.phone" class="flex items-center">
                  <i class="fas fa-phone w-4 text-gray-400 mr-2"></i>
                  {{ teacher.user.phone }}
                </div>
                <div class="flex items-center mt-1">
                  <i class="fas fa-envelope w-4 text-gray-400 mr-2"></i>
                  {{ teacher.user?.email }}
                </div>
              </div>
            </div>
          </div>

          <!-- Professional Information -->
          <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Professional Information</h3>
            <div class="space-y-3">
              <div v-if="teacher.department">
                <label class="text-sm font-medium text-gray-500">Department</label>
                <p class="text-sm text-gray-900">{{ teacher.department.name }}</p>
              </div>
              
              <div v-if="teacher.employment_type">
                <label class="text-sm font-medium text-gray-500">Employment Type</label>
                <p class="text-sm text-gray-900 capitalize">{{ teacher.employment_type.replace('_', ' ') }}</p>
              </div>
              
              <div v-if="teacher.qualification">
                <label class="text-sm font-medium text-gray-500">Qualification</label>
                <p class="text-sm text-gray-900">{{ teacher.qualification }}</p>
              </div>
              
              <div v-if="teacher.years_experience">
                <label class="text-sm font-medium text-gray-500">Experience</label>
                <p class="text-sm text-gray-900">{{ teacher.years_experience }} years</p>
              </div>
              
              <div v-if="teacher.join_date">
                <label class="text-sm font-medium text-gray-500">Join Date</label>
                <p class="text-sm text-gray-900">{{ formatDate(teacher.join_date) }}</p>
              </div>
            </div>
          </div>

          <!-- Skills & Specializations -->
          <div v-if="teacher.skills?.length > 0" class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Skills & Specializations</h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="skill in teacher.skills"
                :key="skill"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
              >
                {{ skill }}
              </span>
            </div>
          </div>
        </div>

        <!-- Right Column - Details -->
        <div class="lg:col-span-2">
          <!-- Quick Stats -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                  </div>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-500">Classes</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ teacher.classes?.length || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-graduate text-green-600"></i>
                  </div>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-500">Students</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ totalStudents }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-purple-600"></i>
                  </div>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-500">Subjects</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ teacher.subjects?.length || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-orange-600"></i>
                  </div>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-500">Avg Rating</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ teacher.avg_rating || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200">
              <nav class="-mb-px flex space-x-8 px-6">
                <button
                  @click="activeTab = 'classes'"
                  :class="[
                    'py-4 px-1 border-b-2 font-medium text-sm',
                    activeTab === 'classes' 
                      ? 'border-blue-500 text-blue-600' 
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Classes ({{ teacher.classes?.length || 0 }})
                </button>
                <button
                  @click="activeTab = 'subjects'"
                  :class="[
                    'py-4 px-1 border-b-2 font-medium text-sm',
                    activeTab === 'subjects' 
                      ? 'border-blue-500 text-blue-600' 
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Subjects ({{ teacher.subjects?.length || 0 }})
                </button>
                <button
                  @click="activeTab = 'performance'"
                  :class="[
                    'py-4 px-1 border-b-2 font-medium text-sm',
                    activeTab === 'performance' 
                      ? 'border-blue-500 text-blue-600' 
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  Performance
                </button>
                <button
                  @click="activeTab = 'history'"
                  :class="[
                    'py-4 px-1 border-b-2 font-medium text-sm',
                    activeTab === 'history' 
                      ? 'border-blue-500 text-blue-600' 
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  History
                </button>
              </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
              <!-- Classes Tab -->
              <div v-show="activeTab === 'classes'">
                <div v-if="teacher.classes?.length > 0" class="space-y-4">
                  <div
                    v-for="classItem in teacher.classes"
                    :key="classItem.id"
                    class="border rounded-lg p-4 hover:bg-gray-50"
                  >
                    <div class="flex items-center justify-between">
                      <div>
                        <h4 class="text-lg font-medium text-gray-900">{{ classItem.name }}</h4>
                        <p class="text-sm text-gray-600">{{ classItem.students_count || 0 }} students</p>
                      </div>
                      <div class="flex items-center space-x-2">
                        <router-link
                          :to="`/admin/classes/${classItem.id}`"
                          class="text-blue-600 hover:text-blue-800"
                        >
                          <i class="fas fa-external-link-alt"></i>
                        </router-link>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  No classes assigned
                </div>
              </div>

              <!-- Subjects Tab -->
              <div v-show="activeTab === 'subjects'">
                <div v-if="teacher.subjects?.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                    v-for="subject in teacher.subjects"
                    :key="subject.id"
                    class="border rounded-lg p-4"
                  >
                    <h4 class="text-lg font-medium text-gray-900">{{ subject.subject_name }}</h4>
                    <p class="text-sm text-gray-600">{{ subject.subject_code }}</p>
                    <div class="mt-2">
                      <span
                        v-if="subject.department"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                      >
                        {{ subject.department }}
                      </span>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  No subjects assigned
                </div>
              </div>

              <!-- Performance Tab -->
              <div v-show="activeTab === 'performance'">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- Student Performance -->
                  <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Student Performance Overview</h4>
                    <div class="space-y-3">
                      <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm text-gray-600">Average Grade</span>
                        <span class="font-medium">{{ performance?.average_grade || 'N/A' }}</span>
                      </div>
                      <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm text-gray-600">Pass Rate</span>
                        <span class="font-medium">{{ performance?.pass_rate || 'N/A' }}%</span>
                      </div>
                      <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                        <span class="text-sm text-gray-600">Student Satisfaction</span>
                        <div class="flex items-center">
                          <span class="font-medium mr-1">{{ performance?.satisfaction || 'N/A' }}</span>
                          <div class="flex text-yellow-400">
                            <i v-for="i in 5" :key="i" :class="i <= Math.floor(performance?.satisfaction || 0) ? 'fas fa-star' : 'far fa-star'" class="text-xs"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Performance Chart -->
                  <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Grade Distribution</h4>
                    <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
                      <p class="text-gray-500">Performance chart would be displayed here</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- History Tab -->
              <div v-show="activeTab === 'history'">
                <div v-if="history?.length > 0" class="space-y-4">
                  <div
                    v-for="item in history"
                    :key="item.id"
                    class="border-l-4 border-blue-500 pl-4 py-2"
                  >
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ item.action }}</p>
                        <p class="text-xs text-gray-600">{{ item.description }}</p>
                      </div>
                      <time class="text-xs text-gray-500">{{ formatDate(item.created_at) }}</time>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  No history available
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Teacher Edit Modal -->
    <TeacherModal
      :show="showEditModal"
      :teacher="teacher"
      :subjects="subjects"
      :departments="departments"
      :loading="modalLoading"
      @close="showEditModal = false"
      @submit="handleTeacherUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminAPI } from '@/api/admin'
import { resolveImageUrl } from '@/utils/imageUrl'
import TeacherModal from '@/components/admin/TeacherModal.vue'

const route = useRoute()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const teacher = ref(null)
const subjects = ref([])
const departments = ref([])
const performance = ref(null)
const history = ref([])
const activeTab = ref('classes')
const showEditModal = ref(false)
const modalLoading = ref(false)
const isRealTimeConnected = ref(false)

// Computed
const totalStudents = computed(() => {
  return teacher.value?.classes?.reduce((total, classItem) => {
    return total + (classItem.students_count || 0)
  }, 0) || 0
})

// Methods
const loadTeacher = async () => {
  try {
    loading.value = true
    error.value = null
    
    const teacherId = route.params.id
    const response = await adminAPI.getTeacher(teacherId)
    teacher.value = response.data
    
    // Load additional data
    await Promise.all([
      loadPerformanceData(),
      loadHistoryData(),
      loadSubjects(),
      loadDepartments()
    ])
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load teacher'
    console.error('Error loading teacher:', err)
  } finally {
    loading.value = false
  }
}

const loadPerformanceData = async () => {
  try {
    const response = await adminAPI.getTeacherAnalytics(teacher.value.user_id)
    performance.value = response.data
  } catch (err) {
    console.error('Error loading performance data:', err)
  }
}

const loadHistoryData = async () => {
  try {
    // Mock history data - would come from API in real implementation
    history.value = [
      {
        id: 1,
        action: 'Profile Updated',
        description: 'Updated contact information',
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        action: 'Subject Assigned',
        description: 'Assigned to Mathematics',
        created_at: new Date(Date.now() - 86400000).toISOString()
      }
    ]
  } catch (err) {
    console.error('Error loading history data:', err)
  }
}

const loadSubjects = async () => {
  try {
    const response = await adminAPI.getSubjects()
    subjects.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Error loading subjects:', err)
  }
}

const loadDepartments = async () => {
  try {
    const response = await adminAPI.getDepartments()
    departments.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Error loading departments:', err)
    // Use mock departments as fallback
    departments.value = [
      { id: 1, name: 'Mathematics' },
      { id: 2, name: 'Science' },
      { id: 3, name: 'English' },
      { id: 4, name: 'History' },
      { id: 5, name: 'Physical Education' },
      { id: 6, name: 'Arts' }
    ]
  }
}

const refreshData = () => {
  loadTeacher()
}

const editTeacher = () => {
  showEditModal.value = true
}

const handleTeacherUpdate = async (teacherData) => {
  try {
    modalLoading.value = true
    await adminAPI.updateTeacher(teacher.value.user_id, teacherData)
    showEditModal.value = false
    await loadTeacher()
  } catch (err) {
    console.error('Error updating teacher:', err)
  } finally {
    modalLoading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString()
}

const resolveImage = (imagePath) => {
  return resolveImageUrl(imagePath)
}

// Initialize real-time connection (mock)
const initRealTime = () => {
  setTimeout(() => {
    isRealTimeConnected.value = true
  }, 1000)
}

onMounted(() => {
  loadTeacher()
  initRealTime()
})

onUnmounted(() => {
  // Clean up real-time connections
})
</script>
