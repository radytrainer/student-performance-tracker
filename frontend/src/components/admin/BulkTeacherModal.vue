<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal Header -->
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          Bulk Operations - {{ selectedTeachers.length }} teachers selected
        </h3>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Operation Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeOperation = 'status'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeOperation === 'status' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Update Status
          </button>
          <button
            @click="activeOperation = 'subjects'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeOperation === 'subjects' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Assign Subjects
          </button>
          <button
            @click="activeOperation = 'classes'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeOperation === 'classes' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Assign Classes
          </button>
          <button
            @click="activeOperation = 'department'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeOperation === 'department' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Change Department
          </button>
        </nav>
      </div>

      <!-- Selected Teachers Summary -->
      <div class="bg-blue-50 rounded-lg p-4 mb-6">
        <h4 class="text-sm font-medium text-blue-900 mb-2">Selected Teachers</h4>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="teacher in selectedTeachersData"
            :key="teacher.user_id"
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
          >
            {{ teacher.full_name }}
          </span>
        </div>
      </div>

      <!-- Operation Forms -->
      <form @submit.prevent="submitOperation" class="space-y-6">
        <!-- Status Update -->
        <div v-show="activeOperation === 'status'">
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Update Status
          </label>
          <select
            v-model="operationData.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          >
            <option value="">Select Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
          <p class="text-sm text-gray-500 mt-2">
            This will update the status for all {{ selectedTeachers.length }} selected teachers.
          </p>
        </div>

        <!-- Subject Assignment -->
        <div v-show="activeOperation === 'subjects'">
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Assign Subjects
          </label>
          <div class="mb-4">
            <div class="flex items-center space-x-4 mb-3">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="operationData.subjectAction"
                  value="add"
                  class="form-radio"
                />
                <span class="ml-2 text-sm">Add to existing subjects</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="operationData.subjectAction"
                  value="replace"
                  class="form-radio"
                />
                <span class="ml-2 text-sm">Replace existing subjects</span>
              </label>
            </div>
          </div>
          
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-4">
            <label v-for="subject in subjects" :key="subject.id" class="flex items-center space-x-2 text-sm">
              <input
                type="checkbox"
                :value="subject.id"
                v-model="operationData.subject_ids"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span>{{ subject.subject_name }}</span>
            </label>
          </div>
          <p class="text-sm text-gray-500 mt-2">
            Selected: {{ operationData.subject_ids.length }} subjects
          </p>
        </div>

        <!-- Class Assignment -->
        <div v-show="activeOperation === 'classes'">
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Assign Classes
          </label>
          <div class="mb-4">
            <div class="flex items-center space-x-4 mb-3">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="operationData.classAction"
                  value="add"
                  class="form-radio"
                />
                <span class="ml-2 text-sm">Add to existing classes</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="operationData.classAction"
                  value="replace"
                  class="form-radio"
                />
                <span class="ml-2 text-sm">Replace existing classes</span>
              </label>
            </div>
          </div>
          
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-4">
            <label v-for="classItem in availableClasses" :key="classItem.id" class="flex items-center space-x-2 text-sm">
              <input
                type="checkbox"
                :value="classItem.id"
                v-model="operationData.class_ids"
                class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
              />
              <span>{{ classItem.name }}</span>
            </label>
          </div>
          <p class="text-sm text-gray-500 mt-2">
            Selected: {{ operationData.class_ids.length }} classes
          </p>
        </div>

        <!-- Department Change -->
        <div v-show="activeOperation === 'department'">
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Change Department
          </label>
          <select
            v-model="operationData.department_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          >
            <option value="">Select Department</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
          <p class="text-sm text-gray-500 mt-2">
            This will transfer all {{ selectedTeachers.length }} selected teachers to the new department.
          </p>
        </div>

        <!-- Progress Bar -->
        <div v-if="processing" class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>

        <!-- Processing Status -->
        <div v-if="processingResults.length > 0" class="bg-gray-50 rounded-lg p-4 max-h-40 overflow-y-auto">
          <h5 class="text-sm font-medium text-gray-900 mb-2">Processing Results</h5>
          <div class="space-y-1">
            <div
              v-for="result in processingResults"
              :key="result.teacher_id"
              class="flex items-center text-xs"
            >
              <i
                :class="result.success ? 'fas fa-check text-green-500' : 'fas fa-times text-red-500'"
                class="mr-2"
              ></i>
              <span>{{ result.teacher_name }}: {{ result.message }}</span>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between border-t pt-6">
          <div class="text-sm text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>
            This operation will affect {{ selectedTeachers.length }} teachers
          </div>
          
          <div class="flex items-center space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
              :disabled="processing"
            >
              Cancel
            </button>
            
            <button
              type="submit"
              :disabled="processing || !isFormValid"
              class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <i v-if="processing" class="fas fa-spinner fa-spin mr-2"></i>
              {{ getSubmitButtonText() }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { adminAPI } from '@/api/admin'

const props = defineProps({
  show: Boolean,
  selectedTeachers: Array,
  selectedTeachersData: Array,
  subjects: Array,
  departments: Array,
  availableClasses: Array
})

const emit = defineEmits(['close', 'completed'])

// State
const activeOperation = ref('status')
const processing = ref(false)
const progressPercentage = ref(0)
const processingResults = ref([])

// Form data
const operationData = reactive({
  status: '',
  subject_ids: [],
  subjectAction: 'add',
  class_ids: [],
  classAction: 'add',
  department_id: ''
})

// Computed
const isFormValid = computed(() => {
  switch (activeOperation.value) {
    case 'status':
      return operationData.status
    case 'subjects':
      return operationData.subject_ids.length > 0
    case 'classes':
      return operationData.class_ids.length > 0
    case 'department':
      return operationData.department_id
    default:
      return false
  }
})

// Methods
const closeModal = () => {
  if (!processing.value) {
    emit('close')
    resetForm()
  }
}

const resetForm = () => {
  activeOperation.value = 'status'
  processing.value = false
  progressPercentage.value = 0
  processingResults.value = []
  
  Object.keys(operationData).forEach(key => {
    if (Array.isArray(operationData[key])) {
      operationData[key] = []
    } else {
      operationData[key] = ''
    }
  })
  
  operationData.subjectAction = 'add'
  operationData.classAction = 'add'
}

const getSubmitButtonText = () => {
  if (processing.value) return 'Processing...'
  
  switch (activeOperation.value) {
    case 'status':
      return 'Update Status'
    case 'subjects':
      return 'Assign Subjects'
    case 'classes':
      return 'Assign Classes'
    case 'department':
      return 'Change Department'
    default:
      return 'Submit'
  }
}

const submitOperation = async () => {
  if (!isFormValid.value || processing.value) return
  
  processing.value = true
  processingResults.value = []
  progressPercentage.value = 0
  
  try {
    let result
    
    switch (activeOperation.value) {
      case 'status':
        result = await adminAPI.bulkUpdateTeacherStatus(props.selectedTeachers, operationData.status)
        break
        
      case 'subjects':
        if (operationData.subjectAction === 'replace') {
          // For replace, we need to process each teacher individually
          await processIndividually(async (teacherId) => {
            // First clear existing subjects
            const teacher = props.selectedTeachersData.find(t => t.user_id === teacherId)
            if (teacher?.subjects?.length > 0) {
              for (const subject of teacher.subjects) {
                await adminAPI.removeSubjectFromTeacher(teacherId, subject.id)
              }
            }
            // Then assign new subjects
            await adminAPI.assignSubjectsToTeacher(teacherId, operationData.subject_ids)
          })
        } else {
          result = await adminAPI.bulkAssignSubjects(props.selectedTeachers, operationData.subject_ids)
        }
        break
        
      case 'classes':
        if (operationData.classAction === 'replace') {
          await processIndividually(async (teacherId) => {
            const teacher = props.selectedTeachersData.find(t => t.user_id === teacherId)
            if (teacher?.classes?.length > 0) {
              for (const classItem of teacher.classes) {
                await adminAPI.removeClassFromTeacher(teacherId, classItem.id)
              }
            }
            await adminAPI.assignClassesToTeacher(teacherId, operationData.class_ids)
          })
        } else {
          result = await adminAPI.bulkAssignClasses(props.selectedTeachers, operationData.class_ids)
        }
        break
        
      case 'department':
        result = await adminAPI.bulkUpdateTeacherDepartment(props.selectedTeachers, operationData.department_id)
        break
    }
    
    if (result) {
      // Handle bulk operation result
      processingResults.value = result.data?.results || []
    }
    
    progressPercentage.value = 100
    
    // Show success message and close modal after delay
    setTimeout(() => {
      emit('completed', {
        operation: activeOperation.value,
        results: processingResults.value
      })
      closeModal()
    }, 1500)
    
  } catch (error) {
    console.error('Bulk operation failed:', error)
    processingResults.value.push({
      success: false,
      message: error.response?.data?.message || 'Operation failed'
    })
  } finally {
    processing.value = false
  }
}

const processIndividually = async (operationFn) => {
  const total = props.selectedTeachers.length
  
  for (let i = 0; i < total; i++) {
    const teacherId = props.selectedTeachers[i]
    const teacher = props.selectedTeachersData.find(t => t.user_id === teacherId)
    
    try {
      await operationFn(teacherId)
      processingResults.value.push({
        teacher_id: teacherId,
        teacher_name: teacher?.full_name || `Teacher ${teacherId}`,
        success: true,
        message: 'Successfully processed'
      })
    } catch (error) {
      processingResults.value.push({
        teacher_id: teacherId,
        teacher_name: teacher?.full_name || `Teacher ${teacherId}`,
        success: false,
        message: error.response?.data?.message || 'Failed to process'
      })
    }
    
    progressPercentage.value = Math.round(((i + 1) / total) * 100)
  }
}

// Watch for operation changes to reset form
watch(activeOperation, () => {
  // Reset operation-specific data when switching operations
  operationData.status = ''
  operationData.subject_ids = []
  operationData.class_ids = []
  operationData.department_id = ''
})

// Watch for modal show/hide
watch(() => props.show, (newVal) => {
  if (!newVal) {
    resetForm()
  }
})
</script>
