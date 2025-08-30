<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header -->
    <!-- <HeaderTeacher /> -->

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Classes Overview Cards -->
      <div v-if="!selectedClass" class="space-y-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Classes</h1>
            <p class="text-gray-600 mt-1">Manage and organize your teaching schedule</p>
          </div>
          <div class="flex space-x-3">
            <button @click="refreshData" 
              :disabled="loadingClasses"
              class="flex items-center bg-white/80 backdrop-blur-sm hover:bg-white text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl border border-gray-200 transition-all duration-200">
              <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loadingClasses }" />
              Refresh
            </button>
            <button @click="handleExportAttendance"
              class="flex items-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-200">
              <Download class="w-4 h-4 mr-2" />
              Export Data
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <!-- Total Classes Card -->
          <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/30 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div class="p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl shadow-lg group-hover:shadow-blue-500/25 transition-shadow duration-300">
                <BookOpen class="w-8 h-8 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Classes</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                  <Loader v-if="loadingClasses" class="w-6 h-6 animate-spin text-blue-600" />
                  <span v-else>{{ classes.length }}</span>
                </p>
                <p class="text-xs text-gray-500 mt-1">Active assignments</p>
              </div>
            </div>
          </div>

          <!-- Total Students Card -->
          <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/30 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div class="p-4 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl shadow-lg group-hover:shadow-emerald-500/25 transition-shadow duration-300">
                <Users class="w-8 h-8 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Students</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                  <Loader v-if="loadingClasses" class="w-6 h-6 animate-spin text-emerald-600" />
                  <span v-else>{{ totalStudents }}</span>
                </p>
                <p class="text-xs text-gray-500 mt-1">Across all classes</p>
              </div>
            </div>
          </div>

          <!-- Average Attendance Card -->
          <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/30 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div class="p-4 bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl shadow-lg group-hover:shadow-amber-500/25 transition-shadow duration-300">
                <Calendar class="w-8 h-8 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Avg Attendance</p>
                <p class="text-3xl font-bold text-gray-900">
                  <Loader v-if="loadingStats" class="w-8 h-8 animate-spin" />
                  <span v-else>{{ averageAttendance }}%</span>
                </p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                  <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-1.5 rounded-full transition-all duration-500"
                    :style="`width: ${averageAttendance}%`"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Average Performance Card -->
          <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/30 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div class="p-4 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl shadow-lg group-hover:shadow-purple-500/25 transition-shadow duration-300">
                <TrendingUp class="w-8 h-8 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Avg Performance</p>
                <p class="text-3xl font-bold text-gray-900">
                  <Loader v-if="loadingClasses" class="w-8 h-8 animate-spin" />
                  <span v-else>{{ averagePerformance }}</span>
                </p>
                <p class="text-xs text-gray-500 mt-1">Overall grade</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Classes Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30 p-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Your Assigned Classes</h2>
              <p class="text-gray-600 mt-1">Classes you teach or manage</p>
            </div>
            <div class="flex items-center space-x-3">
              <div class="relative">
                <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                <input 
                  v-model="classSearch"
                  type="text" 
                  placeholder="Search classes..." 
                  class="pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                />
              </div>
            </div>
          </div>
          
          <!-- Loading State -->
          <div v-if="loadingClasses" class="flex justify-center py-16">
            <div class="text-center">
              <Loader class="w-12 h-12 animate-spin text-blue-600 mx-auto mb-4" />
              <p class="text-gray-500">Loading your classes...</p>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-else-if="filteredClasses.length === 0" class="text-center py-16">
            <div class="bg-gray-100 rounded-2xl p-8 max-w-md mx-auto">
              <BookOpen class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-bold text-gray-900 mb-2">No Classes Found</h3>
              <p class="text-gray-500">
                {{ classSearch ? 'No classes match your search.' : 'You haven\'t been assigned to any classes yet.' }}
              </p>
              <p class="text-gray-500 text-sm mt-2">Contact your admin to get class assignments.</p>
            </div>
          </div>
          
          <!-- Classes Grid -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            <div v-for="classItem in filteredClasses" :key="classItem.id"
              class="group bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 hover:shadow-2xl hover:border-blue-300/50 transition-all duration-300 overflow-hidden">
              
              <!-- Class Header -->
              <div class="relative">
                <div class="h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
                <div class="p-6 pb-4">
                  <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                      <h3 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors">
                        {{ classItem.class_name || classItem.name }}
                      </h3>
                      <p class="text-sm text-gray-500 font-medium">{{ classItem.academic_year || '2024-2025' }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span class="px-3 py-1 bg-gradient-to-r from-emerald-100 to-emerald-200 text-emerald-800 text-xs font-bold rounded-full shadow-sm">
                        Active
                      </span>
                      <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                        <MoreHorizontal class="w-4 h-4" />
                      </button>
                    </div>
                  </div>

                  <!-- Class Info Grid -->
                  <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="flex items-center space-x-3 p-3 bg-gray-50/80 rounded-xl">
                      <div class="p-2 bg-gray-600 rounded-lg">
                        <MapPin class="w-4 h-4 text-white" />
                      </div>
                      <div>
                        <p class="text-xs text-gray-500 font-medium">Room</p>
                        <p class="text-sm font-bold text-gray-900">{{ getClassRoom(classItem) }}</p>
                      </div>
                    </div>
                    
                    <div class="flex items-center space-x-3 p-3 bg-gray-50/80 rounded-xl">
                      <div class="p-2 bg-blue-600 rounded-lg">
                        <Users class="w-4 h-4 text-white" />
                      </div>
                      <div>
                        <p class="text-xs text-gray-500 font-medium">Students</p>
                        <p class="text-sm font-bold text-gray-900">{{ getClassStudentCount(classItem) }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Schedule Info -->
                  <div class="mb-6">
                    <div class="flex items-center space-x-3 p-3 bg-purple-50/80 rounded-xl">
                      <div class="p-2 bg-purple-600 rounded-lg">
                        <Clock class="w-4 h-4 text-white" />
                      </div>
                      <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium">Schedule</p>
                        <p class="text-sm font-bold text-gray-900">{{ getClassSchedule(classItem) }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Subjects Tags -->
                  <div class="mb-6">
                    <p class="text-xs text-gray-500 font-medium mb-2">Subjects</p>
                    <div class="flex flex-wrap gap-2">
                      <span v-for="subject in getClassSubjects(classItem).slice(0, 3)" :key="subject"
                        class="px-3 py-1 bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 text-xs font-bold rounded-full shadow-sm">
                        {{ subject }}
                      </span>
                      <span v-if="getClassSubjects(classItem).length > 3"
                        class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full shadow-sm">
                        +{{ getClassSubjects(classItem).length - 3 }} more
                      </span>
                      <span v-if="getClassSubjects(classItem).length === 0"
                        class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full shadow-sm">
                        No subjects assigned
                      </span>
                    </div>
                  </div>

                  <!-- Performance Indicators -->
                  <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200/50">
                      <div class="flex items-center justify-between">
                        <div>
                          <p class="text-xs text-amber-700 font-bold">Attendance</p>
                          <p class="text-lg font-bold text-amber-900">{{ getClassAttendanceRate(classItem) }}%</p>
                        </div>
                        <div class="w-8 bg-amber-200 rounded-full h-1">
                          <div class="bg-amber-500 h-1 rounded-full transition-all duration-500"
                            :style="`width: ${getClassAttendanceRate(classItem)}%`"></div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="p-3 bg-purple-50/80 rounded-xl border border-purple-200/50">
                      <div class="flex items-center justify-between">
                        <div>
                          <p class="text-xs text-purple-700 font-bold">Avg Grade</p>
                          <p class="text-lg font-bold text-purple-900">{{ getClassAverageGrade(classItem) }}</p>
                        </div>
                        <div class="w-6 h-6 bg-purple-600 rounded-full flex items-center justify-center">
                          <TrendingUp class="w-3 h-3 text-white" />
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex space-x-3">
                    <button @click="viewClassDetails(classItem)"
                      class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl text-sm font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                      <Eye class="w-4 h-4 mr-2 inline" />
                      View Details
                    </button>
                    <button @click="viewStudents(classItem)"
                      class="px-4 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                      <Users class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Class Detail Component -->
      <div v-if="selectedClass" class="space-y-8">
        <!-- Breadcrumb and Actions -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button @click="clearSelectedClass"
                class="flex items-center text-gray-600 hover:text-gray-900 px-4 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200 shadow-md border border-gray-200">
                <ArrowLeft class="w-5 h-5 mr-2" />
                Back to Classes
              </button>
              <nav class="text-sm text-gray-500 font-medium">
                <span class="text-blue-600">My Classes</span>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-bold">{{ selectedClass.class_name || selectedClass.name }}</span>
              </nav>
            </div>
            <div class="flex space-x-3">
              <button @click="showNotes = true"
                class="flex items-center bg-gradient-to-r from-amber-100 to-orange-200 hover:from-amber-200 hover:to-orange-300 text-orange-700 px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all duration-200">
                <StickyNote class="w-4 h-4 mr-2" />
                Notes
              </button>
              <button class="flex items-center bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all duration-200">
                <Settings class="w-4 h-4 mr-2" />
                Settings
              </button>
            </div>
          </div>
        </div>

        <!-- Class Header -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30 p-8">
          <div class="flex items-start justify-between mb-8">
            <div class="flex-1">
              <div class="flex items-center space-x-4 mb-4">
                <div class="p-4 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-lg">
                  <BookOpen class="w-8 h-8 text-white" />
                </div>
                <div>
                  <h1 class="text-3xl font-bold text-gray-900">{{ selectedClass.class_name || selectedClass.name }}</h1>
                  <p class="text-gray-600 text-lg font-medium">{{ selectedClass.academic_year || '2024-2025' }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-6 text-sm text-gray-600">
                <div class="flex items-center space-x-2">
                  <MapPin class="w-4 h-4" />
                  <span class="font-medium">Room {{ getClassRoom(selectedClass) }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <Clock class="w-4 h-4" />
                  <span class="font-medium">{{ getClassSchedule(selectedClass) }}</span>
                </div>
              </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4 min-w-[300px]">
              <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200/50 p-4 rounded-xl text-center">
                <Users class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                <p class="text-2xl font-bold text-blue-900">
                  <Loader v-if="loadingStudents" class="w-6 h-6 animate-spin mx-auto" />
                  <span v-else>{{ getClassStudentCount(selectedClass) }}</span>
                </p>
                <p class="text-xs text-blue-700 font-bold">Students</p>
              </div>
              
              <div class="bg-gradient-to-r from-amber-50 to-amber-100 border border-amber-200/50 p-4 rounded-xl text-center">
                <Calendar class="w-6 h-6 text-amber-600 mx-auto mb-2" />
                <p class="text-2xl font-bold text-amber-900">
                  <Loader v-if="loadingStats" class="w-6 h-6 animate-spin mx-auto" />
                  <span v-else>{{ getClassAttendanceRate(selectedClass) }}%</span>
                </p>
                <p class="text-xs text-amber-700 font-bold">Attendance</p>
              </div>
              
              <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200/50 p-4 rounded-xl text-center">
                <TrendingUp class="w-6 h-6 text-purple-600 mx-auto mb-2" />
                <p class="text-2xl font-bold text-purple-900">
                  <Loader v-if="loading" class="w-6 h-6 animate-spin mx-auto" />
                  <span v-else>{{ getClassAverageGrade(selectedClass) }}</span>
                </p>
                <p class="text-xs text-purple-700 font-bold">Avg Grade</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30">
          <div class="border-b border-gray-200/50">
            <nav class="flex space-x-1 px-6">
              <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" 
                :class="[
                  'py-4 px-6 border-b-3 font-bold text-sm flex items-center space-x-2 transition-all duration-200',
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600 bg-blue-50/50'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50/30'
                ]">
                <component :is="tab.icon" class="w-5 h-5" />
                <span>{{ tab.name }}</span>
              </button>
            </nav>
          </div>

          <div class="p-8">
            <!-- Students Tab -->
            <div v-if="activeTab === 'students'" class="space-y-6">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Class Students</h3>
                  <p class="text-gray-600 text-sm mt-1">Manage students in this class</p>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="relative">
                    <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    <input v-model="studentSearch" type="text" placeholder="Search students..."
                      class="pl-10 pr-4 py-2 w-64 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md font-medium" />
                  </div>
                  <select v-model="statusFilter"
                    class="border border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md font-medium">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                  <button @click="showBulkAssign = true"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg hover:shadow-xl transition-all duration-200"
                    :disabled="loading">
                    <Loader v-if="loading" class="w-4 h-4 mr-2 inline animate-spin" />
                    <Plus v-else class="w-4 h-4 mr-2 inline" />
                    Add Students
                  </button>
                </div>
              </div>

              <!-- Students Table -->
              <div class="bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-2xl overflow-hidden shadow-lg">
                <div v-if="loadingStudents" class="flex justify-center py-12">
                  <div class="text-center">
                    <Loader class="w-8 h-8 animate-spin text-blue-600 mx-auto mb-4" />
                    <p class="text-gray-500">Loading students...</p>
                  </div>
                </div>
                
                <div v-else-if="filteredStudents.length === 0" class="text-center py-12">
                  <Users class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <h3 class="text-lg font-bold text-gray-900 mb-2">No Students Found</h3>
                  <p class="text-gray-500 mb-4">
                    {{ studentSearch || statusFilter ? 'No students match your filters.' : 'No students enrolled in this class.' }}
                  </p>
                  <button @click="showBulkAssign = true"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-200">
                    <Plus class="w-4 h-4 mr-2 inline" />
                    Add Students
                  </button>
                </div>
                
                <table v-else class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Code</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Attendance</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white/60 backdrop-blur-sm divide-y divide-gray-200">
                    <tr v-for="student in filteredStudents" :key="student.id || student.user_id"
                      class="hover:bg-white/80 transition-colors duration-200">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <User class="w-5 h-5 text-white" />
                          </div>
                          <div class="ml-3">
                            <div class="text-sm font-bold text-gray-900">{{ formatStudentName(student) }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ formatStudentCode(student) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">{{ formatStudentEmail(student) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="[
                          'px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full shadow-sm capitalize',
                          formatStudentStatus(student) === 'active'
                            ? 'bg-gradient-to-r from-emerald-100 to-emerald-200 text-emerald-800'
                            : 'bg-gradient-to-r from-red-100 to-red-200 text-red-800'
                        ]">
                          {{ formatStudentStatus(student) }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-16 bg-gray-200 rounded-full h-2 mr-3 shadow-inner">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full shadow-sm transition-all duration-500"
                              :style="`width: ${formatAttendanceRate(student)}%`"></div>
                          </div>
                          <span class="text-sm font-bold text-gray-900 w-12">{{ formatAttendanceRate(student) }}%</span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <button @click="viewStudentProfile(student)"
                            class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <Eye class="w-4 h-4" />
                          </button>
                          <button class="p-2 text-emerald-600 hover:text-emerald-900 hover:bg-emerald-50 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <BookOpen class="w-4 h-4" />
                          </button>
                          <button class="p-2 text-purple-600 hover:text-purple-900 hover:bg-purple-50 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <Calendar class="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Grades Tab -->
            <div v-if="activeTab === 'grades'" class="space-y-6">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Class Grades</h3>
                  <p class="text-gray-600 text-sm mt-1">Manage student grades for this class</p>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="flex items-center space-x-2">
                    <div 
                      :class="[
                        'w-3 h-3 rounded-full',
                        isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'
                      ]"
                    ></div>
                    <span class="text-sm font-medium" :class="isConnected ? 'text-green-700' : 'text-red-700'">
                      {{ isConnected ? 'Live Updates Active' : 'Offline' }}
                    </span>
                  </div>
                  <button @click="openGradeModal()"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg hover:shadow-xl transition-all duration-200"
                    :disabled="!selectedClass || loadingGrades">
                    <Loader v-if="loadingGrades" class="w-4 h-4 mr-2 inline animate-spin" />
                    <Plus v-else class="w-4 h-4 mr-2 inline" />
                    Add Grade
                  </button>
                </div>
              </div>

              <!-- Grades Summary Cards -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 p-4 rounded-xl">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs text-blue-700 font-bold uppercase tracking-wide">Total Grades</p>
                      <p class="text-2xl font-bold text-blue-900">{{ classGrades.length }}</p>
                    </div>
                    <Award class="w-8 h-8 text-blue-600" />
                  </div>
                </div>
                <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 p-4 rounded-xl">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs text-green-700 font-bold uppercase tracking-wide">Average Grade</p>
                      <p class="text-2xl font-bold text-green-900">{{ calculateClassAverage() }}</p>
                    </div>
                    <TrendingUp class="w-8 h-8 text-green-600" />
                  </div>
                </div>
                <div class="bg-gradient-to-r from-amber-50 to-amber-100 border border-amber-200 p-4 rounded-xl">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs text-amber-700 font-bold uppercase tracking-wide">Recent Grades</p>
                      <p class="text-2xl font-bold text-amber-900">{{ getRecentGradesCount() }}</p>
                    </div>
                    <Clock class="w-8 h-8 text-amber-600" />
                  </div>
                </div>
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 p-4 rounded-xl">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs text-purple-700 font-bold uppercase tracking-wide">Students Graded</p>
                      <p class="text-2xl font-bold text-purple-900">{{ getUniqueStudentCount() }}</p>
                    </div>
                    <Users class="w-8 h-8 text-purple-600" />
                  </div>
                </div>
              </div>

              <!-- Grades Table -->
              <div class="bg-white/90 backdrop-blur-sm border border-gray-200/50 rounded-2xl overflow-hidden shadow-lg">
                <div v-if="loadingGrades" class="flex justify-center py-12">
                  <div class="text-center">
                    <Loader class="w-8 h-8 animate-spin text-blue-600 mx-auto mb-4" />
                    <p class="text-gray-500">Loading grades...</p>
                  </div>
                </div>
                
                <div v-else-if="classGrades.length === 0" class="text-center py-12">
                  <Award class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <h3 class="text-lg font-bold text-gray-900 mb-2">No Grades Found</h3>
                  <p class="text-gray-500 mb-4">No grades have been recorded for this class yet.</p>
                  <button @click="openGradeModal()"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-200">
                    <Plus class="w-4 h-4 mr-2 inline" />
                    Add First Grade
                  </button>
                </div>
                
                <div v-else>
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                      <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Assessment</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white/60 backdrop-blur-sm divide-y divide-gray-200">
                      <tr v-for="grade in classGrades" :key="grade.id"
                        class="hover:bg-white/80 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                              <User class="w-4 h-4 text-white" />
                            </div>
                            <div class="ml-3">
                              <div class="text-sm font-bold text-gray-900">{{ getStudentName(grade.student_id) }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ grade.subject || 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-800 capitalize">
                            {{ grade.assessment_type }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <span class="text-sm font-bold text-gray-900 mr-2">{{ grade.score_obtained }}/{{ grade.max_score }}</span>
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                              <div class="h-2 rounded-full transition-all duration-500"
                                :class="getScoreColorClass((grade.score_obtained / grade.max_score) * 100)"
                                :style="{ width: ((grade.score_obtained / grade.max_score) * 100) + '%' }"></div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span class="px-2 py-1 text-xs font-bold rounded-full"
                            :class="getGradeBadgeClass(grade.grade_letter)">
                            {{ grade.grade_letter || 'N/A' }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ formatDate(grade.created_at || grade.recorded_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <div class="flex space-x-2">
                            <button @click="openGradeModal(grade)"
                              class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-all duration-200">
                              <Edit3 class="w-4 h-4" />
                            </button>
                            <button @click="deleteGrade(grade.id)"
                              class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-all duration-200">
                              <Trash2 class="w-4 h-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Attendance Tab -->
            <div v-if="activeTab === 'attendance'" class="space-y-6">
              <AttendanceTracker :selected-class="selectedClass" />
            </div>

            <!-- Subjects Tab -->
            <div v-if="activeTab === 'subjects'" class="space-y-6">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Class Subjects</h3>
                  <p class="text-gray-600 text-sm mt-1">Subjects taught in this class</p>
                </div>
              </div>
              
              <div v-if="loading" class="flex justify-center py-12">
                <div class="text-center">
                  <Loader class="w-8 h-8 animate-spin text-blue-600 mx-auto mb-4" />
                  <p class="text-gray-500">Loading subjects...</p>
                </div>
              </div>
              
              <div v-else-if="currentClassSubjects.length === 0" class="text-center py-12">
                <div class="bg-gray-100 rounded-2xl p-8 max-w-md mx-auto">
                  <BookOpen class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <h3 class="text-lg font-bold text-gray-900 mb-2">No Subjects Assigned</h3>
                  <p class="text-gray-500">This class doesn't have any subjects assigned yet.</p>
                </div>
              </div>
              
              <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="subject in currentClassSubjects" :key="subject.id"
                  class="bg-white/90 backdrop-blur-sm border border-gray-200/50 rounded-2xl p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                  <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                      <div class="p-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                        <BookOpen class="w-5 h-5 text-white" />
                      </div>
                      <div>
                        <h4 class="font-bold text-gray-900 text-lg">{{ subject.subject?.subject_name || subject.name || 'Unknown Subject' }}</h4>
                        <p class="text-sm text-gray-600 font-medium">{{ subject.teacher?.first_name }} {{ subject.teacher?.last_name || 'Not Assigned' }}</p>
                      </div>
                    </div>
                    <span class="px-3 py-1 bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 text-xs font-bold rounded-full shadow-sm">
                      {{ subject.subject?.subject_code || subject.code || 'N/A' }}
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="p-3 bg-gray-50 rounded-xl">
                      <p class="text-xs text-gray-500 font-bold">Credit Hours</p>
                      <p class="text-lg font-bold text-gray-900">{{ subject.subject?.credit_hours || 'N/A' }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                      <p class="text-xs text-gray-500 font-bold">Type</p>
                      <p class="text-lg font-bold text-gray-900">{{ subject.subject?.is_core ? 'Core' : 'Elective' }}</p>
                    </div>
                  </div>
                  
                  <div class="flex space-x-2">
                    <button class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-3 py-2 rounded-xl text-sm font-bold shadow-lg hover:shadow-xl transition-all duration-200">
                      Gradebook
                    </button>
                    <button @click="activeTab = 'attendance'"
                      class="flex-1 bg-gradient-to-r from-amber-100 to-orange-200 hover:from-amber-200 hover:to-orange-300 text-orange-700 px-3 py-2 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all duration-200">
                      Attendance
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Schedule Tab -->
            <div v-if="activeTab === 'schedule'" class="space-y-6">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Weekly Schedule</h3>
                  <p class="text-gray-600 text-sm mt-1">Class timetable for the week</p>
                </div>
                <button class="flex items-center bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all duration-200">
                  <Printer class="w-4 h-4 mr-2" />
                  Print Schedule
                </button>
              </div>
              
              <div class="bg-white/90 backdrop-blur-sm border border-gray-200/50 rounded-2xl overflow-hidden shadow-lg">
                <table class="min-w-full border-collapse">
                  <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                      <th class="border border-gray-200 px-6 py-4 text-left text-sm font-bold text-gray-900">Time</th>
                      <th v-for="day in weekDays" :key="day"
                        class="border border-gray-200 px-6 py-4 text-left text-sm font-bold text-gray-900">
                        {{ day }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="period in periods" :key="period.time"
                      class="hover:bg-white/60 transition-colors duration-200">
                      <td class="border border-gray-200 px-6 py-4 text-sm font-bold text-gray-900 bg-gradient-to-r from-gray-50 to-gray-100">
                        {{ period.time }}
                      </td>
                      <td v-for="day in weekDays" :key="day" class="border border-gray-200 px-6 py-4 text-sm">
                        <div v-if="period.schedule[day]"
                          class="bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 px-3 py-2 rounded-lg text-xs font-bold shadow-sm">
                          {{ period.schedule[day] }}
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Performance Tab -->
            <div v-if="activeTab === 'performance'" class="space-y-6">
              <div>
                <h3 class="text-xl font-bold text-gray-900">Class Performance Analytics</h3>
                <p class="text-gray-600 text-sm mt-1">Overview of student performance and attendance</p>
              </div>
              
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Attendance Overview -->
                <div class="bg-white/90 backdrop-blur-sm border border-gray-200/50 rounded-2xl p-6 shadow-lg">
                  <h4 class="font-bold text-gray-900 mb-4 text-lg flex items-center">
                    <Calendar class="w-5 h-5 mr-2 text-amber-600" />
                    Attendance Overview
                  </h4>
                  <div v-if="loadingStats" class="flex justify-center py-8">
                    <Loader class="w-6 h-6 animate-spin text-blue-600" />
                  </div>
                  <div v-else class="space-y-4">
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                      <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-emerald-700 font-bold">Present</span>
                        <span class="text-lg font-bold text-emerald-800">{{ getClassAttendanceRate(selectedClass) }}%</span>
                      </div>
                      <div class="w-full bg-emerald-200 rounded-full h-2 shadow-inner">
                        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-2 rounded-full shadow-sm transition-all duration-500"
                          :style="`width: ${getClassAttendanceRate(selectedClass)}%`"></div>
                      </div>
                    </div>
                    <div class="p-4 bg-red-50 rounded-xl border border-red-200">
                      <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-red-700 font-bold">Absent</span>
                        <span class="text-lg font-bold text-red-800">{{ 100 - getClassAttendanceRate(selectedClass) }}%</span>
                      </div>
                      <div class="w-full bg-red-200 rounded-full h-2 shadow-inner">
                        <div class="bg-gradient-to-r from-red-500 to-red-600 h-2 rounded-full shadow-sm transition-all duration-500"
                          :style="`width: ${100 - getClassAttendanceRate(selectedClass)}%`"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Grade Distribution -->
                <div class="bg-white/90 backdrop-blur-sm border border-gray-200/50 rounded-2xl p-6 shadow-lg">
                  <h4 class="font-bold text-gray-900 mb-4 text-lg flex items-center">
                    <TrendingUp class="w-5 h-5 mr-2 text-purple-600" />
                    Grade Distribution
                  </h4>
                  <div v-if="loading" class="flex justify-center py-8">
                    <Loader class="w-6 h-6 animate-spin text-blue-600" />
                  </div>
                  <div v-else-if="gradeDistribution.length === 0" class="text-center py-8">
                    <TrendingUp class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                    <p class="text-gray-500 text-sm">No grade data available</p>
                  </div>
                  <div v-else class="space-y-3">
                    <div v-for="grade in gradeDistribution" :key="grade.grade"
                      class="p-3 bg-gray-50 rounded-xl">
                      <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-700 font-bold">Grade {{ grade.grade }}</span>
                        <div class="flex items-center space-x-2">
                          <span class="text-sm font-bold text-gray-900">{{ grade.count }} students</span>
                          <span class="text-xs text-gray-500">({{ grade.percentage }}%)</span>
                        </div>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2 shadow-inner">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full shadow-sm transition-all duration-500"
                          :style="`width: ${grade.percentage}%`"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Subject Performance -->
              <div class="bg-white/90 backdrop-blur-sm border border-gray-200/50 rounded-2xl p-6 shadow-lg">
                <h4 class="font-bold text-gray-900 mb-4 text-lg flex items-center">
                  <BookOpen class="w-5 h-5 mr-2 text-indigo-600" />
                  Subject Performance
                </h4>
                <div v-if="loading" class="flex justify-center py-8">
                  <Loader class="w-6 h-6 animate-spin text-blue-600" />
                </div>
                <div v-else-if="currentClassSubjects.length === 0" class="text-center py-8">
                  <BookOpen class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                  <p class="text-gray-500 text-sm">No subjects found</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div v-for="subject in currentClassSubjects" :key="subject.id"
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-md border border-gray-200/50 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center space-x-3">
                      <div class="p-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow">
                        <BookOpen class="w-4 h-4 text-white" />
                      </div>
                      <div>
                        <p class="font-bold text-gray-900">{{ subject.subject?.subject_name || subject.name || 'Unknown Subject' }}</p>
                        <p class="text-xs text-gray-600 font-medium">{{ subject.teacher?.first_name }} {{ subject.teacher?.last_name || 'Not Assigned' }}</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="text-xl font-bold text-gray-900">{{ subject.average_grade || 'N/A' }}</p>
                      <p class="text-xs text-gray-600 font-medium">{{ subject.subject?.credit_hours || 0 }} Credits</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Profile Modal -->
    <div v-if="selectedStudent"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white/95 backdrop-blur-lg rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-white/30">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex justify-between items-center">
            <h3 class="text-2xl font-bold text-gray-900">Student Profile</h3>
            <button @click="selectedStudent = null"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
              <X class="w-6 h-6" />
            </button>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
              <User class="w-8 h-8 text-white" />
            </div>
            <div>
              <h4 class="text-xl font-bold text-gray-900">{{ selectedStudent.name }}</h4>
              <p class="text-gray-600 font-medium">{{ selectedStudent.code }}</p>
              <p class="text-gray-600">{{ selectedStudent.email }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-xl shadow-md border border-gray-200">
              <p class="text-sm text-gray-600 font-bold uppercase tracking-wide">Status</p>
              <p class="font-bold text-lg capitalize">{{ selectedStudent.status }}</p>
            </div>
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-xl shadow-md border border-blue-200">
              <p class="text-sm text-gray-600 font-bold uppercase tracking-wide">Attendance Rate</p>
              <p class="font-bold text-lg">{{ selectedStudent.attendance }}%</p>
            </div>
          </div>
          <div class="flex space-x-3">
            <button class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-200">
              View Grades
            </button>
            <button class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-200">
              View Attendance
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Assign Students Modal -->
    <div v-if="showBulkAssign"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white/95 backdrop-blur-lg rounded-2xl max-w-lg w-full shadow-2xl border border-white/30">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-xl font-bold text-gray-900">Add Students to Class</h3>
              <p class="text-gray-600 text-sm mt-1">Select students to enroll</p>
            </div>
            <button @click="showBulkAssign = false"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-3">Available Students</label>
            <div v-if="loading" class="flex justify-center py-8">
              <Loader class="w-6 h-6 animate-spin text-blue-600" />
            </div>
            <div v-else-if="availableStudentsForClass.length === 0" class="text-center py-8">
              <Users class="w-12 h-12 text-gray-400 mx-auto mb-2" />
              <p class="text-gray-500 text-sm">No available students to add</p>
            </div>
            <div v-else class="max-h-64 overflow-y-auto bg-gray-50 rounded-xl p-4 space-y-3 border border-gray-200">
              <label v-for="student in availableStudentsForClass" :key="student.id || student.user_id"
                class="flex items-center space-x-3 p-3 hover:bg-white rounded-lg transition-colors duration-200 cursor-pointer">
                <input type="checkbox" :value="student.id || student.user_id" v-model="selectedStudentsForBulk"
                  class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                  <User class="w-4 h-4 text-white" />
                </div>
                <div class="flex-1">
                  <p class="text-sm font-bold text-gray-900">{{ formatStudentName(student) }}</p>
                  <p class="text-xs text-gray-500">{{ formatStudentCode(student) }}</p>
                </div>
              </label>
            </div>
          </div>
          <div class="flex space-x-3">
            <button @click="showBulkAssign = false"
              class="flex-1 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all duration-200">
              Cancel
            </button>
            <button @click="handleBulkAddStudents"
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-200"
              :disabled="loading || selectedStudentsForBulk.length === 0">
              <Loader v-if="loading" class="w-4 h-4 mr-2 inline animate-spin" />
              <Plus v-else class="w-4 h-4 mr-2 inline" />
              Add {{ selectedStudentsForBulk.length }} Student{{ selectedStudentsForBulk.length !== 1 ? 's' : '' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notes Modal -->
    <div v-if="showNotes" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white/95 backdrop-blur-lg rounded-2xl max-w-lg w-full shadow-2xl border border-white/30">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-xl font-bold text-gray-900">Class Notes</h3>
              <p class="text-gray-600 text-sm mt-1">Quick notes for this class</p>
            </div>
            <button @click="showNotes = false"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Add New Note</label>
            <textarea v-model="newNote" rows="3"
              class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md font-medium resize-none"
              placeholder="Enter your note about this class..."></textarea>
          </div>
          <div v-if="classNotes.length > 0" class="space-y-3 max-h-48 overflow-y-auto">
            <h4 class="text-sm font-bold text-gray-700">Recent Notes</h4>
            <div v-for="note in classNotes" :key="note.id"
              class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-xl shadow-md border border-blue-200">
              <p class="text-sm text-gray-900 font-medium mb-2">{{ note.content }}</p>
              <p class="text-xs text-blue-600 font-bold">{{ note.date }}</p>
            </div>
          </div>
          <div class="flex space-x-3">
            <button @click="showNotes = false"
              class="flex-1 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all duration-200">
              Close
            </button>
            <button @click="addNote" :disabled="!newNote.trim()"
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
              Add Note
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Grade Modal -->
    <div v-if="showGradeModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white/95 backdrop-blur-lg rounded-2xl max-w-lg w-full shadow-2xl border border-white/30">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-xl font-bold text-gray-900">
                {{ editingGrade ? 'Edit Grade' : 'Add New Grade' }}
              </h3>
              <p class="text-gray-600 text-sm mt-1">
                {{ selectedClass?.class_name || selectedClass?.name || 'Unknown Class' }}
              </p>
            </div>
            <button @click="closeGradeModal"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>
        <form @submit.prevent="submitGrade" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Student</label>
              <select v-model="gradeForm.student_id" required
                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md">
                <option value="">Select Student</option>
                <option v-for="student in classStudents" :key="student.id || student.user_id" 
                  :value="student.id || student.user_id">
                  {{ formatStudentName(student) }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Subject</label>
              <select v-model="gradeForm.subject" required
                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md">
                <option value="">Select Subject</option>
                <option v-for="subject in currentClassSubjects" :key="subject.id"
                  :value="subject.subject?.subject_name || subject.name">
                  {{ subject.subject?.subject_name || subject.name }}
                </option>
              </select>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Assessment Type</label>
              <select v-model="gradeForm.assessment_type" required
                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md">
                <option value="">Select Type</option>
                <option v-for="type in assessmentTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Max Score</label>
              <input v-model="gradeForm.max_score" type="number" min="1" max="1000" required
                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md">
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Score Obtained</label>
              <input v-model="gradeForm.score_obtained" type="number" min="0" :max="gradeForm.max_score" required
                @input="calculateGradeLetterInModal"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md">
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Grade Letter</label>
              <select v-model="gradeForm.grade_letter"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md">
                <option value="">Auto-calculate</option>
                <option v-for="letter in gradeLetters" :key="letter" :value="letter">{{ letter }}</option>
              </select>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Remarks (Optional)</label>
            <textarea v-model="gradeForm.remarks" rows="2"
              class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-md resize-none"
              placeholder="Add any remarks about this grade..."></textarea>
          </div>
          
          <div class="flex space-x-3 pt-4">
            <button type="button" @click="closeGradeModal"
              class="flex-1 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all duration-200">
              Cancel
            </button>
            <button type="submit" :disabled="loadingGrades"
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50">
              <Loader v-if="loadingGrades" class="w-4 h-4 mr-2 inline animate-spin" />
              {{ editingGrade ? 'Update Grade' : 'Add Grade' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Toast Notifications -->
    <ToastNotification />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  BookOpen, Users, Calendar, TrendingUp, MapPin, Clock, Plus, User,
  MoreHorizontal, ArrowLeft, StickyNote, Settings, Search, Download,
  Eye, Printer, X, RefreshCw, Loader, Edit3, Trash2, Award
} from 'lucide-vue-next'
import HeaderTeacher from '@/components/teacher/Classes/HeaderTeacher.vue'
import ToastNotification from '@/components/common/ToastNotification.vue'
import AttendanceTracker from '@/components/teacher/AttendanceTracker.vue'
import { useTeacherClasses } from '@/composables/useTeacherClasses'
import { useWebSocket } from '@/composables/useWebSocket'
import gradesApi from '@/api/grades'

// Use the teacher classes composable
const {
  // State
  classes,
  selectedClass,
  students,
  classStudents,
  classSubjects,
  terms,
  currentTerm,
  attendanceStats,
  gradeDistribution,
  classNotes,
  availableStudents,
  
  // Loading states
  loading,
  loadingClasses,
  loadingStudents,
  loadingStats,
  
  // Computed
  totalStudents,
  averageAttendance,
  averagePerformance,
  
  // Methods
  fetchClasses,
  selectClass,
  clearSelectedClass,
  bulkAddStudents,
  exportAttendance,
  initializeData
} = useTeacherClasses()

// WebSocket for real-time updates
const { connect, isConnected, send } = useWebSocket()

// Local reactive data
const activeTab = ref('students')
const selectedStudent = ref(null)
const showBulkAssign = ref(false)
const showNotes = ref(false)
const studentSearch = ref('')
const statusFilter = ref('')
const classSearch = ref('')
const selectedStudentsForBulk = ref([])
const newNote = ref('')

// Grade management state
const classGrades = ref([])
const loadingGrades = ref(false)
const showGradeModal = ref(false)
const editingGrade = ref(null)
const gradeForm = ref({
  student_id: '',
  subject: '',
  assessment_type: '',
  score_obtained: '',
  max_score: 100,
  grade_letter: '',
  remarks: ''
})

// Assessment types
const assessmentTypes = [
  { value: 'quiz', label: 'Quiz' },
  { value: 'exam', label: 'Exam' },
  { value: 'midterm', label: 'Midterm' },
  { value: 'final', label: 'Final' },
  { value: 'project', label: 'Project' },
  { value: 'assignment', label: 'Assignment' },
  { value: 'homework', label: 'Homework' },
  { value: 'participation', label: 'Participation' },
  { value: 'lab', label: 'Lab Test' }
]

const gradeLetters = ['A', 'B', 'C', 'D', 'E', 'F']

// Tabs configuration
const tabs = [
  { id: 'students', name: 'Students', icon: Users },
  { id: 'grades', name: 'Grades', icon: BookOpen },
  { id: 'attendance', name: 'Attendance', icon: Calendar },
  { id: 'subjects', name: 'Subjects', icon: BookOpen },
  { id: 'schedule', name: 'Schedule', icon: Clock },
  { id: 'performance', name: 'Performance', icon: TrendingUp }
]

// Mock schedule data
const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
const periods = [
  {
    time: '08:00-09:00',
    schedule: {
      Monday: 'Mathematics',
      Tuesday: 'Physics', 
      Wednesday: 'Mathematics',
      Thursday: 'Chemistry',
      Friday: 'Mathematics'
    }
  },
  {
    time: '09:00-10:00',
    schedule: {
      Monday: 'Physics',
      Tuesday: 'Mathematics',
      Wednesday: 'Chemistry',
      Thursday: 'Physics',
      Friday: 'Chemistry'
    }
  },
  {
    time: '10:00-11:00',
    schedule: {
      Monday: 'Chemistry',
      Tuesday: 'Chemistry',
      Wednesday: 'Physics',
      Thursday: 'Mathematics',
      Friday: 'Physics'
    }
  }
]

// Computed properties
const filteredStudents = computed(() => {
  if (!classStudents.value || !Array.isArray(classStudents.value)) return []
  let filtered = classStudents.value

  if (studentSearch.value) {
    filtered = filtered.filter(student =>
      (student.name || `${student.first_name || ''} ${student.last_name || ''}`)
        .toLowerCase().includes(studentSearch.value.toLowerCase()) ||
      (student.code || student.student_code || '')
        .toLowerCase().includes(studentSearch.value.toLowerCase())
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(student => 
      (student.status || (student.is_active ? 'active' : 'inactive')) === statusFilter.value
    )
  }

  return filtered
})

const filteredClasses = computed(() => {
  if (!classes.value || !Array.isArray(classes.value)) return []
  if (!classSearch.value) return classes.value
  
  return classes.value.filter(classItem =>
    (classItem.class_name || classItem.name || '')
      .toLowerCase().includes(classSearch.value.toLowerCase()) ||
    (classItem.room_number || '')
      .toLowerCase().includes(classSearch.value.toLowerCase())
  )
})

const availableStudentsForClass = computed(() => {
  if (!availableStudents.value || !Array.isArray(availableStudents.value)) return []
  if (!classStudents.value || !Array.isArray(classStudents.value)) return availableStudents.value
  
  const classStudentIds = new Set(classStudents.value.map(s => s.id || s.user_id))
  return availableStudents.value.filter(student => 
    !classStudentIds.has(student.id || student.user_id)
  )
})

const currentClassSubjects = computed(() => {
  if (!selectedClass.value || !classSubjects.value || !Array.isArray(classSubjects.value)) return []
  return classSubjects.value.filter(cs => cs.class_id === selectedClass.value.id)
})

// Grade management methods
const fetchClassGrades = async () => {
  if (!selectedClass.value) return
  
  loadingGrades.value = true
  try {
    // Mock data for now - replace with real API call
    const mockGrades = [
      {
        id: 1,
        student_id: classStudents.value[0]?.id || 1,
        subject: 'Mathematics',
        assessment_type: 'quiz',
        score_obtained: 85,
        max_score: 100,
        grade_letter: 'B',
        remarks: 'Good effort',
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        student_id: classStudents.value[1]?.id || 2,
        subject: 'Physics',
        assessment_type: 'exam',
        score_obtained: 92,
        max_score: 100,
        grade_letter: 'A',
        remarks: 'Excellent work',
        created_at: new Date(Date.now() - 86400000).toISOString()
      }
    ]
    
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 500))
    classGrades.value = mockGrades
    
  } catch (error) {
    console.error('Error fetching class grades:', error)
  } finally {
    loadingGrades.value = false
  }
}

const openGradeModal = (grade = null) => {
  editingGrade.value = grade
  if (grade) {
    gradeForm.value = {
      student_id: grade.student_id,
      subject: grade.subject,
      assessment_type: grade.assessment_type,
      score_obtained: grade.score_obtained,
      max_score: grade.max_score,
      grade_letter: grade.grade_letter,
      remarks: grade.remarks || ''
    }
  } else {
    gradeForm.value = {
      student_id: '',
      subject: '',
      assessment_type: '',
      score_obtained: '',
      max_score: 100,
      grade_letter: '',
      remarks: ''
    }
  }
  showGradeModal.value = true
}

const closeGradeModal = () => {
  showGradeModal.value = false
  editingGrade.value = null
  gradeForm.value = {
    student_id: '',
    subject: '',
    assessment_type: '',
    score_obtained: '',
    max_score: 100,
    grade_letter: '',
    remarks: ''
  }
}

const calculateGradeLetterInModal = () => {
  if (gradeForm.value.score_obtained === '' || gradeForm.value.max_score === '') return
  
  const percentage = (parseFloat(gradeForm.value.score_obtained) / parseFloat(gradeForm.value.max_score)) * 100
  
  if (percentage >= 90) gradeForm.value.grade_letter = 'A'
  else if (percentage >= 80) gradeForm.value.grade_letter = 'B'
  else if (percentage >= 70) gradeForm.value.grade_letter = 'C'
  else if (percentage >= 60) gradeForm.value.grade_letter = 'D'
  else if (percentage >= 50) gradeForm.value.grade_letter = 'E'
  else gradeForm.value.grade_letter = 'F'
}

const submitGrade = async () => {
  if (!selectedClass.value) return
  
  loadingGrades.value = true
  try {
    // Calculate grade letter if not manually set
    if (!gradeForm.value.grade_letter) {
      calculateGradeLetterInModal()
    }
    
    const gradeData = {
      ...gradeForm.value,
      class_id: selectedClass.value.id,
      recorded_by: 1, // Replace with actual teacher ID
      weightage: 1
    }
    
    if (editingGrade.value) {
      // Update existing grade
      const index = classGrades.value.findIndex(g => g.id === editingGrade.value.id)
      if (index !== -1) {
        classGrades.value[index] = {
          ...editingGrade.value,
          ...gradeData,
          updated_at: new Date().toISOString()
        }
      }
      
      // Broadcast update via WebSocket
      if (isConnected.value) {
        const updateMessage = {
          type: 'grade_updated',
          grade: classGrades.value[index],
          student_id: gradeData.student_id,
          teacher_id: 1,
          timestamp: new Date().toISOString(),
          metadata: {
            subject: gradeData.subject,
            assessment_type: gradeData.assessment_type,
            grade_letter: gradeData.grade_letter,
            score: `${gradeData.score_obtained}/${gradeData.max_score}`
          }
        }
        send(updateMessage)
      }
      
    } else {
      // Create new grade
      const newGrade = {
        id: Date.now(),
        ...gradeData,
        created_at: new Date().toISOString()
      }
      classGrades.value.unshift(newGrade)
      
      // Broadcast creation via WebSocket
      if (isConnected.value) {
        const updateMessage = {
          type: 'grade_created',
          grade: newGrade,
          student_id: gradeData.student_id,
          teacher_id: 1,
          timestamp: new Date().toISOString(),
          metadata: {
            subject: gradeData.subject,
            assessment_type: gradeData.assessment_type,
            grade_letter: gradeData.grade_letter,
            score: `${gradeData.score_obtained}/${gradeData.max_score}`
          }
        }
        send(updateMessage)
      }
    }
    
    closeGradeModal()
    
  } catch (error) {
    console.error('Error submitting grade:', error)
    alert('Failed to save grade. Please try again.')
  } finally {
    loadingGrades.value = false
  }
}

const deleteGrade = async (gradeId) => {
  if (!confirm('Are you sure you want to delete this grade?')) return
  
  const gradeToDelete = classGrades.value.find(g => g.id === gradeId)
  if (!gradeToDelete) return
  
  try {
    // Remove from local array
    classGrades.value = classGrades.value.filter(g => g.id !== gradeId)
    
    // Broadcast deletion via WebSocket
    if (isConnected.value) {
      const updateMessage = {
        type: 'grade_deleted',
        grade_id: gradeId,
        grade: gradeToDelete,
        student_id: gradeToDelete.student_id,
        teacher_id: 1,
        timestamp: new Date().toISOString(),
        metadata: {
          subject: gradeToDelete.subject,
          assessment_type: gradeToDelete.assessment_type,
          grade_letter: gradeToDelete.grade_letter
        }
      }
      send(updateMessage)
    }
    
  } catch (error) {
    console.error('Error deleting grade:', error)
    alert('Failed to delete grade. Please try again.')
  }
}

const getStudentName = (studentId) => {
  const student = classStudents.value.find(s => (s.id || s.user_id) === studentId)
  return student ? formatStudentName(student) : 'Unknown Student'
}

const calculateClassAverage = () => {
  if (classGrades.value.length === 0) return 'N/A'
  
  const totalPercentage = classGrades.value.reduce((sum, grade) => {
    return sum + ((grade.score_obtained / grade.max_score) * 100)
  }, 0)
  
  const average = totalPercentage / classGrades.value.length
  return `${average.toFixed(1)}%`
}

const getRecentGradesCount = () => {
  const oneDayAgo = new Date(Date.now() - 24 * 60 * 60 * 1000)
  return classGrades.value.filter(grade => 
    new Date(grade.created_at || grade.recorded_at) > oneDayAgo
  ).length
}

const getUniqueStudentCount = () => {
  const studentIds = new Set(classGrades.value.map(grade => grade.student_id))
  return studentIds.size
}

const getScoreColorClass = (percentage) => {
  if (percentage >= 90) return 'bg-green-500'
  if (percentage >= 80) return 'bg-blue-500'  
  if (percentage >= 70) return 'bg-yellow-500'
  if (percentage >= 60) return 'bg-orange-500'
  return 'bg-red-500'
}

const getGradeBadgeClass = (grade) => {
  const classes = {
    'A': 'bg-green-100 text-green-800',
    'B': 'bg-blue-100 text-blue-800',
    'C': 'bg-yellow-100 text-yellow-800',
    'D': 'bg-orange-100 text-orange-800',
    'E': 'bg-red-100 text-red-800',
    'F': 'bg-red-100 text-red-800'
  }
  return classes[grade] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

// Methods
const viewClassDetails = async (classItem) => {
  await selectClass(classItem)
  activeTab.value = 'students'
  await fetchClassGrades()
}

const viewStudents = async (classItem) => {
  await selectClass(classItem)
  activeTab.value = 'students'
  await fetchClassGrades()
}

const viewStudentProfile = (student) => {
  selectedStudent.value = student
}

const handleBulkAddStudents = async () => {
  if (!selectedClass.value || selectedStudentsForBulk.value.length === 0) return
  
  await bulkAddStudents(selectedClass.value.id, selectedStudentsForBulk.value)
  selectedStudentsForBulk.value = []
  showBulkAssign.value = false
}

const addNote = () => {
  if (newNote.value.trim()) {
    classNotes.value.unshift({
      id: Date.now(),
      content: newNote.value,
      date: new Date().toLocaleDateString()
    })
    newNote.value = ''
  }
}

const handleExportAttendance = async () => {
  const params = {}
  if (selectedClass.value) {
    params.class_id = selectedClass.value.id
  }
  if (currentTerm.value) {
    params.term_id = currentTerm.value.id
  }
  
  await exportAttendance(params)
}

const refreshData = async () => {
  await initializeData()
}

// Format helper functions
const formatStudentName = (student) => {
  return student.name || `${student.first_name || ''} ${student.last_name || ''}`.trim()
}

const formatStudentCode = (student) => {
  return student.code || student.student_code || 'N/A'
}

const formatStudentEmail = (student) => {
  return student.email || 'N/A'
}

const formatStudentStatus = (student) => {
  return student.status || (student.is_active ? 'active' : 'inactive')
}

const formatAttendanceRate = (student) => {
  return student.attendance || student.attendance_rate || 0
}

const getClassStudentCount = (classItem) => {
  return classItem.student_count || classItem.students?.length || 0
}

const getClassAttendanceRate = (classItem) => {
  return classItem.attendance_rate || classItem.attendanceRate || 87
}

const getClassAverageGrade = (classItem) => {
  return classItem.average_grade || classItem.avgGrade || 'B+'
}

const getClassRoom = (classItem) => {
  return classItem.room_number || classItem.room || 'TBA'
}

const getClassSchedule = (classItem) => {
  return classItem.schedule || 'Monday-Friday 8:00 AM - 3:00 PM'
}

const getClassSubjects = (classItem) => {
  if (classItem.subjects && Array.isArray(classItem.subjects)) {
    return classItem.subjects
  }
  if (classItem.class_subjects) {
    return classItem.class_subjects.map(cs => cs.subject?.subject_name || cs.name).filter(Boolean)
  }
  return []
}

// Initialize WebSocket connection on mount
onMounted(async () => {
  await connect()
})
</script>

<style scoped>
/* Custom scrollbar for modals */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 6px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 6px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Smooth transitions for all interactive elements */
button, input, select, textarea {
  transition: all 0.2s ease-in-out;
}

/* Card hover effects */
.group:hover {
  transform: translateY(-2px);
}

/* Progress bar animations */
@keyframes slideIn {
  from {
    width: 0%;
  }
  to {
    width: var(--final-width);
  }
}
</style>
