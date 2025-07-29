<template>
  <header class="relative bg-gradient-to-r from-blue-10 via-purple-500 to-blue-00 overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 via-purple-600/20 to-pink-600/20"></div>
    <div class="absolute top-0 left-0 w-full h-full">
      <div class="absolute top-10 left-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute top-20 right-20 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
      <div class="absolute bottom-10 left-1/2 w-80 h-80 bg-pink-500/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>
    
    <!-- Glass overlay -->
    <div class="absolute inset-0 bg-white/5 backdrop-blur-sm"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between py-8">
        <!-- Left side - Logo and Title -->
        <div class="flex items-center space-x-6">
          <!-- Logo -->
          <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 via-purple-600 rounded-2xl blur opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-tilt"></div>
            <div class="relative w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl transform group-hover:scale-110 transition-all duration-300">
              <BookOpen class="w-8 h-8 text-white" />
            </div>
          </div>
          
          <!-- Title and Subtitle -->
          <div class="space-y-1">
            <h1 class="text-4xl font-black bg-gradient-to-r from-white via-blue-100 to-purple-100 bg-clip-text text-transparent">
              My Classes
            </h1>
            <p class="text-blue-200/80 font-medium text-lg">
              Manage and organize your teaching schedule
            </p>
          </div>
        </div>

        <!-- Center - Quick Search -->
        <div class="hidden lg:flex flex-1 max-w-2xl mx-8">
          <div class="relative w-full group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
            <div class="relative flex items-center">
              <div class="absolute left-4 z-10">
                <Search class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors duration-300" />
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search classes, students, or subjects..."
                class="w-full pl-12 pr-16 py-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-transparent focus:bg-white/20 transition-all duration-300 font-medium"
                @focus="showSearchResults = true"
                @blur="hideSearchResults"
                @input="$emit('search', searchQuery)"
              />
              <div class="absolute right-2">
                <button class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white p-2.5 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                  <Filter class="w-4 h-4" />
                </button>
              </div>
            </div>
            
            <!-- Search Results Dropdown -->
            <div v-if="showSearchResults && searchQuery" class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 z-50 max-h-96 overflow-y-auto">
              <div class="p-4">
                <div class="space-y-3">
                  <!-- Classes Results -->
                  <div v-if="filteredClasses.length > 0">
                    <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Classes</h4>
                    <div class="space-y-2">
                      <div v-for="cls in filteredClasses" :key="cls.id" class="flex items-center p-3 hover:bg-blue-50 rounded-xl cursor-pointer transition-colors duration-200 group">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                          <BookOpen class="w-5 h-5 text-white" />
                        </div>
                        <div>
                          <p class="font-semibold text-gray-900 group-hover:text-blue-600">{{ cls.name }}</p>
                          <p class="text-sm text-gray-500">{{ cls.studentCount }} students • Room {{ cls.room }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Students Results -->
                  <div v-if="filteredStudents.length > 0">
                    <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2 mt-4">Students</h4>
                    <div class="space-y-2">
                      <div v-for="student in filteredStudents" :key="student.id" class="flex items-center p-3 hover:bg-green-50 rounded-xl cursor-pointer transition-colors duration-200 group">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                          <User class="w-5 h-5 text-white" />
                        </div>
                        <div>
                          <p class="font-semibold text-gray-900 group-hover:text-green-600">{{ student.name }}</p>
                          <p class="text-sm text-gray-500">{{ student.code }} • Grade {{ getStudentClass(student.classId) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- No Results -->
                  <div v-if="filteredClasses.length === 0 && filteredStudents.length === 0" class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <Search class="w-8 h-8 text-gray-400" />
                    </div>
                    <p class="text-gray-500 font-medium">No results found</p>
                    <p class="text-sm text-gray-400">Try searching for a different term</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right side - Quick Actions and Profile -->
        <div class="flex items-center space-x-4">
          <!-- Quick Find Button (Mobile) -->
          <button @click="showMobileSearch = true" class="lg:hidden bg-white/10 backdrop-blur-md border border-white/20 text-white p-3 rounded-xl hover:bg-white/20 transition-all duration-300">
            <Search class="w-5 h-5" />
          </button>
          
          <!-- Quick Actions -->
          <div class="hidden sm:flex items-center space-x-3">
            <button @click="$emit('quick-add')" class="relative group bg-white/10 backdrop-blur-md border border-white/20 text-white px-6 py-3 rounded-xl hover:bg-white/20 transition-all duration-300 font-medium">
              <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl blur opacity-0 group-hover:opacity-30 transition duration-300"></div>
              <span class="relative flex items-center">
                <Plus class="w-4 h-4 mr-2" />
                Quick Add
              </span>
            </button>
            
            <button @click="$emit('find-class')" class="relative group bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 font-medium">
              <span class="flex items-center">
                <Zap class="w-4 h-4 mr-2" />
                Find Class
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Search Modal -->
    <div v-if="showMobileSearch" class="lg:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-start justify-center pt-20">
      <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 w-full max-w-md mx-4">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Quick Search</h3>
            <button @click="showMobileSearch = false" class="text-gray-400 hover:text-gray-600 p-2">
              <X class="w-5 h-5" />
            </button>
          </div>
          <div class="relative">
            <Search class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search classes, students..."
              class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              autofocus
            />
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { BookOpen, Plus, Zap, Search, Filter, User, ChevronDown, X } from 'lucide-vue-next'

const searchQuery = ref('')
const showSearchResults = ref(false)
const showMobileSearch = ref(false)

defineEmits(['search', 'quick-add', 'find-class'])

// Mock data for search
const classes = [
  { id: 1, name: 'Grade 10A', studentCount: 28, room: '101' },
  { id: 2, name: 'Grade 11B', studentCount: 25, room: '205' },
  { id: 3, name: 'Grade 12C', studentCount: 22, room: '301' }
]

const students = [
  { id: 1, name: 'Alice Johnson', code: 'STU001', classId: 1 },
  { id: 2, name: 'Bob Smith', code: 'STU002', classId: 1 },
  { id: 3, name: 'Carol Davis', code: 'STU003', classId: 2 }
]

const filteredClasses = computed(() => {
  if (!searchQuery.value) return []
  return classes.filter(cls => 
    cls.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  ).slice(0, 3)
})

const filteredStudents = computed(() => {
  if (!searchQuery.value) return []
  return students.filter(student => 
    student.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    student.code.toLowerCase().includes(searchQuery.value.toLowerCase())
  ).slice(0, 3)
})

const hideSearchResults = () => {
  setTimeout(() => {
    showSearchResults.value = false
  }, 200)
}

const getStudentClass = (classId) => {
  const classItem = classes.find(c => c.id === classId)
  return classItem ? classItem.name : 'Unknown'
}
</script>

<style scoped>
@keyframes tilt {
  0%, 50%, 100% {
    transform: rotate(0deg);
  }
  25% {
    transform: rotate(1deg);
  }
  75% {
    transform: rotate(-1deg);
  }
}

.animate-tilt {
  animation: tilt 10s infinite linear;
}
</style>
 