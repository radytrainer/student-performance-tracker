<template>
    <!-- Class Details View -->
    <div v-if="selectedClass" class="space-y-8">
        <!-- Back Button and Class Header -->
        <div class="flex items-center justify-between">
            <button @click="selectedClass = null"
                class="flex items-center text-gray-600 hover:text-gray-900 bg-white/60 backdrop-blur-sm px-4 py-2 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                <ArrowLeft class="w-4 h-4 mr-2" />
                <span class="font-medium">Back to Classes</span>
            </button>
            <div class="flex space-x-3">
                <button @click="showNotes = true"
                    class="bg-gradient-to-r from-amber-100 to-amber-200 hover:from-amber-200 hover:to-amber-300 text-amber-800 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <StickyNote class="w-4 h-4 inline mr-2" />
                    Notes
                </button>
                <button
                    class="bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <Settings class="w-4 h-4 inline mr-2" />
                    Edit Class
                </button>
            </div>
        </div>

        <!-- Class Summary -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2
                        class="text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent mb-2">
                        {{ selectedClass.name }}</h2>
                    <p class="text-gray-600 text-lg font-medium">{{ selectedClass.academicYear }} • Room {{
                        selectedClass.room
                        }}</p>
                </div>
                <div class="text-right bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-xl">
                    <p class="text-sm text-gray-500 font-semibold uppercase tracking-wide">Class Teacher</p>
                    <p class="font-bold text-gray-900 text-lg">Ms. Sarah Johnson</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-2xl text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 font-semibold uppercase tracking-wide text-sm">Students</p>
                            <p class="text-3xl font-bold">{{ selectedClass.studentCount }}</p>
                        </div>
                        <Users class="w-8 h-8 text-blue-200" />
                    </div>
                </div>
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 p-6 rounded-2xl text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 font-semibold uppercase tracking-wide text-sm">Attendance Rate
                            </p>
                            <p class="text-3xl font-bold">{{ selectedClass.attendanceRate }}%</p>
                        </div>
                        <Calendar class="w-8 h-8 text-emerald-200" />
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-2xl text-white shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 font-semibold uppercase tracking-wide text-sm">Avg Grade</p>
                            <p class="text-3xl font-bold">{{ selectedClass.avgGrade }}</p>
                        </div>
                        <TrendingUp class="w-8 h-8 text-purple-200" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <div class="border-b border-gray-200/50">
                <nav class="flex space-x-8 px-8">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                        'py-6 px-1 border-b-3 font-semibold text-sm transition-all duration-300 flex items-center space-x-2',
                        activeTab === tab.id
                            ? 'border-blue-500 text-blue-600 bg-blue-50/50'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
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
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <Search
                                    class="w-5 h-5 absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
                                <input v-model="studentSearch" type="text" placeholder="Search students..."
                                    class="pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white/60 backdrop-blur-sm shadow-md font-medium" />
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
                                class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                <Download class="w-4 h-4 inline mr-2" />
                                Export CSV
                            </button>
                            <button @click="showBulkAssign = true"
                                class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                <Plus class="w-4 h-4 inline mr-2" />
                                Add Student
                            </button>
                        </div>
                    </div>

                    <div
                        class="bg-white/60 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200/50">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th
                                        class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Student
                                    </th>
                                    <th
                                        class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Code</th>
                                    <th
                                        class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th
                                        class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Attendance</th>
                                    <th
                                        class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/40 backdrop-blur-sm divide-y divide-gray-200/30">
                                <tr v-for="student in filteredStudents" :key="student.id"
                                    class="hover:bg-white/60 transition-colors duration-200">
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center shadow-lg">
                                                <User class="w-6 h-6 text-white" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ student.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap text-sm font-semibold text-gray-900">{{
                                        student.code }}
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap text-sm text-gray-600 font-medium">{{
                                        student.email }}</td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <span :class="[
                                            'px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full shadow-sm',
                                            student.status === 'active' ? 'bg-gradient-to-r from-emerald-100 to-emerald-200 text-emerald-800' : 'bg-gradient-to-r from-red-100 to-red-200 text-red-800'
                                        ]">
                                            {{ student.status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-3">
                                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full"
                                                    :style="`width: ${student.attendance}%`"></div>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ student.attendance
                                                }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button @click="viewStudentProfile(student)"
                                                class="p-2 text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors duration-200">
                                                <Eye class="w-4 h-4" />
                                            </button>
                                            <button @click="viewGrades(student)"
                                                class="p-2 text-emerald-600 hover:text-emerald-900 bg-emerald-100 hover:bg-emerald-200 rounded-lg transition-colors duration-200">
                                                <BookOpen class="w-4 h-4" />
                                            </button>
                                            <button @click="viewAttendance(student)"
                                                class="p-2 text-amber-600 hover:text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors duration-200">
                                                <Calendar class="w-4 h-4" />
                                            </button>
                                            <button
                                                class="p-2 text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                                                <MoreHorizontal class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Subjects Tab -->
                <div v-if="activeTab === 'subjects'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="subject in selectedClass.subjectDetails" :key="subject.id"
                            class="bg-gradient-to-br from-white/80 to-gray-50/80 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg">{{ subject.name }}</h4>
                                    <p class="text-sm text-gray-600 font-medium">{{ subject.teacher }}</p>
                                </div>
                                <span
                                    class="px-3 py-1 bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 text-xs font-bold rounded-full shadow-sm">{{
                                    subject.code }}</span>
                            </div>
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 font-medium">Avg Grade:</span>
                                    <span class="font-bold text-gray-900">{{ subject.avgGrade }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 font-medium">Assignments:</span>
                                    <span class="font-bold text-gray-900">{{ subject.assignments }}</span>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-3 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    Gradebook
                                </button>
                                <button
                                    class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-3 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    Attendance
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Tab -->
                <div v-if="activeTab === 'schedule'" class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-2xl font-bold text-gray-900">Weekly Schedule</h3>
                        <button
                            class="bg-gradient-to-r from-gray-100 to-gray-200 hover:from-gray-200 hover:to-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <Printer class="w-4 h-4 inline mr-2" />
                            Print Schedule
                        </button>
                    </div>

                    <div
                        class="bg-white/60 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <th
                                        class="border border-gray-200/50 px-6 py-4 text-left text-sm font-bold text-gray-900">
                                        Time
                                    </th>
                                    <th v-for="day in weekDays" :key="day"
                                        class="border border-gray-200/50 px-6 py-4 text-left text-sm font-bold text-gray-900">
                                        {{ day }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="period in periods" :key="period.time"
                                    class="hover:bg-white/60 transition-colors duration-200">
                                    <td
                                        class="border border-gray-200/50 px-6 py-4 text-sm font-bold text-gray-900 bg-gray-50/50">
                                        {{
                                        period.time }}</td>
                                    <td v-for="day in weekDays" :key="day"
                                        class="border border-gray-200/50 px-6 py-4 text-sm">
                                        <div v-if="period.schedule[day]"
                                            class="bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 px-3 py-2 rounded-lg text-xs font-semibold shadow-sm">
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
                        <div
                            class="bg-gradient-to-br from-white/80 to-gray-50/80 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20">
                            <h4 class="font-bold text-gray-900 mb-6 text-lg">Attendance Overview</h4>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 font-medium">Present</span>
                                    <span class="text-sm font-bold text-emerald-600">87%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 h-3 rounded-full shadow-lg"
                                        style="width: 87%"></div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 font-medium">Absent</span>
                                    <span class="text-sm font-bold text-red-600">13%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                                    <div class="bg-gradient-to-r from-red-500 to-red-600 h-3 rounded-full shadow-lg"
                                        style="width: 13%"></div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-br from-white/80 to-gray-50/80 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20">
                            <h4 class="font-bold text-gray-900 mb-6 text-lg">Grade Distribution</h4>
                            <div class="space-y-3">
                                <div v-for="grade in gradeDistribution" :key="grade.grade"
                                    class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 font-medium">Grade {{ grade.grade }}</span>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-24 bg-gray-200 rounded-full h-3 shadow-inner">
                                            <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full shadow-lg"
                                                :style="`width: ${grade.percentage}%`"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 w-8">{{ grade.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-white/80 to-gray-50/80 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-white/20">
                        <h4 class="font-bold text-gray-900 mb-6 text-lg">Subject Performance</h4>
                        <div class="space-y-4">
                            <div v-for="subject in selectedClass.subjectDetails" :key="subject.id"
                                class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50/80 to-white/80 rounded-xl shadow-md border border-white/20">
                                <div>
                                    <p class="font-bold text-gray-900">{{ subject.name }}</p>
                                    <p class="text-sm text-gray-600 font-medium">{{ subject.teacher }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-900">{{ subject.avgGrade }}</p>
                                    <p class="text-sm text-gray-600 font-medium">Class Average</p>
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
import { ref, computed } from 'vue'
import {
    BookOpen, Users, Calendar, TrendingUp, MapPin, Clock, Plus, User,
    MoreHorizontal, ArrowLeft, StickyNote, Settings, Search, Download,
    Eye, Printer, X
} from 'lucide-vue-next'


// Reactive data
const selectedClass = ref(null)
const activeTab = ref('students')
const selectedStudent = ref(null)
const showBulkAssign = ref(false)
const showNotes = ref(false)
const studentSearch = ref('')
const statusFilter = ref('')
const selectedStudentsForBulk = ref([])
const newNote = ref('')

// Mock data
const classes = ref([
    {
        id: 1,
        name: 'Grade 10A',
        academicYear: '2024-2025',
        room: '101',
        studentCount: 28,
        schedule: 'Mon-Fri 8:00-14:00',
        subjects: ['Mathematics', 'Physics', 'Chemistry'],
        attendanceRate: 87,
        avgGrade: 'B+',
        subjectDetails: [
            { id: 1, name: 'Mathematics', teacher: 'Mr. Smith', code: 'MATH10', avgGrade: 'B+', assignments: 12 },
            { id: 2, name: 'Physics', teacher: 'Dr. Johnson', code: 'PHYS10', avgGrade: 'A-', assignments: 8 },
            { id: 3, name: 'Chemistry', teacher: 'Ms. Davis', code: 'CHEM10', avgGrade: 'B', assignments: 10 }
        ]
    },
    {
        id: 2,
        name: 'Grade 11B',
        academicYear: '2024-2025',
        room: '205',
        studentCount: 25,
        schedule: 'Mon-Fri 9:00-15:00',
        subjects: ['Biology', 'English', 'History'],
        attendanceRate: 92,
        avgGrade: 'A-',
        subjectDetails: [
            { id: 4, name: 'Biology', teacher: 'Dr. Wilson', code: 'BIO11', avgGrade: 'A-', assignments: 9 },
            { id: 5, name: 'English', teacher: 'Ms. Brown', code: 'ENG11', avgGrade: 'B+', assignments: 15 },
            { id: 6, name: 'History', teacher: 'Mr. Taylor', code: 'HIST11', avgGrade: 'A', assignments: 7 }
        ]
    },
    {
        id: 3,
        name: 'Grade 12C',
        academicYear: '2024-2025',
        room: '301',
        studentCount: 22,
        schedule: 'Mon-Fri 10:00-16:00',
        subjects: ['Advanced Math', 'Computer Science'],
        attendanceRate: 89,
        avgGrade: 'A',
        subjectDetails: [
            { id: 7, name: 'Advanced Mathematics', teacher: 'Dr. Anderson', code: 'AMATH12', avgGrade: 'A', assignments: 14 },
            { id: 8, name: 'Computer Science', teacher: 'Mr. Garcia', code: 'CS12', avgGrade: 'A-', assignments: 11 }
        ]
    }
])

const students = ref([
    { id: 1, name: 'Alice Johnson', code: 'STU001', email: 'alice@school.edu', status: 'active', attendance: 95, classId: 1 },
    { id: 2, name: 'Bob Smith', code: 'STU002', email: 'bob@school.edu', status: 'active', attendance: 88, classId: 1 },
    { id: 3, name: 'Carol Davis', code: 'STU003', email: 'carol@school.edu', status: 'active', attendance: 92, classId: 1 },
    { id: 4, name: 'David Wilson', code: 'STU004', email: 'david@school.edu', status: 'inactive', attendance: 76, classId: 1 },
    { id: 5, name: 'Emma Brown', code: 'STU005', email: 'emma@school.edu', status: 'active', attendance: 94, classId: 2 },
    { id: 6, name: 'Frank Miller', code: 'STU006', email: 'frank@school.edu', status: 'active', attendance: 87, classId: 2 }
])

const availableStudents = ref([
    { id: 7, name: 'Grace Taylor', code: 'STU007' },
    { id: 8, name: 'Henry Anderson', code: 'STU008' },
    { id: 9, name: 'Ivy Garcia', code: 'STU009' },
    { id: 10, name: 'Jack Martinez', code: 'STU010' }
])

const classNotes = ref([
    { id: 1, content: 'Remember to prepare for the upcoming parent-teacher conference next week.', date: '2024-01-15' },
    { id: 2, content: 'Several students need extra help with algebra concepts.', date: '2024-01-10' }
])

const tabs = [
    { id: 'students', name: 'Students', icon: Users },
    { id: 'subjects', name: 'Subjects', icon: BookOpen },
    { id: 'schedule', name: 'Schedule', icon: Calendar },
    { id: 'performance', name: 'Performance', icon: TrendingUp }
]

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

const gradeDistribution = [
    { grade: 'A', count: 8, percentage: 30 },
    { grade: 'B', count: 12, percentage: 45 },
    { grade: 'C', count: 6, percentage: 20 },
    { grade: 'D', count: 2, percentage: 5 }
]

// Computed properties
const totalStudents = computed(() => {
    return classes.value.reduce((total, cls) => total + cls.studentCount, 0)
})

const filteredStudents = computed(() => {
    if (!selectedClass.value) return []

    let filtered = students.value.filter(student => student.classId === selectedClass.value.id)

    if (studentSearch.value) {
        filtered = filtered.filter(student =>
            student.name.toLowerCase().includes(studentSearch.value.toLowerCase()) ||
            student.code.toLowerCase().includes(studentSearch.value.toLowerCase())
        )
    }

    if (statusFilter.value) {
        filtered = filtered.filter(student => student.status === statusFilter.value)
    }

    return filtered
})

// Methods
const viewClassDetails = (classItem) => {
    selectedClass.value = classItem
    activeTab.value = 'students'
}

const viewStudents = (classItem) => {
    selectedClass.value = classItem
    activeTab.value = 'students'
}

const viewStudentProfile = (student) => {
    selectedStudent.value = student
}

const viewGrades = (student) => {
    console.log('View grades for:', student.name)
}

const viewAttendance = (student) => {
    console.log('View attendance for:', student.name)
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
</script>