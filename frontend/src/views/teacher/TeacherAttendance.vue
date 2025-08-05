<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Manage Attendance</h1>
    
    <!-- 🔹 1. Attendance Dashboard / Class Selection Filters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 items-end">
      <div class="w-full">
        <label class="block mb-1 font-medium text-gray-700">Class</label>
        <select v-model="filters.class_id" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">All Classes</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
        </select>
      </div>
      <div class="w-full">
        <label class="block mb-1 font-medium text-gray-700">Date</label>
        <input type="date" v-model="filters.date" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
      </div>
      <div class="w-full">
        <label class="block mb-1 font-medium text-gray-700">Subject</label>
        <select v-model="filters.subject_id" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">All Subjects</option>
          <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.subject_name }}</option>
        </select>
      </div>
      <div class="w-full">
        <button @click="openTakeAttendanceModal" class="w-full flex items-center justify-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors mt-6 sm:mt-0">
          <PlusIcon class="h-5 w-5" />
          Take Attendance
        </button>
      </div>
    </div>

    <!-- 🔹 2. Take Attendance Interface Modal -->
    <div v-if="showAttendanceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white w-full max-w-2xl rounded-lg shadow-xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-xl font-bold text-gray-800">Take Attendance</h3>
        </div>
        <div class="overflow-y-auto p-6 flex-1">
          <form @submit.prevent="submitAttendance">
            <!-- Class Selection -->
            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Class</label>
              <select v-model="newAttendance.class_id" @change="onClassChange" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" required>
                <option value="">Select Class</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
              </select>
            </div>

            <!-- Date Selection -->
            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Date</label>
              <input type="date" v-model="newAttendance.date" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" required />
            </div>

            <!-- Mark All Present Button -->
            <div class="mb-4">
              <button type="button" @click="markAllPresent" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors">
                Mark All Present
              </button>
            </div>

            <!-- Students List -->
            <div v-if="modalFilteredStudents.length > 0" class="space-y-4 max-h-96 overflow-y-auto">
              <div v-for="student in modalFilteredStudents" :key="student.user_id" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <label class="block font-medium mb-2 text-gray-700">
                  {{ student.user?.first_name }} {{ student.user?.last_name }} 
                  <span class="text-sm text-gray-500">(ID: {{ student.user_id }})</span>
                </label>
                
                <!-- Attendance Status -->
                <div class="mb-2">
                  <select 
                    v-model="studentAttendance[student.user_id]" 
                    class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" 
                    required
                  >
                    <option value="">Select Status</option>
                    <option v-for="status in attendanceStatuses" :key="status" :value="status">{{ status }}</option>
                  </select>
                </div>
                
                <!-- Notes -->
                <input 
                  v-model="studentAttendanceNotes[student.user_id]" 
                  type="text" 
                  placeholder="Notes (e.g., sick leave)" 
                  class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" 
                />
              </div>
            </div>

            <!-- No Students Message -->
            <div v-else-if="newAttendance.class_id" class="text-center py-8 text-gray-500">
              <p>No students found for the selected class.</p>
            </div>

            <!-- Select Class Message -->
            <div v-else class="text-center py-8 text-gray-500">
              <p>Please select a class to view students.</p>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex gap-3">
              <button 
                type="submit" 
                :disabled="!canSubmit"
                class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md transition-colors"
              >
                Save Attendance
              </button>
              <button 
                type="button" 
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 🔹 3. View Attendance Records Section -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Attendance Records</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
          <label class="block mb-1 font-medium text-gray-700">Class</label>
          <select v-model="viewFilters.class_id" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Classes</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
          </select>
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Student</label>
          <select v-model="viewFilters.student_id" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Students</option>
            <option v-for="student in students" :key="student.user_id" :value="student.user_id">{{ student.user?.first_name }} {{ student.user?.last_name }}</option>
          </select>
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Date Range</label>
          <input type="date" v-model="viewFilters.start_date" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Status</label>
          <select v-model="viewFilters.status" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Statuses</option>
            <option v-for="status in attendanceStatuses" :key="status" :value="status">{{ status }}</option>
          </select>
        </div>
      </div>
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
      </div>
      <div v-else-if="error" class="text-red-500 p-4 bg-red-50 rounded-md">{{ error }}</div>
      <div v-else-if="attendanceRecords.length === 0" class="text-gray-500 p-4 text-center">No attendance records available.</div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="record in attendanceRecords" :key="record.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.student_id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.student?.user?.first_name }} {{ record.student?.user?.last_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.class?.class_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(record.date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(record.status)">
                  {{ record.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.notes || "N/A" }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 📅 Calendar View -->
      <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Calendar View</h3>
        <div class="bg-white p-4 rounded-xl shadow-md">
          <FullCalendar :options="calendarOptions" />
        </div>
      </div>

      <!-- 📈 Attendance Summary per Student -->
      <div v-for="student in uniqueStudents" :key="student.user_id" class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">{{ student.user?.first_name }} {{ student.user?.last_name }}</h3>
        <div class="grid grid-cols-2 gap-4 mt-2">
          <div><p class="text-gray-600">Present: {{ getAttendanceCount(student.user_id, "Present") }}</p></div>
          <div><p class="text-gray-600">Absent: {{ getAttendanceCount(student.user_id, "Absent") }}</p></div>
          <div><p class="text-gray-600">Late: {{ getAttendanceCount(student.user_id, "Late") }}</p></div>
          <div><p class="text-gray-600">Excused: {{ getAttendanceCount(student.user_id, "Excused") }}</p></div>
          <div><p class="text-gray-600">Total Days: {{ getTotalDays(student.user_id) }}</p></div>
          <div><p class="text-gray-600">Attendance %: {{ getAttendancePercentage(student.user_id).toFixed(1) }}%</p></div>
        </div>
        <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
          <div :style="{ width: getAttendancePercentage(student.user_id) + '%', backgroundColor: getAttendancePercentage(student.user_id) < 75 ? '#ef4444' : '#22c55e' }" class="h-2.5 rounded-full"></div>
        </div>
      </div>
    </div>

    <!-- Analytics Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6">
      <div class="p-4 rounded-xl shadow-md min-h-[300px] flex flex-col bg-gray-50 border border-gray-200" aria-label="Attendance Rate Chart">
        <h3 class="font-semibold text-lg mb-4">Attendance Rate (Monthly)</h3>
        <div class="flex-1">
          <BarChart v-if="!loadingCharts" :chart-data="barChartData" :chart-options="barChartOptions" />
          <div v-else class="flex items-center justify-center h-full">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
          </div>
        </div>
      </div>

      <div class="p-4 rounded-xl shadow-md min-h-[300px] flex flex-col bg-gray-50 border border-gray-200" aria-label="Late Arrival Trends Chart">
        <h3 class="font-semibold text-lg mb-4">Late Arrival Trends</h3>
        <div class="flex-1">
          <LineChart v-if="!loadingCharts" :chart-data="lineChartData" :chart-options="lineChartOptions" />
          <div v-else-if="errorCharts" class="flex items-center justify-center h-full text-red-500">
            Failed to load chart data
          </div>
          <div v-else class="flex items-center justify-center h-full">
            <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
          </div>
        </div>
      </div>

      <div class="p-4 rounded-xl shadow-md min-h-[300px] flex flex-col bg-gray-50 border border-gray-200" aria-label="Status Breakdown Chart">
        <h3 class="font-semibold text-lg mb-4">Status Breakdown</h3>
        <div class="flex-1">
          <PieChart v-if="!loadingCharts" :chart-data="pieChartData" :chart-options="pieChartOptions" />
          <div v-else class="flex items-center justify-center h-full">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔹 4. Export / Reporting Features -->
    <div class="bg-white rounded-lg shadow-md p-4 mt-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Export / Reports</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button @click="exportCSV" class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors">
          📄 Export CSV
        </button>
        <button @click="exportPDF" class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors">
          📄 Export PDF
        </button>
        <button @click="showAnalytics" class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors">
          📊 Analytics View
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";
import BarChart from '@/components/charts/BarChart.vue'
import LineChart from '@/components/charts/LineChart.vue'
import PieChart from '@/components/charts/PieChart.vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import { PlusIcon } from 'lucide-vue-next'

// Example: attendance events for calendar
const calendarOptions = ref({
  plugins: [dayGridPlugin],
  initialView: 'dayGridMonth',
  events: [
    { title: 'Present', date: '2025-08-01', color: '#22c55e' },
    { title: 'Absent', date: '2025-08-02', color: '#ef4444' },
    { title: 'Late', date: '2025-08-03', color: '#facc15' },
    { title: 'Present', date: '2025-08-04', color: '#22c55e' },
  ],
})

const barChartData = ref({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
  datasets: [
    {
      label: 'Attendance Rate (%)',
      data: [92, 88, 95, 90, 87, 93],
      backgroundColor: '#3b82f6'
    }
  ]
})

const lineChartData = ref({
  labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
  datasets: [
    {
      label: 'Late Arrivals',
      data: [5, 3, 7, 2],
      borderColor: '#f59e0b',
      tension: 0.3
    }
  ]
})

const pieChartData = ref({
  labels: ['Present', 'Absent', 'Late', 'Excused'],
  datasets: [
    {
      label: 'Status Distribution',
      data: [120, 15, 10, 5],
      backgroundColor: ['#22c55e', '#ef4444', '#f59e0b', '#3b82f6']
    }
  ]
})

const barChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
})

const lineChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
})

const pieChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
})

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  console.error("Request error:", error);
  return Promise.reject(error);
});

const loading = ref(false);
const loadingCharts = ref(false);
const errorCharts = ref(false);
const error = ref(null);
const attendanceRecords = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]);
const showAttendanceModal = ref(false);
const attendanceStatuses = ref(["Present", "Absent", "Late", "Excused"]);

const filters = ref({
  class_id: "",
  date: new Date().toISOString().split("T")[0],
  subject_id: "",
});

const viewFilters = ref({
  class_id: "",
  student_id: "",
  start_date: "",
  end_date: "",
  status: "",
});

const newAttendance = ref({
  class_id: "",
  date: new Date().toISOString().split("T")[0],
});

const studentAttendance = ref({});
const studentAttendanceNotes = ref({});

// Mock data for development
const initializeMockData = () => {
  classes.value = [
    { id: "1", class_name: "Grade 10A" },
    { id: "2", class_name: "Grade 10B" },
    { id: "3", class_name: "Grade 11A" },
  ];

  subjects.value = [
    { id: "1", subject_name: "Mathematics" },
    { id: "2", subject_name: "English" },
    { id: "3", subject_name: "Science" },
  ];

  students.value = [
    { id: "1", user_id: "1", class_id: "1", user: { id: "1", first_name: "John", last_name: "Doe" } },
    { id: "2", user_id: "2", class_id: "1", user: { id: "2", first_name: "Jane", last_name: "Smith" } },
    { id: "3", user_id: "3", class_id: "2", user: { id: "3", first_name: "Bob", last_name: "Johnson" } },
    { id: "4", user_id: "4", class_id: "2", user: { id: "4", first_name: "Alice", last_name: "Brown" } },
    { id: "5", user_id: "5", class_id: "3", user: { id: "5", first_name: "Charlie", last_name: "Wilson" } },
  ];

  attendanceRecords.value = [
    {
      id: "1",
      student_id: "1",
      class_id: "1",
      date: "2025-01-15",
      status: "Present",
      notes: "",
      student: students.value[0],
      class: classes.value[0],
    },
    {
      id: "2",
      student_id: "2",
      class_id: "1",
      date: "2025-01-15",
      status: "Absent",
      notes: "Sick leave",
      student: students.value[1],
      class: classes.value[0],
    },
    {
      id: "3",
      student_id: "3",
      class_id: "2",
      date: "2025-01-15",
      status: "Late",
      notes: "Traffic jam",
      student: students.value[2],
      class: classes.value[1],
    },
    {
      id: "4",
      student_id: "4",
      class_id: "2",
      date: "2025-01-15",
      status: "Present",
      notes: "",
      student: students.value[3],
      class: classes.value[1],
    },
  ];
};

const fetchAll = async () => {
  loading.value = true;
  try {
    // For development, use mock data
    // In production, uncomment the API calls below
    initializeMockData();
    
    
    const [cls, sub, stu] = await Promise.all([
      apiClient.get("/classes"),
      apiClient.get("/subjects"),
      apiClient.get("/students"),
    ]);
    classes.value = cls.data.data;
    subjects.value = sub.data.data;
    students.value = stu.data.data;
    
    
    await fetchAttendance();
  } catch (err) {
    error.value = "Failed to load data: " + (err.response?.data?.message || err.message);
    console.error("Fetch all error:", err);
  } finally {
    loading.value = false;
  }
};

const fetchAttendance = async () => {
  loading.value = true;
  try {
    // For development, skip API call
    // In production, uncomment the API call below
    
    /*
    const params = { ...filters.value, ...viewFilters.value };
    const res = await apiClient.get("/attendance", { params });
    attendanceRecords.value = res.data.data;
    */
    
    error.value = null;
  } catch (err) {
    error.value = "Failed to load attendance records: " + (err.response?.data?.message || err.message);
    console.error("Fetch attendance error:", err.response ? err.response : err);
  } finally {
    loading.value = false;
  }
};

// Computed property for students filtered by selected class in modal
const modalFilteredStudents = computed(() => {
  if (!newAttendance.value.class_id) return [];
  return students.value.filter((student) => student.class_id === newAttendance.value.class_id);
});

// Computed property for students filtered by main filters
const filteredStudents = computed(() => {
  if (!filters.value.class_id) return students.value;
  return students.value.filter((student) => student.class_id === filters.value.class_id);
});

// Computed property to check if form can be submitted
const canSubmit = computed(() => {
  if (!newAttendance.value.class_id || !newAttendance.value.date) return false;
  if (modalFilteredStudents.value.length === 0) return false;
  
  // Check if all students have attendance status selected
  return modalFilteredStudents.value.every(student => 
    studentAttendance.value[student.user_id] && 
    studentAttendance.value[student.user_id] !== ""
  );
});

const openTakeAttendanceModal = () => {
  showAttendanceModal.value = true;
  resetAttendanceForm();
};

const closeModal = () => {
  showAttendanceModal.value = false;
  resetAttendanceForm();
};

const resetAttendanceForm = () => {
  newAttendance.value.class_id = filters.value.class_id || "";
  newAttendance.value.date = filters.value.date;
  studentAttendance.value = {};
  studentAttendanceNotes.value = {};
  
  // Initialize attendance for current students
  if (newAttendance.value.class_id) {
    const currentStudents = students.value.filter(student => student.class_id === newAttendance.value.class_id);
    currentStudents.forEach((student) => {
      studentAttendance.value[student.user_id] = "Present";
      studentAttendanceNotes.value[student.user_id] = "";
    });
  }
};

const onClassChange = () => {
  // Reset attendance data when class changes
  studentAttendance.value = {};
  studentAttendanceNotes.value = {};
  
  // Initialize attendance for new class students
  modalFilteredStudents.value.forEach((student) => {
    studentAttendance.value[student.user_id] = "Present";
    studentAttendanceNotes.value[student.user_id] = "";
  });
};

const markAllPresent = () => {
  modalFilteredStudents.value.forEach((student) => {
    studentAttendance.value[student.user_id] = "Present";
    studentAttendanceNotes.value[student.user_id] = "";
  });
};

const submitAttendance = async () => {
  if (!canSubmit.value) {
    alert("Please fill in all required fields and select attendance status for all students.");
    return;
  }

  try {
    const payloads = modalFilteredStudents.value.map((student) => ({
      student_id: student.user_id,
      class_id: newAttendance.value.class_id,
      date: newAttendance.value.date,
      status: studentAttendance.value[student.user_id],
      notes: studentAttendanceNotes.value[student.user_id] || null,
      recorded_by: localStorage.getItem("user_id") || null,
      recorded_at: new Date().toISOString(),
    }));

    // For development, just log the data and simulate success
    console.log("Attendance data to be submitted:", payloads);
    
    // Simulate API call delay
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Add the new records to our mock data
    const newRecords = payloads.map((payload, index) => ({
      id: `new_${Date.now()}_${index}`,
      student_id: payload.student_id,
      class_id: payload.class_id,
      date: payload.date,
      status: payload.status,
      notes: payload.notes,
      student: students.value.find(s => s.user_id === payload.student_id),
      class: classes.value.find(c => c.id === payload.class_id),
    }));
    
    attendanceRecords.value.push(...newRecords);
    
    
    // In production, use this API call:
    await apiClient.post("/attendance", payloads);
    
    
    alert("Attendance saved successfully!");
    showAttendanceModal.value = false;
    await fetchAttendance();
  } catch (err) {
    alert(err.response?.data?.message || "Failed to save attendance");
    console.error("Submit attendance error:", err);
  }
};

const uniqueStudents = computed(() => {
  const studentIds = [...new Set(attendanceRecords.value.map((r) => r.student_id))];
  return students.value.filter((s) => studentIds.includes(s.user_id));
});

const getAttendanceCount = (studentId, status) => {
  return attendanceRecords.value.filter((r) => r.student_id === studentId && r.status === status).length;
};

const getTotalDays = (studentId) => {
  return attendanceRecords.value.filter((r) => r.student_id === studentId).length;
};

const getAttendancePercentage = (studentId) => {
  const total = getTotalDays(studentId);
  const present = getAttendanceCount(studentId, "Present");
  return total > 0 ? (present / total) * 100 : 0;
};

const exportCSV = () => {
  const headers = ["Student ID", "Full Name", "Class", "Date", "Status", "Notes"];
  const rows = attendanceRecords.value.map((record) => [
    record.student_id,
    `${record.student?.user?.first_name} ${record.student?.user?.last_name}`,
    record.class?.class_name,
    formatDate(record.date),
    record.status,
    record.notes || "",
  ]);
  const csvContent = [headers.join(","), ...rows.map((row) => row.map((cell) => `"${cell}"`).join(","))].join("\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = `attendance_${new Date().toISOString().split("T")[0]}.csv`;
  link.click();
};

const exportPDF = () => {
  alert("PDF export functionality to be implemented");
};

const showAnalytics = () => {
  alert("Analytics view to be implemented");
};

const getStatusBadgeClass = (status) => {
  const classes = {
    Present: "bg-green-100 text-green-800",
    Absent: "bg-red-100 text-red-800",
    Late: "bg-yellow-100 text-yellow-800",
    Excused: "bg-blue-100 text-blue-800",
  };
  return classes[status] || "bg-gray-100 text-gray-800";
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

watch(() => ({ ...filters.value, ...viewFilters.value }), fetchAttendance, {
  deep: true,
});

onMounted(() => {
  fetchAll();
});
</script>

<style scoped>
/* Custom scrollbar for the table container */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Modal scrollable content */
.overflow-y-auto {
  overflow-y: auto;
}

/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Smooth transitions for hover effects */
tr {
  transition: background-color 0.2s ease;
}

/* Focus styles for form inputs */
select:focus,
input:focus {
  outline: none;
}

/* Animation for loading spinner */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
