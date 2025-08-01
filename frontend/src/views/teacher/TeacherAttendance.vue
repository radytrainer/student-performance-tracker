<template>
  <div class="p-6">
    <!-- 🔹 1. Attendance Dashboard / Class Selection -->
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Manage Attendance</h1>
    <div class="p-6">
      <!-- 🔹 1. Attendance Dashboard / Class Selection -->
      <h1 class="text-2xl font-bold mb-6 text-gray-800">Manage Attendance</h1>
      <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 items-end"
      >
        <div class="w-full">
          <label class="block mb-1 font-medium text-gray-700">Class</label>
          <select
            v-model="filters.class_id"
            @change="fetchAttendance"
            class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Classes</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
              {{ cls.class_name }}
            </option>
          </select>
        </div>
        <div class="w-full">
          <label class="block mb-1 font-medium text-gray-700">Date</label>
          <input
            type="date"
            v-model="filters.date"
            value="defaultDate"
            @change="fetchAttendance"
            class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div class="w-full">
          <label class="block mb-1 font-medium text-gray-700">Subject</label>
          <select
            v-model="filters.subject_id"
            @change="fetchAttendance"
            class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Subjects</option>
            <option
              v-for="subject in subjects"
              :key="subject.id"
              :value="subject.id"
            >
              {{ subject.subject_name }}
            </option>
          </select>
        </div>
        <div class="w-full">
          <button
            @click="openTakeAttendanceModal"
            class="w-full flex items-center justify-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors mt-6 sm:mt-0"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                clip-rule="evenodd"
              />
            </svg>
            Take Attendance
          </button>
        </div>
      </div>
    </div>

    <!-- 🔹 2. Take Attendance Interface -->
    <div
      v-if="showAttendanceModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white w-full max-w-lg rounded-lg shadow-xl max-h-[90vh] flex flex-col"
      >
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-xl font-bold text-gray-800">Take Attendance</h3>
        </div>
        <div class="overflow-y-auto p-6 flex-1">
          <form @submit.prevent="submitAttendance">
            <div class="mb-4">
              <label class="block font-medium mb-1 text-gray-700">Date</label>
              <input
                type="date"
                v-model="newAttendance.date"
                value="defaultDate"
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                required
              />
            </div>
            <button
              type="button"
              @click="markAllPresent"
              class="mb-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors"
            >
              Mark All Present
            </button>
            <div
              v-for="student in filteredStudents"
              :key="student.id"
              class="mb-4 border-b pb-2"
            >
              <label class="block font-medium mb-1 text-gray-700">
                {{ student.user?.first_name }}
                {{ student.user?.last_name }} (ID: {{ student.user_id }})
              </label>
              <select
                v-model="studentAttendance[student.user_id]"
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option
                  v-for="status in attendanceStatuses"
                  :key="status"
                  :value="status"
                >
                  {{ status }}
                </option>
              </select>
              <input
                v-model="studentAttendanceNotes[student.user_id]"
                type="text"
                placeholder="Notes (e.g., sick leave)"
                class="mt-2 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors"
            >
              Save Attendance
            </button>
          </form>
        </div>
        <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
          <button
            type="button"
            @click="showAttendanceModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- 🔹 3. View Attendance Records -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Attendance Records</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
          <label class="block mb-1 font-medium text-gray-700">Class</label>
          <select
            v-model="viewFilters.class_id"
            @change="fetchAttendance"
            class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Classes</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
              {{ cls.class_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Student</label>
          <select
            v-model="viewFilters.student_id"
            @change="fetchAttendance"
            class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Students</option>
            <option
              v-for="student in students"
              :key="student.user_id"
              :value="student.user_id"
            >
              {{ student.user?.first_name }} {{ student.user?.last_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Date Range</label>
          <input
            type="date"
            v-model="viewFilters.start_date"
            @change="fetchAttendance"
            class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div>
          <label class="block mb-1 font-medium text-gray-700">Status</label>
          <select
            v-model="viewFilters.status"
            @change="fetchAttendance"
            class="w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Statuses</option>
            <option
              v-for="status in attendanceStatuses"
              :key="status"
              :value="status"
            >
              {{ status }}
            </option>
          </select>
        </div>
      </div>
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div
          class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"
        ></div>
      </div>
      <div v-else-if="error" class="text-red-500 p-4 bg-red-50 rounded-md">
        {{ error }}
      </div>
      <div
        v-else-if="attendanceRecords.length === 0"
        class="text-gray-500 p-4 text-center"
      >
        No attendance records available.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Student ID
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Full Name
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Class
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Date
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Notes
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="record in attendanceRecords"
              :key="record.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.student_id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.student?.user?.first_name }}
                {{ record.student?.user?.last_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.class?.class_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(record.date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getStatusBadgeClass(record.status)"
                >
                  {{ record.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ record.notes || "N/A" }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- 📅 Calendar View (Optional) -->
      <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-800">Calendar View</h3>
        <div class="w-full h-64 bg-gray-100 flex items-center justify-center">
          Calendar Placeholder (Green: Present, Red: Absent, Yellow: Late)
        </div>
      </div>
      <!-- 📈 Attendance Summary per Student with Charts -->
      <div
        v-for="student in uniqueStudents"
        :key="student.user_id"
        class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200"
      >
        <h3 class="text-lg font-semibold text-gray-800">
          {{ student.user?.first_name }} {{ student.user?.last_name }}
        </h3>
        <div class="grid grid-cols-2 gap-4 mt-2">
          <div>
            <p class="text-gray-600">
              Present: {{ getAttendanceCount(student.user_id, "Present") }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">
              Absent: {{ getAttendanceCount(student.user_id, "Absent") }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">
              Late: {{ getAttendanceCount(student.user_id, "Late") }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">
              Excused: {{ getAttendanceCount(student.user_id, "Excused") }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">
              Total Days: {{ getTotalDays(student.user_id) }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">
              Attendance %:
              {{ getAttendancePercentage(student.user_id).toFixed(1) }}%
            </p>
          </div>
        </div>
        <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
          <div
            :style="{
              width: getAttendancePercentage(student.user_id) + '%',
              backgroundColor:
                getAttendancePercentage(student.user_id) < 75
                  ? '#ef4444'
                  : '#22c55e',
            }"
            class="h-2.5 rounded-full"
          ></div>
        </div>
      </div>
      <!-- Analytics Charts -->
      <div class="mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
          Attendance Analysis Dashboard
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Attendance % by Grade -->
          <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
              Attendance % by Grade
            </h3>
            <chart
              type="bar"
              :data="{
                labels: ['9', '10', '11', '12'],
                datasets: [
                  {
                    label: 'Past Week',
                    data: [92, 93, 91, 91],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                  },
                  {
                    label: 'Current Week',
                    data: [97, 95, 91, 95],
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                  },
                  {
                    label: 'YTD',
                    data: [98, 95, 82, 91],
                    backgroundColor: 'rgba(255, 205, 86, 0.7)',
                    borderColor: 'rgba(255, 205, 86, 1)',
                    borderWidth: 1,
                  },
                ],
              }"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                  y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Attendance %' },
                  },
                },
              }"
              class="w-full h-64"
            ></chart>
          </div>
          <!-- % Student Absent YTD -->
          <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
              % Student Absent YTD
            </h3>
            <chart
              type="pie"
              :data="{
                labels: ['0 Days', '1-4 Days', '5-9 Days', '10-14 Days'],
                datasets: [
                  {
                    label: 'Absent %',
                    data: [11, 4, 30, 55],
                    backgroundColor: [
                      'rgba(201, 203, 207, 0.7)',
                      'rgba(54, 162, 235, 0.7)',
                      'rgba(255, 205, 86, 0.7)',
                      'rgba(255, 99, 132, 0.7)',
                    ],
                    borderColor: 'rgba(255, 255, 255, 0.8)',
                    borderWidth: 1,
                  },
                ],
              }"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } },
              }"
              class="w-full h-64"
            ></chart>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
          <!-- Attendance % by Day of the Week -->
          <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
              Attendance % by Day of the Week
            </h3>
            <chart
              type="radar"
              :data="{
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Web', 'Sat'],
                datasets: [
                  {
                    label: 'Attendance %',
                    data: [82, 90, 0, 0, 95, 85, 0],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                  },
                ],
              }"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                  r: { beginAtZero: true, max: 100, ticks: { stepSize: 20 } },
                },
              }"
              class="w-full h-64"
            ></chart>
          </div>
          <!-- Suspension by Grade -->
          <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
              Suspension by Grade
            </h3>
            <chart
              type="bar"
              :data="{
                labels: ['9', '10', '11', '12'],
                datasets: [
                  {
                    label: 'Out of School',
                    data: [2.1, 1.1, 2.1, 0.8],
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                  },
                  {
                    label: 'In School',
                    data: [1.1, 1.1, 1.1, 1.1],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                  },
                ],
              }"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                  y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Count' },
                  },
                },
              }"
              class="w-full h-64"
            ></chart>
          </div>
        </div>
      </div>
    </div>

    <!-- 🔹 4. Export / Reporting Features -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Export / Reports</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          @click="exportCSV"
          class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
        >
          📄 Export CSV
        </button>
        <button
          @click="exportPDF"
          class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
        >
          📄 Export PDF
        </button>
        <button
          @click="showAnalytics"
          class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
        >
          📊 Analytics View
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";

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
});

const loading = ref(false);
const error = ref(null);
const attendanceRecords = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]);
const showAttendanceModal = ref(false);
const attendanceStatuses = ref(["Present", "Absent", "Late", "Excused"]);

const filters = ref({
  class_id: "",
  date: new Date("2025-08-01T10:08:00+07:00").toISOString().split("T")[0],
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
  date: new Date("2025-08-01T10:08:00+07:00").toISOString().split("T")[0],
});

const studentAttendance = ref({});
const studentAttendanceNotes = ref({});

const defaultDate = computed(
  () => new Date("2025-08-01T10:08:00+07:00").toISOString().split("T")[0]
);

const fetchAll = async () => {
  loading.value = true;
  try {
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
    error.value =
      "Failed to load data: " + (err.response?.data?.message || err.message);
  } finally {
    loading.value = false;
  }
};

const fetchAttendance = async () => {
  loading.value = true;
  try {
    const params = { ...filters.value, ...viewFilters.value };
    const res = await apiClient.get("/attendances", { params });
    attendanceRecords.value = res.data.data;
  } catch (err) {
    error.value =
      "Failed to load attendance records: " +
      (err.response?.data?.message || err.message);
  } finally {
    loading.value = false;
  }
};

const openTakeAttendanceModal = () => {
  showAttendanceModal.value = true;
  resetAttendanceForm();
};

const resetAttendanceForm = () => {
  newAttendance.value.class_id = filters.value.class_id || "";
  newAttendance.value.date = filters.value.date;
  studentAttendance.value = {};
  studentAttendanceNotes.value = {};
  filteredStudents.value.forEach((student) => {
    studentAttendance.value[student.user_id] = "Present";
    studentAttendanceNotes.value[student.user_id] = "";
  });
};

const filteredStudents = computed(() => {
  if (!filters.value.class_id) return students.value;
  return students.value.filter(
    (student) => student.class_id === filters.value.class_id
  );
});

const markAllPresent = () => {
  filteredStudents.value.forEach((student) => {
    studentAttendance.value[student.user_id] = "Present";
    studentAttendanceNotes.value[student.user_id] = "";
  });
};

const submitAttendance = async () => {
  try {
    const payloads = filteredStudents.value.map((student) => ({
      student_id: student.user_id,
      class_id: newAttendance.value.class_id,
      date: newAttendance.value.date,
      status: studentAttendance.value[student.user_id],
      notes: studentAttendanceNotes.value[student.user_id] || null,
      recorded_by: localStorage.getItem("user_id") || null,
      recorded_at: new Date().toISOString(),
    }));
    await apiClient.post("/attendances/bulk", payloads);
    showAttendanceModal.value = false;
    await fetchAttendance();
  } catch (err) {
    alert(err.response?.data?.message || "Failed to save attendance");
  }
};

const uniqueStudents = computed(() => {
  const studentIds = [
    ...new Set(attendanceRecords.value.map((r) => r.student_id)),
  ];
  return students.value.filter((s) => studentIds.includes(s.user_id));
});

const getAttendanceCount = (studentId, status) => {
  return attendanceRecords.value.filter(
    (r) => r.student_id === studentId && r.status === status
  ).length;
};

const getTotalDays = (studentId) => {
  return attendanceRecords.value.filter((r) => r.student_id === studentId)
    .length;
};

const getAttendancePercentage = (studentId) => {
  const total = getTotalDays(studentId);
  const present = getAttendanceCount(student.user_id, "Present");
  return total > 0 ? (present / total) * 100 : 0;
};

const exportCSV = () => {
  const headers = [
    "Student ID",
    "Full Name",
    "Class",
    "Date",
    "Status",
    "Notes",
  ];
  const rows = attendanceRecords.value.map((record) => [
    record.student_id,
    `${record.student?.user?.first_name} ${record.student?.user?.last_name}`,
    record.class?.class_name,
    formatDate(record.date),
    record.status,
    record.notes || "",
  ]);
  const csvContent = [
    headers.join(","),
    ...rows.map((row) => row.map((cell) => `"${cell}"`).join(",")),
  ].join("\n");
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
```
