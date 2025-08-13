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
                  {{ student.user?.first_name || "N/A" }} {{ student.user?.last_name || "" }} 
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
                    <option v-for="status in attendanceStatuses" :key="status" :value="status">{{ status.charAt(0).toUpperCase() + status.slice(1) }}</option>
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
            <option v-for="student in students" :key="student.user_id" :value="student.user_id">{{ student.user?.first_name || "N/A" }} {{ student.user?.last_name || "" }}</option>
          </select>
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Start Date</label>
          <input type="date" v-model="viewFilters.start_date" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Status</label>
          <select v-model="viewFilters.status" @change="fetchAttendance" class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Statuses</option>
            <option v-for="status in attendanceStatuses" :key="status" :value="status">{{ status.charAt(0).toUpperCase() + status.slice(1) }}</option>
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
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.student?.user?.first_name || "N/A" }} {{ record.student?.user?.last_name || "" }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.class?.class_name || "N/A" }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(record.date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(record.status)">
                  {{ record.status.charAt(0).toUpperCase() + record.status.slice(1) }}
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
        <h3 class="text-lg font-semibold text-gray-800">{{ student.user?.first_name || "N/A" }} {{ student.user?.last_name || "" }}</h3>
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
import jsPDF from "jspdf";
import "jspdf-autotable";
import BarChart from "@/components/charts/BarChart.vue";
import LineChart from "@/components/charts/LineChart.vue";
import PieChart from "@/components/charts/PieChart.vue";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import { PlusIcon } from "lucide-vue-next";

// API client setup
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("auth_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    console.error("Request error:", error);
    return Promise.reject(error);
  }
);

// Reactive state
const loading = ref(false);
const loadingCharts = ref(false);
const errorCharts = ref(false);
const error = ref(null);
const attendanceRecords = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]);
const showAttendanceModal = ref(false);
const attendanceStatuses = ref(["present", "absent", "late"]);

const filters = ref({
  class_id: "",
  date: new Date().toISOString().split("T")[0],
  subject_id: "",
});

const viewFilters = ref({
  class_id: "",
  student_id: "",
  start_date: "",
  status: "",
});

const newAttendance = ref({
  class_id: "",
  date: new Date().toISOString().split("T")[0],
});

const studentAttendance = ref({});
const studentAttendanceNotes = ref({});

// Computed calendar options
const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin],
  initialView: "dayGridMonth",
  events: attendanceRecords.value.map((record) => ({
    title: record.status,
    date: record.date,
    color: getStatusColor(record.status),
  })),
}));

// Computed chart data
const barChartData = computed(() => {
  const months = [
    ...new Set(
      attendanceRecords.value.map((r) =>
        new Date(r.date).toLocaleString("en-US", { month: "short", year: "numeric" })
      )
    ),
  ].sort((a, b) => new Date(a) - new Date(b));
  const data = months.map((month) => {
    const records = attendanceRecords.value.filter(
      (r) => new Date(r.date).toLocaleString("en-US", { month: "short", year: "numeric" }) === month
    );
    const present = records.filter((r) => r.status === "Present").length;
    const total = records.length;
    return total > 0 ? (present / total) * 100 : 0;
  });

  return {
    labels: months,
    datasets: [
      {
        label: "Attendance Rate (%)",
        data,
        backgroundColor: "#3b82f6",
      },
    ],
  };
});

const lineChartData = computed(() => {
  const weeks = [
    ...new Set(
      attendanceRecords.value.map((r) => {
        const date = new Date(r.date);
        const year = date.getFullYear();
        const week = Math.ceil((date.getDate() + date.getDay()) / 7);
        return `Week ${week} ${year}`;
      })
    ),
  ].sort();
  const data = weeks.map((week) => {
    const records = attendanceRecords.value.filter((r) => {
      const date = new Date(r.date);
      const year = date.getFullYear();
      const weekNum = Math.ceil((date.getDate() + date.getDay()) / 7);
      return `Week ${weekNum} ${year}` === week;
    });
    return records.filter((r) => r.status === "late").length;
  });

  return {
    labels: weeks,
    datasets: [
      {
        label: "Late Arrivals",
        data,
        borderColor: "#f59e0b",
        tension: 0.3,
      },
    ],
  };
});

const pieChartData = computed(() => {
  const statuses = ["present", "absent", "late"];
  const data = statuses.map((status) => attendanceRecords.value.filter((r) => r.status === status).length);

  return {
    labels: statuses.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
    datasets: [
      {
        label: "Status Distribution",
        data,
        backgroundColor: ["#22c55e", "#ef4444", "#facc15"],
      },
    ],
  };
});

const barChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
});

const lineChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
});

const pieChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
});

// Fetch all initial data
const fetchAll = async () => {
  loading.value = true;
  try {
    const [cls, sub, stu] = await Promise.all([
      apiClient.get("/classes"),
      apiClient.get("/subjects"),
      apiClient.get("/students"),
    ]);
    classes.value = cls.data.data || [];
    subjects.value = sub.data.data || [];
    students.value = stu.data.data || [];
    await fetchAttendance();
  } catch (err) {
    error.value = `Failed to load data: ${err.response?.data?.message || err.message}`;
    console.error("Fetch all error:", err);
  } finally {
    loading.value = false;
  }
};

// Fetch attendance records
const fetchAttendance = async () => {
  loading.value = true;
  try {
    const params = { ...filters.value, ...viewFilters.value };
    const res = await apiClient.get("/attendance", { params });
    attendanceRecords.value = res.data.attendances || [];
    error.value = null;
  } catch (err) {
    error.value = `Failed to load attendance records: ${err.response?.data?.message || err.message}`;
    console.error("Fetch attendance error:", err);
  } finally {
    loading.value = false;
  }
};

// Computed properties
const modalFilteredStudents = computed(() => {
  if (!newAttendance.value.class_id) return [];
  return students.value.filter((student) => student.current_class_id === newAttendance.value.class_id);
});

const filteredStudents = computed(() => {
  if (!filters.value.class_id) return students.value;
  return students.value.filter((student) => student.current_class_id === filters.value.class_id);
});

const canSubmit = computed(() => {
  if (!newAttendance.value.class_id || !newAttendance.value.date) return false;
  if (modalFilteredStudents.value.length === 0) return false;
  return modalFilteredStudents.value.every(
    (student) => studentAttendance.value[student.user_id] && studentAttendance.value[student.user_id] !== ""
  );
});

const uniqueStudents = computed(() => {
  const studentIds = [...new Set(attendanceRecords.value.map((r) => r.student_id))];
  return students.value.filter((s) => studentIds.includes(s.user_id));
});

// Modal functions
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
  if (newAttendance.value.class_id) {
    const currentStudents = students.value.filter((student) => student.current_class_id === newAttendance.value.class_id);
    currentStudents.forEach((student) => {
      studentAttendance.value[student.user_id] = "present";
      studentAttendanceNotes.value[student.user_id] = "";
    });
  }
};

const onClassChange = () => {
  studentAttendance.value = {};
  studentAttendanceNotes.value = {};
  modalFilteredStudents.value.forEach((student) => {
    studentAttendance.value[student.user_id] = "present";
    studentAttendanceNotes.value[student.user_id] = "";
  });
};

const markAllPresent = () => {
  modalFilteredStudents.value.forEach((student) => {
    studentAttendance.value[student.user_id] = "present";
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
    }));

    await Promise.all(payloads.map(p => apiClient.post("/attendance", p)));
    alert("Attendance saved successfully!");
    showAttendanceModal.value = false;
    await fetchAttendance();
  } catch (err) {
    alert(err.response?.data?.message || "Failed to save attendance");
    console.error("Submit attendance error:", err);
  }
};

// Helper functions
const getAttendanceCount = (studentId, status) => {
  return attendanceRecords.value.filter((r) => r.student_id === studentId && r.status === status.toLowerCase()).length;
};

const getTotalDays = (studentId) => {
  return attendanceRecords.value.filter((r) => r.student_id === studentId).length;
};

const getAttendancePercentage = (studentId) => {
  const total = getTotalDays(studentId);
  const present = getAttendanceCount(studentId, "Present");
  return total > 0 ? (present / total) * 100 : 0;
};

const getStatusColor = (status) => {
  const colors = {
    present: "#22c55e",
    absent: "#ef4444",
    late: "#facc15",
  };
  return colors[status] || "#6b7280";
};

const getStatusBadgeClass = (status) => {
  const classes = {
    present: "bg-green-100 text-green-800",
    absent: "bg-red-100 text-red-800",
    late: "bg-yellow-100 text-yellow-800",
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

// Export functions
const exportCSV = () => {
  const headers = ["Student ID", "Full Name", "Class", "Date", "Status", "Notes"];
  const rows = attendanceRecords.value.map((record) => [
    record.student_id,
    `${record.student?.user?.first_name || "N/A"} ${record.student?.user?.last_name || ""}`,
    record.class?.class_name || "N/A",
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
  const doc = new jsPDF();
  doc.setFontSize(16);
  doc.text("Attendance Report", 20, 20);

  const headers = ["Student ID", "Full Name", "Class", "Date", "Status", "Notes"];
  const rows = attendanceRecords.value.map((record) => [
    record.student_id,
    `${record.student?.user?.first_name || "N/A"} ${record.student?.user?.last_name || ""}`,
    record.class?.class_name || "N/A",
    formatDate(record.date),
    record.status,
    record.notes || "",
  ]);

  doc.autoTable({
    startY: 30,
    head: [headers],
    body: rows,
  });

  doc.save(`attendance_${new Date().toISOString().split("T")[0]}.pdf`);
};

const showAnalytics = () => {
  const summary = {
    totalRecords: attendanceRecords.value.length,
    byStatus: attendanceStatuses.value.reduce((acc, status) => {
      acc[status] = attendanceRecords.value.filter((r) => r.status === status).length;
      return acc;
    }, {}),
    byClass: classes.value.reduce((acc, cls) => {
      acc[cls.class_name] = attendanceRecords.value.filter((r) => r.class_id === cls.id).length;
      return acc;
    }, {}),
  };
  console.log("Analytics Summary:", summary);
  alert("Analytics summary logged to console. Check console for details.");
};

// Watchers
watch(() => ({ ...filters.value, ...viewFilters.value }), fetchAttendance, {
  deep: true,
});

// Lifecycle hooks
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