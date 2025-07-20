<template>
  <div class="">
    <!-- Modern Header with Profile Overview -->
    <header class="backdrop-blur-xl border-b border-gray-100 sticky ">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center py-6">
          <!-- Welcome Message & Profile Overview -->
          <div class="flex items-center space-x-4">           
            <div>
              <h1 class="text-2xl font-bold text-purple-600 tracking-tight">Welcome back, {{ student.name }}!</h1>
              <p class="text-sm text-gray-600 font-medium">{{ student.course }} • ID: {{ student.studentId }}</p>
              <p class="text-xs text-gray-500">Last login: {{ student.lastLogin }}</p>
            </div>
          </div>          
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- System Summary Widgets -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-10">
        <div class="group bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
              <BookOpenIcon class="w-6 h-6" />
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold">{{ summaryStats.totalCourses }}</p>
              <p class="text-blue-100 text-sm font-medium">Total Courses</p>
            </div>
          </div>
        </div>

        <div class="group bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
              <ClipboardCheckIcon class="w-6 h-6" />
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold">{{ summaryStats.assignmentsSubmitted }}/{{ summaryStats.totalAssignments }}</p>
              <p class="text-emerald-100 text-sm font-medium">Assignments</p>
            </div>
          </div>
        </div>

        <div class="group bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
              <UserCheckIcon class="w-6 h-6" />
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold">{{ summaryStats.attendanceRate }}%</p>
              <p class="text-purple-100 text-sm font-medium">Attendance</p>
            </div>
          </div>
        </div>

        <div class="group bg-gradient-to-br from-amber-500 to-orange-500 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
              <TrendingUpIcon class="w-6 h-6" />
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold">{{ summaryStats.currentGPA }}</p>
              <p class="text-amber-100 text-sm font-medium">Current GPA</p>
            </div>
          </div>
        </div>

        <div class="group bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-3xl p-6 text-white hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
              <ClockIcon class="w-6 h-6" />
            </div>
            <div class="text-right">
              <p class="text-xl font-bold">{{ summaryStats.lastLogin }}</p>
              <p class="text-indigo-100 text-sm font-medium">Last Login</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
        <!-- Main Content Area -->
        <div class="xl:col-span-3 space-y-8">
          <!-- Class Schedule / Timetable -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-2xl font-bold text-gray-900 mb-2">Weekly Class Schedule</h2>
                  <p class="text-gray-500">{{ getCurrentWeek() }}</p>
                </div>
                <div class="flex space-x-2">
                  <button class="px-4 py-2 bg-blue-100 text-blue-600 rounded-xl text-sm font-medium hover:bg-blue-200 transition-colors">
                    This Week
                  </button>
                  <button class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-xl text-sm font-medium transition-colors">
                    Next Week
                  </button>
                </div>
              </div>
            </div>
            
            <div class="p-8">
              <div class="space-y-4">
                <div v-for="class_ in weeklySchedule" :key="class_.id" 
                     class="group flex items-center justify-between p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-100 hover:shadow-lg hover:border-gray-200 transition-all duration-300">
                  <div class="flex items-center space-x-6">
                    <div class="text-center min-w-[100px]">
                      <p class="text-lg font-bold text-gray-900">{{ class_.time }}</p>
                      <p class="text-sm text-gray-500">{{ class_.day }}</p>
                    </div>
                    <div class="w-px h-12 bg-gray-200"></div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ class_.subject }}</h3>
                      <p class="text-gray-600 font-medium">{{ class_.teacher }}</p>
                      <p class="text-sm text-gray-500">{{ class_.room }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    
                    <span :class="getStatusColor(class_.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                      {{ class_.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Academic Progress / Grades -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Academic Progress & Grades</h2>
                <div class="flex items-center space-x-2">
                  <span class="text-sm text-gray-500">Overall GPA:</span>
                  <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    {{ summaryStats.currentGPA }}
                  </span>
                </div>
              </div>
            </div>
            
            <div class="p-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="subject in academicProgress" :key="subject.id" 
                     class="group p-6 bg-gradient-to-br from-white to-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ subject.name }}</h3>
                    <span class="text-2xl font-bold" :class="getGradeColor(subject.grade)">{{ subject.grade }}</span>
                  </div>
                  <div class="relative">
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                      <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full transition-all duration-500 ease-out" 
                           :style="`width: ${subject.progress}%`"></div>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 font-medium">{{ subject.progress }}% Complete</span>
                      <span class="text-gray-500">Recent: {{ subject.recentScore }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Upcoming Assignments & Exams -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-100">
              <h2 class="text-2xl font-bold text-gray-900">Upcoming Assignments & Exams</h2>
            </div>
            
            <div class="p-8">
              <div class="space-y-4">
                <div v-for="item in upcomingItems" :key="item.id" 
                     class="group flex items-center justify-between p-6 bg-gradient-to-r from-white to-gray-50 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300">
                  <div class="flex items-center space-x-4">
                    <div :class="item.type === 'exam' ? 'bg-gradient-to-br from-red-500 to-pink-500' : 'bg-gradient-to-br from-blue-500 to-indigo-500'" 
                         class="p-3 rounded-2xl text-white shadow-lg">
                      <component :is="item.type === 'exam' ? 'ClipboardCheckIcon' : 'DocumentTextIcon'" class="w-6 h-6" />
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-900">{{ item.title }}</h3>
                      <p class="text-gray-600 font-medium">{{ item.subject }}</p>
                      <p class="text-sm text-gray-500">Due: {{ item.dueDate }}</p>
                    </div>
                  </div>
                  <div class="text-right space-y-2">
                    <span :class="getPriorityColor(item.priority)" class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
                      {{ item.priority }}
                    </span>
                    <p class="text-sm text-gray-500">{{ item.timeLeft }}</p>
                    <span :class="getSubmissionColor(item.submissionStatus)" class="text-xs font-medium">
                      {{ item.submissionStatus }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Feedback & Evaluation -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-100">
              <h2 class="text-2xl font-bold text-gray-900">Feedback & Evaluation</h2>
            </div>
            
            <div class="p-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <h3 class="text-lg font-bold text-gray-900">Recent Feedback</h3>
                  <div v-for="feedback in recentFeedback" :key="feedback.id" 
                       class="p-4 bg-gray-50 rounded-2xl">
                    <h4 class="font-semibold text-gray-900">{{ feedback.subject }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ feedback.comment }}</p>
                    <p class="text-xs text-gray-500 mt-2">- {{ feedback.teacher }}</p>
                  </div>
                </div>
                
                <div class="space-y-4">
                  <h3 class="text-lg font-bold text-gray-900">Evaluation Options</h3>
                  <button class="w-full p-4 bg-blue-50 text-blue-700 rounded-2xl hover:bg-blue-100 transition-colors">
                    <StarIcon class="w-5 h-5 inline mr-2" />
                    Evaluate Teachers
                  </button>
                  <button class="w-full p-4 bg-gray-50 text-gray-700 rounded-2xl hover:bg-gray-100 transition-colors">
                    <EyeIcon class="w-5 h-5 inline mr-2" />
                    View Past Evaluations
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
          <!-- Notifications / Announcements -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Notifications</h3>
                <span class="bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
                  {{ unreadNotifications }} new
                </span>
              </div>
            </div>
            
            <div class="p-6">
              <div class="space-y-4">
                <div v-for="notification in notifications.slice(0, 4)" :key="notification.id" 
                     class="flex items-start space-x-3 p-4 rounded-2xl hover:bg-gray-50 transition-colors cursor-pointer">
                  <div :class="getNotificationColor(notification.type)" class="p-2 rounded-xl">
                    <component :is="getNotificationIcon(notification.type)" class="w-4 h-4" />
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">{{ notification.title }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ notification.message }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ notification.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Attendance Record -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h3 class="text-xl font-bold text-gray-900">Attendance Record</h3>
            </div>
            
            <div class="p-6">
              <div class="space-y-4">
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-100">
                  <span class="text-sm font-medium text-gray-700">This Month</span>
                  <span class="text-xl font-bold text-green-600">{{ attendanceRecord.thisMonth }}%</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl border border-red-100">
                  <span class="text-sm font-medium text-gray-700">Absences</span>
                  <span class="text-xl font-bold text-red-600">{{ attendanceRecord.totalAbsences }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-2xl border border-yellow-100">
                  <span class="text-sm font-medium text-gray-700">Late Check-ins</span>
                  <span class="text-xl font-bold text-yellow-600">{{ attendanceRecord.lateCheckins }}</span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100">
                  <span class="text-sm font-medium text-gray-700">Weekly Average</span>
                  <span class="text-xl font-bold text-blue-600">{{ attendanceRecord.weeklyAverage }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Resources / Materials -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h3 class="text-xl font-bold text-gray-900">Resources & Materials</h3>
            </div>
            
            <div class="p-6">
              <div class="space-y-3">
                <a v-for="resource in resources" :key="resource.id" href="#" 
                   class="flex items-center space-x-3 p-4 rounded-2xl hover:bg-gray-50 transition-all duration-200 group">
                  <div class="p-2 bg-gray-100 rounded-xl group-hover:bg-blue-100 transition-colors">
                    <component :is="getResourceIcon(resource.type)" class="w-5 h-5 text-gray-500 group-hover:text-blue-500" />
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">{{ resource.title }}</p>
                    <p class="text-xs text-gray-500">{{ resource.subject }}</p>
                  </div>
                  <DownloadIcon class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" />
                </a>
              </div>
              
              <button class="w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl font-medium hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                <LibraryIcon class="w-4 h-4 inline mr-2" />
                Access Library
              </button>
            </div>
          </div>

          <!-- Support & Contact -->
          <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h3 class="text-xl font-bold text-gray-900">Support & Contact</h3>
            </div>
            
            <div class="p-6">
              <div class="space-y-3">
                <button class="w-full flex items-center justify-center space-x-2 px-4 py-4 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl font-medium hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                  <MessageCircleIcon class="w-5 h-5" />
                  <span>Submit Help Ticket</span>
                </button>
                
                <div class="grid grid-cols-2 gap-3">
                  <button class="flex items-center justify-center space-x-2 px-3 py-3 border border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors">
                    <PhoneIcon class="w-4 h-4" />
                    <span class="text-sm">Call</span>
                  </button>
                  <button class="flex items-center justify-center space-x-2 px-3 py-3 border border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-colors">
                    <MailIcon class="w-4 h-4" />
                    <span class="text-sm">Email</span>
                  </button>
                </div>
                
                <div class="text-center pt-4 border-t border-gray-100">
                  <p class="text-xs text-gray-500">Academic Advisor</p>
                  <p class="text-sm font-semibold text-gray-900">Dr. Sarah Wilson</p>
                  <p class="text-xs text-blue-600">s.wilson@university.edu</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Icon components

const BookOpenIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>' }
const ClipboardCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>' }
const UserCheckIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }
const TrendingUpIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>' }
const ClockIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }
const VideoIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>' }
const DocumentTextIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' }
const StarIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>' }
const EyeIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>' }
const DownloadIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' }
const LibraryIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11"></path></svg>' }
const MessageCircleIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>' }
const PhoneIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>' }
const MailIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' }


// State management
const unreadNotifications = ref(5)

// Student data
const student = ref({
  name: 'Sarah Johnson',
  profilePicture: '/placeholder.svg?height=64&width=64',
  course: 'Computer Science - Year 3',
  studentId: 'CS2024001',
  lastLogin: 'Today 9:30 AM'
})

// Summary statistics
const summaryStats = ref({
  totalCourses: 6,
  assignmentsSubmitted: 12,
  totalAssignments: 15,
  attendanceRate: 94,
  currentGPA: 3.7,
  lastLogin: 'Today'
})

// Weekly schedule
const weeklySchedule = ref([
  {
    id: 1,
    day: 'Monday',
    time: '09:00',
    subject: 'Data Structures & Algorithms',
    teacher: 'Dr. Smith',
    room: 'Room 301',
    status: 'Upcoming',
    isOnline: false
  },
  {
    id: 2,
    day: 'Monday',
    time: '11:00',
    subject: 'Web Development',
    teacher: 'Prof. Johnson',
    room: 'Online',
    status: 'In Progress',
    isOnline: true
  },
  {
    id: 3,
    day: 'Tuesday',
    time: '14:00',
    subject: 'Database Systems',
    teacher: 'Dr. Brown',
    room: 'Room 205',
    status: 'Upcoming',
    isOnline: false
  },
  {
    id: 4,
    day: 'Wednesday',
    time: '10:00',
    subject: 'Software Engineering',
    teacher: 'Prof. Davis',
    room: 'Online',
    status: 'Upcoming',
    isOnline: true
  }
])

// Academic progress
const academicProgress = ref([
  { id: 1, name: 'Mathematics', grade: 'A-', progress: 85, recentScore: '92%' },
  { id: 2, name: 'Programming', grade: 'A', progress: 92, recentScore: '95%' },
  { id: 3, name: 'Physics', grade: 'B+', progress: 78, recentScore: '88%' },
  { id: 4, name: 'English', grade: 'A-', progress: 88, recentScore: '90%' }
])

// Upcoming items
const upcomingItems = ref([
  {
    id: 1,
    title: 'Final Project Submission',
    subject: 'Web Development',
    dueDate: 'Dec 15, 2024',
    type: 'assignment',
    priority: 'High',
    timeLeft: '3 days left',
    submissionStatus: 'Not Submitted'
  },
  {
    id: 2,
    title: 'Midterm Exam',
    subject: 'Data Structures',
    dueDate: 'Dec 18, 2024',
    type: 'exam',
    priority: 'High',
    timeLeft: '6 days left',
    submissionStatus: 'Scheduled'
  },
  {
    id: 3,
    title: 'Research Paper',
    subject: 'Database Systems',
    dueDate: 'Dec 22, 2024',
    type: 'assignment',
    priority: 'Medium',
    timeLeft: '10 days left',
    submissionStatus: 'Draft Saved'
  }
])

// Notifications
const notifications = ref([
  {
    id: 1,
    type: 'assignment',
    title: 'New Assignment Posted',
    message: 'Web Development - Final Project guidelines are now available',
    time: '2 hours ago'
  },
  {
    id: 2,
    type: 'grade',
    title: 'Grade Updated',
    message: 'Your Mathematics quiz grade has been posted',
    time: '1 day ago'
  },
  {
    id: 3,
    type: 'announcement',
    title: 'System Maintenance',
    message: 'Scheduled maintenance on Dec 20, 2024 from 2-4 AM',
    time: '2 days ago'
  },
  {
    id: 4,
    type: 'deadline',
    title: 'Assignment Due Soon',
    message: 'Programming assignment due in 2 days',
    time: '3 hours ago'
  },
  {
    id: 5,
    type: 'announcement',
    title: 'Class Cancelled',
    message: 'Physics class on Dec 13 has been cancelled',
    time: '5 hours ago'
  }
])

// Attendance record
const attendanceRecord = ref({
  thisMonth: 94,
  totalAbsences: 3,
  lateCheckins: 2,
  weeklyAverage: 96
})

// Recent feedback
const recentFeedback = ref([
  {
    id: 1,
    subject: 'Web Development',
    comment: 'Excellent work on the React project. Great attention to detail.',
    teacher: 'Prof. Johnson'
  },
  {
    id: 2,
    subject: 'Mathematics',
    comment: 'Good improvement in problem-solving approach.',
    teacher: 'Dr. Smith'
  }
])

// Resources
const resources = ref([
  {
    id: 1,
    title: 'Lecture Notes - Week 12',
    subject: 'Data Structures',
    type: 'document'
  },
  {
    id: 2,
    title: 'Tutorial Video - React Hooks',
    subject: 'Web Development',
    type: 'video'
  },
  {
    id: 3,
    title: 'Reading List - Database Design',
    subject: 'Database Systems',
    type: 'link'
  },
  {
    id: 4,
    title: 'Assignment Template',
    subject: 'Software Engineering',
    type: 'document'
  }
])

// Helper functions
const getCurrentWeek = () => {
  const now = new Date()
  const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()))
  const endOfWeek = new Date(now.setDate(now.getDate() - now.getDay() + 6))
  return `${startOfWeek.toLocaleDateString()} - ${endOfWeek.toLocaleDateString()}`
}

const getStatusColor = (status) => {
  switch (status) {
    case 'Upcoming': return 'bg-blue-100 text-blue-700 border border-blue-200'
    case 'In Progress': return 'bg-green-100 text-green-700 border border-green-200'
    case 'Completed': return 'bg-gray-100 text-gray-700 border border-gray-200'
    default: return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

const getGradeColor = (grade) => {
  if (grade.startsWith('A')) return 'text-green-600'
  if (grade.startsWith('B')) return 'text-blue-600'
  if (grade.startsWith('C')) return 'text-yellow-600'
  return 'text-red-600'
}

const getPriorityColor = (priority) => {
  switch (priority) {
    case 'High': return 'bg-red-100 text-red-700 border border-red-200'
    case 'Medium': return 'bg-yellow-100 text-yellow-700 border border-yellow-200'
    case 'Low': return 'bg-green-100 text-green-700 border border-green-200'
    default: return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

const getSubmissionColor = (status) => {
  switch (status) {
    case 'Submitted': return 'text-green-600'
    case 'Draft Saved': return 'text-yellow-600'
    case 'Not Submitted': return 'text-red-600'
    case 'Scheduled': return 'text-blue-600'
    default: return 'text-gray-600'
  }
}

const getNotificationColor = (type) => {
  switch (type) {
    case 'assignment': return 'bg-blue-100 text-blue-600'
    case 'grade': return 'bg-green-100 text-green-600'
    case 'announcement': return 'bg-yellow-100 text-yellow-600'
    case 'deadline': return 'bg-red-100 text-red-600'
    default: return 'bg-gray-100 text-gray-600'
  }
}

const getNotificationIcon = (type) => {
  switch (type) {
    case 'assignment': return 'ClipboardCheckIcon'
    case 'grade': return 'CheckCircleIcon'
    case 'announcement': return 'InfoIcon'
    case 'deadline': return 'AlertCircleIcon'
    default: return 'AlertCircleIcon'
  }
}

const getResourceIcon = (type) => {
  switch (type) {
    case 'video': return 'VideoIcon'
    case 'document': return 'FileTextIcon'
    case 'link': return 'LinkIcon'
    default: return 'FileTextIcon'
  }
}
</script>

<style scoped>
/* Modern animations and transitions */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom backdrop blur */
.backdrop-blur-xl {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

/* Gradient text effect */
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Enhanced focus states */
button:focus,
a:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
