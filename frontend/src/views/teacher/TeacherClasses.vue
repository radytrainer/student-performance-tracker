<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header -->
    <HeaderTeacher />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Classes Overview Cards -->
      <div v-if="!selectedClass" class="space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div
            class="group bg-white/70 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div
                class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:shadow-blue-500/25 transition-shadow duration-300">
                <BookOpen class="w-7 h-7 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Classes</p>
                <p class="text-3xl font-bold text-gray-900">
                  <Loader v-if="loadingClasses" class="w-8 h-8 animate-spin" />
                  <span v-else>{{ classes.length }}</span>
                </p>
              </div>
            </div>
          </div>

          <div
            class="group bg-white/70 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div
                class="p-3 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-lg group-hover:shadow-emerald-500/25 transition-shadow duration-300">
                <Users class="w-7 h-7 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Students</p>
                <p class="text-3xl font-bold text-gray-900">
                  <Loader v-if="loadingClasses" class="w-8 h-8 animate-spin" />
                  <span v-else>{{ totalStudents }}</span>
                </p>
              </div>
            </div>
          </div>

          <div
            class="group bg-white/70 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div
                class="p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl shadow-lg group-hover:shadow-amber-500/25 transition-shadow duration-300">
                <Calendar class="w-7 h-7 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Avg Attendance</p>
                <p class="text-3xl font-bold text-gray-900">
                  <Loader v-if="loadingStats" class="w-8 h-8 animate-spin" />
                  <span v-else>{{ averageAttendance }}%</span>
                </p>
              </div>
            </div>
          </div>

          <div
            class="group bg-white/70 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-2xl hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
              <div
                class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:shadow-purple-500/25 transition-shadow duration-300">
                <TrendingUp class="w-7 h-7 text-white" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Avg Performance</p>
                <p class="text-3xl font-bold text-gray-900">
                  <Loader v-if="loadingClasses" class="w-8 h-8 animate-spin" />
                  <span v-else>{{ averagePerformance }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Classes Grid -->
        <div>
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-900">Your Classes</h3>
            <div class="flex space-x-3">
              <button @click="refreshData" 
                class="flex items-center bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
                Refresh
              </button>
              <button @click="handleExportAttendance"
                class="flex items-center bg-gradient-to-r from-amber-100 to-orange-200 hover:from-amber-200 hover:to-orange-300 text-orange-700 px-4 py-2 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                <Download class="w-4 h-4 mr-2" />
                Export
              </button>
            </div>
          </div>
          
          <div v-if="loadingClasses" class="flex justify-center py-12">
            <Loader class="w-8 h-8 animate-spin text-blue-600" />
          </div>
          
          <div v-else-if="classes.length === 0" class="text-center py-12">
            <BookOpen class="w-16 h-16 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-500 text-lg">No classes found</p>
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="classItem in classes" :key="classItem.id"
              class="group bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 hover:shadow-2xl hover:scale-105 transition-all duration-300 overflow-hidden">
              <div class="h-2 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
              <div class="p-6">
                <div class="flex items-start justify-between mb-6">
                  <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ classItem.class_name || classItem.name }}</h3>
                    <p class="text-sm text-gray-500 font-medium">{{ classItem.academic_year || classItem.academicYear || '2024-2025' }}</p>
                  </div>
                  <span
                    class="px-3 py-1 bg-gradient-to-r from-emerald-100 to-emerald-200 text-emerald-800 text-xs font-semibold rounded-full shadow-sm">Active</span>
                </div>

                <div class="space-y-3 mb-6">
                  <div class="flex items-center text-sm text-gray-600">
                    <div
                      class="w-8 h-8 bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg flex items-center justify-center mr-3">
                      <MapPin class="w-4 h-4 text-gray-500" />
                    </div>
                    <span class="font-medium">Room {{ getClassRoom(classItem) }}</span>
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <div
                      class="w-8 h-8 bg-gradient-to-r from-blue-100 to-blue-200 rounded-lg flex items-center justify-center mr-3">
                      <Users class="w-4 h-4 text-blue-500" />
                    </div>
                    <span class="font-medium">{{ getClassStudentCount(classItem) }} Students</span>
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <div
                      class="w-8 h-8 bg-gradient-to-r from-purple-100 to-purple-200 rounded-lg flex items-center justify-center mr-3">
                      <Clock class="w-4 h-4 text-purple-500" />
                    </div>
                    <span class="font-medium">{{ getClassSchedule(classItem) }}</span>
                  </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-6">
                  <span v-for="subject in getClassSubjects(classItem)" :key="subject"
                    class="px-3 py-1 bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 text-xs font-semibold rounded-full shadow-sm">
                    {{ subject }}
                  </span>
                  <span v-if="getClassSubjects(classItem).length === 0"
                    class="px-3 py-1 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-600 text-xs font-semibold rounded-full shadow-sm">
                    No subjects assigned
                  </span>
                </div>

                <div class="flex space-x-2">
                  <button @click="viewClassDetails(classItem)"
                    class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    View Details
                  </button>
                  <button @click="viewStudents(classItem)"
                    class="flex-1 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-3 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                    Students
                  </button>
                  <button
                    class="px-4 py-3 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                    <MoreHorizontal class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Class Detail Component -->
      <div v-if="selectedClass" class="space-y-8">
        <!-- Breadcrumb and Actions -->
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <button @click="clearSelectedClass"
              class="flex items-center text-gray-600 hover:text-gray-900 px-4 py-2 rounded-xl hover:bg-white/60 backdrop-blur-sm transition-all duration-200 shadow-md">
              <ArrowLeft class="w-5 h-5 mr-2" />
              Back to Classes
            </button>
            <div class="text-sm text-gray-500 font-medium">Classes / {{ selectedClass.class_name || selectedClass.name }}</div>
          </div>
          <div class="flex space-x-3">
            <button @click="showNotes = true"
              class="bg-gradient-to-r from-amber-100 to-orange-200 hover:from-amber-200 hover:to-orange-300 text-orange-700 px-4 py-2 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
              <StickyNote class="w-4 h-4 mr-2 inline" />
              Notes
            </button>
            <button
              class="bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
              <Settings class="w-4 h-4 mr-2 inline" />
              Settings
            </button>
          </div>
        </div>

        <!-- Class Header -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8">
          <div class="flex items-start justify-between mb-8">
            <div>
              <h2 class="text-4xl font-bold text-gray-900 mb-2">{{ selectedClass.class_name || selectedClass.name }}</h2>
              <p class="text-gray-600 text-lg font-medium">{{ selectedClass.academic_year || selectedClass.academicYear || '2024-2025' }} • Room {{ getClassRoom(selectedClass) }}</p>
            </div>
            <div
              class="text-right bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-md border border-white/20">
              <p class="text-sm text-gray-500 font-semibold uppercase tracking-wide mb-1">Class Teacher</p>
              <p class="font-bold text-gray-900 text-lg">{{ selectedClass.class_teacher?.first_name }} {{ selectedClass.class_teacher?.last_name || 'Not Assigned' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 p-6 rounded-2xl shadow-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-blue-700 font-bold text-sm uppercase tracking-wide mb-1">Students</p>
                  <p class="text-3xl font-bold text-blue-900">
                    <Loader v-if="loadingStudents" class="w-8 h-8 animate-spin" />
                    <span v-else>{{ getClassStudentCount(selectedClass) }}</span>
                  </p>
                </div>
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg">
                  <Users class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>

            <div
              class="bg-gradient-to-r from-amber-50 to-orange-100 border border-orange-200 p-6 rounded-2xl shadow-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-orange-700 font-bold text-sm uppercase tracking-wide mb-1">Attendance Rate</p>
                  <p class="text-3xl font-bold text-orange-900">
                    <Loader v-if="loadingStats" class="w-8 h-8 animate-spin" />
                    <span v-else>{{ getClassAttendanceRate(selectedClass) }}%</span>
                  </p>
                </div>
                <div class="p-3 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl shadow-lg">
                  <Calendar class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>

            <div
              class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 p-6 rounded-2xl shadow-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-purple-700 font-bold text-sm uppercase tracking-wide mb-1">Average Grade</p>
                  <p class="text-3xl font-bold text-purple-900">
                    <Loader v-if="loading" class="w-8 h-8 animate-spin" />
                    <span v-else>{{ getClassAverageGrade(selectedClass) }}</span>
                  </p>
                </div>
                <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg">
                  <TrendingUp class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20">
          <div class="border-b border-gray-200/50">
            <nav class="flex space-x-8 px-8">
              <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                'py-6 px-2 border-b-2 font-semibold text-sm flex items-center space-x-2 transition-all duration-200',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]">
                <component :is="tab.icon" class="w-5 h-5" />
                <span>{{ tab.name }}</span>
              </button>
            </nav>
          </div>

          <div class="p-8">
            <!-- Students Tab -->
            <div v-if="activeTab === 'students'" class="space-y-8">
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                  <div class="relative">
                    <Search class="w-5 h-5 absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    <input v-model="studentSearch" type="text" placeholder="Search students..."
                      class="pl-12 w-80 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/60 backdrop-blur-sm shadow-md font-medium" />
                  </div>
                  <select v-model="statusFilter"
                    class="border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/60 backdrop-blur-sm shadow-md font-medium">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="flex space-x-3">
                  <button
                    class="bg-gradient-to-r from-amber-100 to-orange-200 hover:from-amber-200 hover:to-orange-300 text-orange-700 px-4 py-3 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                    <Download class="w-4 h-4 mr-2 inline" />
                    Export
                  </button>
                  <button @click="showBulkAssign = true"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                    :disabled="loading">
                    <Loader v-if="loading" class="w-4 h-4 mr-2 inline animate-spin" />
                    <Plus v-else class="w-4 h-4 mr-2 inline" />
                    Add Student
                  </button>
                </div>
              </div>

              <div class="bg-white/60 backdrop-blur-sm border border-gray-200 rounded-2xl overflow-hidden shadow-lg">
                <div v-if="loadingStudents" class="flex justify-center py-12">
                  <Loader class="w-8 h-8 animate-spin text-blue-600" />
                </div>
                
                <div v-else-if="filteredStudents.length === 0" class="text-center py-12">
                  <Users class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                  <p class="text-gray-500 text-lg">No students found</p>
                </div>
                
                <table v-else class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                      <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student
                      </th>
                      <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Code</th>
                      <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email
                      </th>
                      <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status
                      </th>
                      <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Attendance</th>
                      <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white/40 backdrop-blur-sm divide-y divide-gray-200">
                    <tr v-for="student in filteredStudents" :key="student.id || student.user_id"
                      class="hover:bg-white/60 transition-colors duration-200">
                      <td class="px-8 py-6 whitespace-nowrap">
                        <div class="flex items-center">
                          <div
                            class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <User class="w-6 h-6 text-white" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-bold text-gray-900">{{ formatStudentName(student) }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-8 py-6 whitespace-nowrap text-sm font-bold text-gray-900">{{ formatStudentCode(student) }}</td>
                      <td class="px-8 py-6 whitespace-nowrap text-sm text-gray-600 font-medium">{{ formatStudentEmail(student) }}</td>
                      <td class="px-8 py-6 whitespace-nowrap">
                        <span :class="[
                          'px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full shadow-sm capitalize',
                          formatStudentStatus(student) === 'active'
                            ? 'bg-gradient-to-r from-emerald-100 to-emerald-200 text-emerald-800'
                            : 'bg-gradient-to-r from-red-100 to-red-200 text-red-800'
                        ]">
                          {{ formatStudentStatus(student) }}
                        </span>
                      </td>
                      <td class="px-8 py-6 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-20 bg-gray-200 rounded-full h-3 mr-3 shadow-inner">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full shadow-sm"
                              :style="`width: ${formatAttendanceRate(student)}%`"></div>
                          </div>
                          <span class="text-sm font-bold text-gray-900">{{ formatAttendanceRate(student) }}%</span>
                        </div>
                      </td>
                      <td class="px-8 py-6 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <button @click="viewStudentProfile(student)"
                            class="text-blue-600 hover:text-blue-900 hover:bg-blue-50 p-2 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <Eye class="w-5 h-5" />
                          </button>
                          <button
                            class="text-emerald-600 hover:text-emerald-900 hover:bg-emerald-50 p-2 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <BookOpen class="w-5 h-5" />
                          </button>
                          <button
                            class="text-purple-600 hover:text-purple-900 hover:bg-purple-50 p-2 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            <Calendar class="w-5 h-5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Attendance Tab -->
            <div v-if="activeTab === 'attendance'" class="space-y-6">
              <AttendanceTracker :selected-class="selectedClass" />
            </div>

            <!-- Subjects Tab -->
            <div v-if="activeTab === 'subjects'" class="space-y-8">
              <div v-if="loading" class="flex justify-center py-12">
                <Loader class="w-8 h-8 animate-spin text-blue-600" />
              </div>
              
              <div v-else-if="currentClassSubjects.length === 0" class="text-center py-12">
                <BookOpen class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500 text-lg">No subjects assigned</p>
              </div>
              
              <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="subject in currentClassSubjects" :key="subject.id"
                  class="bg-white/60 backdrop-blur-sm border border-gray-200 rounded-2xl p-8 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                  <div class="flex items-start justify-between mb-6">
                    <div>
                      <h4 class="font-bold text-gray-900 text-xl mb-1">{{ subject.subject?.subject_name || subject.name || 'Unknown Subject' }}</h4>
                      <p class="text-sm text-gray-600 font-medium">{{ subject.teacher?.first_name }} {{ subject.teacher?.last_name || 'Not Assigned' }}</p>
                    </div>
                    <span
                      class="px-3 py-1 bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 text-xs font-bold rounded-full shadow-sm">
                      {{ subject.subject?.subject_code || subject.code || 'N/A' }}
                    </span>
                  </div>
                  <div class="space-y-4 mb-8">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 font-medium">Credit Hours:</span>
                      <span class="font-bold text-gray-900">{{ subject.subject?.credit_hours || 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 font-medium">Department:</span>
                      <span class="font-bold text-gray-900">{{ subject.subject?.department || 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 font-medium">Type:</span>
                      <span class="font-bold text-gray-900">{{ subject.subject?.is_core ? 'Core' : 'Elective' }}</span>
                    </div>
                  </div>
                  <div class="flex space-x-3">
                    <button
                      class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                      Gradebook
                    </button>
                    <button
                      @click="activeTab = 'attendance'"
                      class="flex-1 bg-gradient-to-r from-amber-100 to-orange-200 hover:from-amber-200 hover:to-orange-300 text-orange-700 px-4 py-3 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                      Attendance
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Schedule Tab -->
            <div v-if="activeTab === 'schedule'" class="space-y-8">
              <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-gray-900">Weekly Schedule</h3>
                <button
                  class="bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-4 py-3 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                  <Printer class="w-4 h-4 mr-2 inline" />
                  Print
                </button>
              </div>
              <div class="bg-white/60 backdrop-blur-sm border border-gray-200 rounded-2xl overflow-hidden shadow-lg">
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
                      <td
                        class="border border-gray-200 px-6 py-4 text-sm font-bold text-gray-900 bg-gradient-to-r from-gray-50 to-gray-100">
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
            <div v-if="activeTab === 'performance'" class="space-y-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white/60 backdrop-blur-sm border border-gray-200 rounded-2xl p-8 shadow-lg">
                  <h4 class="font-bold text-gray-900 mb-6 text-xl">Attendance Overview</h4>
                  <div v-if="loadingStats" class="flex justify-center py-8">
                    <Loader class="w-6 h-6 animate-spin text-blue-600" />
                  </div>
                  <div v-else class="space-y-6">
                    <div class="flex justify-between items-center">
                      <span class="text-sm text-gray-600 font-medium">Present</span>
                      <span class="text-sm font-bold text-emerald-600">{{ getClassAttendanceRate(selectedClass) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                      <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-3 rounded-full shadow-sm"
                        :style="`width: ${getClassAttendanceRate(selectedClass)}%`"></div>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-sm text-gray-600 font-medium">Absent</span>
                      <span class="text-sm font-bold text-red-600">{{ 100 - getClassAttendanceRate(selectedClass) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                      <div class="bg-gradient-to-r from-red-500 to-red-600 h-3 rounded-full shadow-sm"
                        :style="`width: ${100 - getClassAttendanceRate(selectedClass)}%`"></div>
                    </div>
                  </div>
                </div>

                <div class="bg-white/60 backdrop-blur-sm border border-gray-200 rounded-2xl p-8 shadow-lg">
                  <h4 class="font-bold text-gray-900 mb-6 text-xl">Grade Distribution</h4>
                  <div v-if="loading" class="flex justify-center py-8">
                    <Loader class="w-6 h-6 animate-spin text-blue-600" />
                  </div>
                  <div v-else-if="gradeDistribution.length === 0" class="text-center py-8">
                    <TrendingUp class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                    <p class="text-gray-500 text-sm">No grade data available</p>
                  </div>
                  <div v-else class="space-y-4">
                    <div v-for="grade in gradeDistribution" :key="grade.grade"
                      class="flex justify-between items-center">
                      <span class="text-sm text-gray-600 font-medium">Grade {{ grade.grade }}</span>
                      <div class="flex items-center space-x-4">
                        <div class="w-24 bg-gray-200 rounded-full h-3 shadow-inner">
                          <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full shadow-sm"
                            :style="`width: ${grade.percentage}%`"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-900 w-8">{{ grade.count }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white/60 backdrop-blur-sm border border-gray-200 rounded-2xl p-8 shadow-lg">
                <h4 class="font-bold text-gray-900 mb-6 text-xl">Subject Performance</h4>
                <div v-if="loading" class="flex justify-center py-8">
                  <Loader class="w-6 h-6 animate-spin text-blue-600" />
                </div>
                <div v-else-if="currentClassSubjects.length === 0" class="text-center py-8">
                  <BookOpen class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                  <p class="text-gray-500 text-sm">No subjects found</p>
                </div>
                <div v-else class="space-y-6">
                  <div v-for="subject in currentClassSubjects" :key="subject.id"
                    class="flex items-center justify-between p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl shadow-md border border-white/20">
                    <div>
                      <p class="font-bold text-gray-900 text-lg">{{ subject.subject?.subject_name || subject.name || 'Unknown Subject' }}</p>
                      <p class="text-sm text-gray-600 font-medium">{{ subject.teacher?.first_name }} {{ subject.teacher?.last_name || 'Not Assigned' }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-2xl font-bold text-gray-900">{{ subject.average_grade || 'N/A' }}</p>
                      <p class="text-sm text-gray-600 font-medium">{{ subject.subject?.credit_hours || 0 }} Credits</p>
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
      <div
        class="bg-white/90 backdrop-blur-lg rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-white/20">
        <div class="p-8 border-b border-gray-200/50">
          <div class="flex justify-between items-center">
            <h3 class="text-2xl font-bold text-gray-900">Student Profile</h3>
            <button @click="selectedStudent = null"
              class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
              <X class="w-6 h-6" />
            </button>
          </div>
        </div>
        <div class="p-8 space-y-8">
          <div class="flex items-center space-x-6">
            <div
              class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
              <User class="w-10 h-10 text-white" />
            </div>
            <div>
              <h4 class="text-2xl font-bold text-gray-900">{{ selectedStudent.name }}</h4>
              <p class="text-gray-600 font-medium text-lg">{{ selectedStudent.code }}</p>
              <p class="text-gray-600 font-medium">{{ selectedStudent.email }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-2xl shadow-md">
              <p class="text-sm text-gray-600 font-semibold uppercase tracking-wide">Status</p>
              <p class="font-bold text-lg capitalize">{{ selectedStudent.status }}</p>
            </div>
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-2xl shadow-md">
              <p class="text-sm text-gray-600 font-semibold uppercase tracking-wide">Attendance Rate</p>
              <p class="font-bold text-lg">{{ selectedStudent.attendance }}%</p>
            </div>
          </div>
          <div class="flex space-x-4">
            <button
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
              View Grades
            </button>
            <button
              class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
              View Attendance
            </button>
            <button
              class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
              Export Info
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Assign Students Modal -->
    <div v-if="showBulkAssign"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white/90 backdrop-blur-lg rounded-2xl max-w-md w-full shadow-2xl border border-white/20">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Add Students</h3>
            <button @click="showBulkAssign = false"
              class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-3">Select Students</label>
            <div v-if="loading" class="flex justify-center py-8">
              <Loader class="w-6 h-6 animate-spin text-blue-600" />
            </div>
            <div v-else-if="availableStudentsForClass.length === 0" class="text-center py-8">
              <Users class="w-12 h-12 text-gray-400 mx-auto mb-2" />
              <p class="text-gray-500 text-sm">No available students</p>
            </div>
            <div v-else class="max-h-48 overflow-y-auto bg-gray-50/50 rounded-xl p-3 space-y-2 border border-gray-200">
              <label v-for="student in availableStudentsForClass" :key="student.id || student.user_id"
                class="flex items-center space-x-3 p-2 hover:bg-white/60 rounded-lg transition-colors duration-200">
                <input type="checkbox" :value="student.id || student.user_id" v-model="selectedStudentsForBulk"
                  class="rounded text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium">{{ formatStudentName(student) }} ({{ formatStudentCode(student) }})</span>
              </label>
            </div>
          </div>
          <div class="flex space-x-3">
            <button @click="showBulkAssign = false"
              class="flex-1 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold shadow-md hover:shadow-lg transition-all duration-200">
              Cancel
            </button>
            <button @click="handleBulkAddStudents"
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
              :disabled="loading || selectedStudentsForBulk.length === 0">
              <Loader v-if="loading" class="w-4 h-4 mr-2 inline animate-spin" />
              Add Students ({{ selectedStudentsForBulk.length }})
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notes Modal -->
    <div v-if="showNotes" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white/90 backdrop-blur-lg rounded-2xl max-w-lg w-full shadow-2xl border border-white/20">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Class Notes</h3>
            <button @click="showNotes = false"
              class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-3">Add Note</label>
            <textarea v-model="newNote" rows="4"
              class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/60 backdrop-blur-sm shadow-md font-medium resize-none"
              placeholder="Enter your note here..."></textarea>
          </div>
          <div class="space-y-3 max-h-48 overflow-y-auto">
            <div v-for="note in classNotes" :key="note.id"
              class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-xl shadow-md border border-white/20">
              <p class="text-sm text-gray-900 font-medium">{{ note.content }}</p>
              <p class="text-xs text-gray-500 mt-2 font-medium">{{ note.date }}</p>
            </div>
          </div>
          <div class="flex space-x-3">
            <button @click="showNotes = false"
              class="flex-1 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold shadow-md hover:shadow-lg transition-all duration-200">
              Close
            </button>
            <button @click="addNote"
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200">
              Add Note
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Toast Notifications -->
    <ToastNotification />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  BookOpen, Users, Calendar, TrendingUp, MapPin, Clock, Plus, User,
  MoreHorizontal, ArrowLeft, StickyNote, Settings, Search, Download,
  Eye, Printer, X, RefreshCw, Loader
} from 'lucide-vue-next'
import HeaderTeacher from '@/components/teacher/Classes/HeaderTeacher.vue'
import ToastNotification from '@/components/common/ToastNotification.vue'
import AttendanceTracker from '@/components/teacher/AttendanceTracker.vue'
import { useTeacherClasses } from '@/composables/useTeacherClasses'

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

// Local reactive data
const activeTab = ref('students')
const selectedStudent = ref(null)
const showBulkAssign = ref(false)
const showNotes = ref(false)
const showClassModal = ref(false)
const showGradeModal = ref(false)
const studentSearch = ref('')
const statusFilter = ref('')
const selectedStudentsForBulk = ref([])
const newNote = ref('')

// Form data
const classForm = ref({
  name: '',
  academic_year: new Date().getFullYear() + '-' + (new Date().getFullYear() + 1),
  room_number: '',
  schedule: ''
})

const gradeForm = ref({
  student_id: null,
  class_subject_id: null,
  term_id: null,
  assessment_type: 'exam',
  max_score: 100,
  score_obtained: 0,
  remarks: ''
})

// Tabs configuration
const tabs = [
  { id: 'students', name: 'Students', icon: Users },
  { id: 'attendance', name: 'Attendance', icon: Calendar },
  { id: 'subjects', name: 'Subjects', icon: BookOpen },
  { id: 'schedule', name: 'Schedule', icon: Clock },
  { id: 'performance', name: 'Performance', icon: TrendingUp }
]

// Mock schedule data (until backend endpoint is implemented)
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
  if (!classStudents.value) return []
  let filtered = classStudents.value

  if (studentSearch.value) {
    filtered = filtered.filter(student =>
      (student.name || student.first_name + ' ' + student.last_name)
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

const availableStudentsForClass = computed(() => {
  if (!availableStudents.value || !classStudents.value) return availableStudents.value || []
  
  const classStudentIds = new Set(classStudents.value.map(s => s.id || s.user_id))
  return availableStudents.value.filter(student => 
    !classStudentIds.has(student.id || student.user_id)
  )
})

const currentClassSubjects = computed(() => {
  if (!selectedClass.value || !classSubjects.value) return []
  return classSubjects.value.filter(cs => cs.class_id === selectedClass.value.id)
})

// Methods
const viewClassDetails = async (classItem) => {
  await selectClass(classItem)
  activeTab.value = 'students'
}

const viewStudents = async (classItem) => {
  await selectClass(classItem)
  activeTab.value = 'students'
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
      date: new Date().toISOString().split('T')[0]
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
  return classItem.room_number || classItem.room || 'N/A'
}

const getClassSchedule = (classItem) => {
  return classItem.schedule || 'Mon-Fri 8:00-14:00'
}

const getClassSubjects = (classItem) => {
  if (classItem.subjects && Array.isArray(classItem.subjects)) {
    return classItem.subjects
  }
  if (classItem.class_subjects) {
    return classItem.class_subjects.map(cs => cs.subject?.name || cs.name).filter(Boolean)
  }
  return []
}
</script>