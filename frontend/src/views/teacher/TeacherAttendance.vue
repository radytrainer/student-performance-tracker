<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-4 md:p-6">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-slate-900">Attendance Manager</h1>
            <p class="text-slate-600 mt-1">Track and manage student attendance efficiently</p>
          </div>
          <button 
            @click="openTakeAttendanceModal" 
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-sm hover:shadow-md"
          >
            Take Attendance
          </button>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-slate-900">Filter Records</h2>
          <button 
            @click="openAddStudentModal" 
            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm"
          >
            ➕ Add Student
          </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Class 1</label>
            <select 
              v-model="viewFilters.class_id" 
              @change="fetchAttendance" 
              class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >
              <option value="">All Classes</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Class 2</label>
            <select 
              v-model="viewFilters.class_id2" 
              @change="fetchAttendance" 
              class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >
              <option value="">All Classes</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Student</label>
            <select 
              v-model="viewFilters.student_id" 
              @change="fetchAttendance" 
              class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >
              <option value="">All Students</option>
              <option v-for="student in students" :key="student.user_id" :value="student.user_id">
                {{ student.user?.first_name || "N/A" }} {{ student.user?.last_name || "" }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Date Range</label>
            <input 
              type="date" 
              v-model="viewFilters.start_date" 
              @change="fetchAttendance" 
              class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
            <select 
              v-model="viewFilters.status" 
              @change="fetchAttendance" 
              class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >
              <option value="">All Statuses</option>
              <option v-for="status in attendanceStatuses" :key="status" :value="status">
                {{ status.charAt(0).toUpperCase() + status.slice(1) }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Attendance Records -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-xl font-semibold text-slate-900">Attendance Records</h2>
            <div class="flex gap-2">
              <button 
                @click="exportCSV" 
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm"
              >
                📄 Export CSV
              </button>
            </div>
          </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-600 border-t-transparent"></div>
        </div>
        
        <div v-else-if="error" class="p-6">
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
            {{ error }}
          </div>
        </div>
        
        <div v-else-if="attendanceRecords.length === 0" class="p-12 text-center">
          <div class="text-slate-400 text-lg mb-2">📋</div>
              <p class="text-slate-600">No attendance records found</p>
              <p class="text-sm text-slate-500 mt-1">Try adjusting your filters or take attendance first</p>
        </div>
        
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Student</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Class</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="record in attendanceRecords" :key="record.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-200">
                      <img 
                        v-if="record.student?.user?.profile_picture"
                        :src="getImageUrl(record.student.user.profile_picture)"
                        :alt="record.student?.user?.first_name"
                        class="w-full h-full object-cover"
                        @error="handleImageError"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center text-sm font-medium text-slate-600">
                        {{ (record.student?.user?.first_name || 'N')[0] }}{{ (record.student?.user?.last_name || 'A')[0] }}
                      </div>
                    </div>
                    <div>
                      <p class="font-medium text-slate-900">
                        {{ record.student?.user?.first_name || "N/A" }} {{ record.student?.user?.last_name || "" }}
                      </p>
                      <p class="text-sm text-slate-500">ID: {{ record.student_id }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-slate-900">{{ record.class?.class_name || "N/A" }}</td>
                <td class="px-6 py-4 text-slate-900">{{ formatDate(record.date) }}</td>
                <td class="px-6 py-4">
                  <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusBadgeClass(record.status)"
                  >
                    {{ record.status.charAt(0).toUpperCase() + record.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-slate-600">{{ record.notes || "—" }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Take Attendance Modal -->
      <div v-if="showAttendanceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl max-h-[90vh] flex flex-col">
          <div class="p-6 border-b border-slate-200">
            <h3 class="text-2xl font-bold text-slate-900">Take Attendance</h3>
            <p class="text-slate-600 mt-1">Mark attendance for students in the selected class</p>
          </div>
          
          <div class="overflow-y-auto p-6 flex-1">
            <form @submit.prevent="submitAttendance" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-2">Class *</label>
                  <select 
                    v-model="newAttendance.class_id" 
                    @change="onClassChange" 
                    class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                    required
                  >
                    <option value="">Select Class</option>
                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-2">Date *</label>
                  <input 
                    type="date" 
                    v-model="newAttendance.date" 
                    class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                    required 
                  />
                </div>
              </div>

              <div v-if="newAttendance.class_id" class="flex justify-between items-center">
                <h4 class="text-lg font-semibold text-slate-900">Students ({{ modalFilteredStudents.length }})</h4>
                <button 
                  type="button" 
                  @click="markAllPresent" 
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm"
                >
                  Mark All Present
                </button>
              </div>

              <div v-if="modalFilteredStudents.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                <div 
                  v-for="student in modalFilteredStudents" 
                  :key="student.user_id" 
                  class="border border-slate-200 rounded-xl p-4 bg-slate-50 hover:bg-slate-100 transition-colors"
                >
                  <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-200">
                      <img 
                        v-if="student.user?.profile_picture"
                        :src="getImageUrl(student.user.profile_picture)"
                        :alt="student.user?.first_name"
                        class="w-full h-full object-cover"
                        @error="handleImageError"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center text-sm font-medium text-slate-600">
                        {{ (student.user?.first_name || 'N')[0] }}{{ (student.user?.last_name || 'A')[0] }}
                      </div>
                    </div>
                    <div>
                      <p class="font-medium text-slate-900">
                        {{ student.user?.first_name || "N/A" }} {{ student.user?.last_name || "" }}
                      </p>
                      <p class="text-xs text-slate-500">ID: {{ student.user_id }}</p>
                    </div>
                  </div>
                  
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-slate-600 mb-1">Status *</label>
                      <select 
                        v-model="studentAttendance[student.user_id]" 
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                        required
                      >
                        <option value="">Select Status</option>
                        <option v-for="status in attendanceStatuses" :key="status" :value="status">
                          {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                        </option>
                      </select>
                    </div>
                    
                    <div>
                      <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                      <input 
                        v-model="studentAttendanceNotes[student.user_id]" 
                        type="text" 
                        placeholder="Optional notes..." 
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div v-else-if="newAttendance.class_id" class="text-center py-12">
                <div class="text-slate-400 text-4xl mb-4">👥</div>
                <p class="text-slate-600">No students found for the selected class</p>
              </div>

              <div v-else class="text-center py-12">
                <div class="text-slate-400 text-4xl mb-4">🏫</div>
                <p class="text-slate-600">Please select a class to view students</p>
              </div>

              <div class="flex gap-3 pt-6 border-t border-slate-200">
                <button 
                  type="submit" 
                  :disabled="!canSubmit"
                  class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl font-medium transition-colors"
                >
                  Save Attendance
                </button>
                <button 
                  type="button" 
                  @click="closeModal"
                  class="px-6 py-3 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 transition-colors font-medium"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Add Student Modal -->
      <div v-if="showAddStudentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl">
          <div class="p-6 border-b border-slate-200">
            <h3 class="text-2xl font-bold text-slate-900">Add New Student</h3>
            <p class="text-slate-600 mt-1">Add a new student to a class</p>
          </div>
          
          <form @submit.prevent="submitAddStudent" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">First Name *</label>
              <input 
                v-model="newStudent.first_name" 
                type="text" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Last Name *</label>
              <input 
                v-model="newStudent.last_name" 
                type="text" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
              <input 
                v-model="newStudent.email" 
                type="email" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Class *</label>
              <select 
                v-model="newStudent.class_id" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                required
              >
                <option value="">Select Class</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.class_name }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Admission Number</label>
              <input 
                v-model="newStudent.admission_number" 
                type="text" 
                class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
              />
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-200">
              <button 
                type="submit" 
                :disabled="!canAddStudent"
                class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-slate-400 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl font-medium transition-colors"
              >
                Add Student
              </button>
              <button 
                type="button" 
                @click="closeAddStudentModal"
                class="px-6 py-3 border border-slate-300 rounded-xl text-slate-700 hover:bg-slate-50 transition-colors font-medium"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";

// API setup
const apiClient = axios.create({
  baseURL: "http://localhost:8000/api",
  headers: { "Content-Type": "application/json", Accept: "application/json" },
});

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// State
const loading = ref(false);
const error = ref(null);
const attendanceRecords = ref([]);
const classes = ref([]);
const students = ref([]);
const showAttendanceModal = ref(false);
const attendanceStatuses = ref(["present", "absent", "late"]);

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

// Computed properties
const modalFilteredStudents = computed(() => {
  if (!newAttendance.value.class_id) return [];
  return students.value.filter(student => student.current_class_id === newAttendance.value.class_id);
});

const canSubmit = computed(() => {
  if (!newAttendance.value.class_id || !newAttendance.value.date) return false;
  if (modalFilteredStudents.value.length === 0) return false;
  return modalFilteredStudents.value.every(student => 
    studentAttendance.value[student.user_id] && studentAttendance.value[student.user_id] !== ""
  );
});

// Methods
const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  // Handle both relative and absolute URLs
  if (imagePath.startsWith('http')) {
    return imagePath;
  }
  return `http://localhost:8000/storage/${imagePath}`;
};

const handleImageError = (event) => {
  event.target.style.display = 'none';
  event.target.parentElement.querySelector('.fallback-initials').style.display = 'flex';
};

const fetchAll = async () => {
  loading.value = true;
  try {
    const [cls, stu] = await Promise.all([
      apiClient.get("/classes"),
      apiClient.get("/students"),
    ]);
     classes.value = cls.data.data || [];
    students.value = stu.data.data || [];
    await fetchAttendance();
  } catch (err) {
    error.value = `Failed to load data: ${err.response?.data?.message || err.message}`;
  } finally {
    loading.value = false;
  }
};

const fetchAttendance = async () => {
  loading.value = true;
  try {
    const res = await apiClient.get("/attendance", { params: viewFilters.value });
    attendanceRecords.value = res.data.attendances || [];
    error.value = null;
  } catch (err) {
    console.error('Attendance fetch error:', err);
    error.value = `Failed to load attendance: ${err.response?.data?.message || err.message}`;
  } finally {
    loading.value = false;
  }
};

const openTakeAttendanceModal = () => {
  showAttendanceModal.value = true;
  resetAttendanceForm();
};

const closeModal = () => {
  showAttendanceModal.value = false;
  resetAttendanceForm();
};

const resetAttendanceForm = () => {
  newAttendance.value.class_id = viewFilters.value.class_id || "";
  newAttendance.value.date = new Date().toISOString().split("T")[0];
  studentAttendance.value = {};
  studentAttendanceNotes.value = {};
};

const onClassChange = () => {
  studentAttendance.value = {};
  studentAttendanceNotes.value = {};
  modalFilteredStudents.value.forEach(student => {
    studentAttendance.value[student.user_id] = "present";
    studentAttendanceNotes.value[student.user_id] = "";
  });
};

const markAllPresent = () => {
  modalFilteredStudents.value.forEach(student => {
    studentAttendance.value[student.user_id] = "present";
    studentAttendanceNotes.value[student.user_id] = "";
  });
};

const submitAttendance = async () => {
  if (!canSubmit.value) {
    alert("Please fill in all required fields.");
    return;
  }

  try {
    const payloads = modalFilteredStudents.value.map(student => ({
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
  }
};

const getStatusBadgeClass = (status) => {
  const classes = {
    present: "bg-green-100 text-green-800",
    absent: "bg-red-100 text-red-800",
    late: "bg-yellow-100 text-yellow-800",
  };
  return classes[status] || "bg-slate-100 text-slate-800";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  } catch (error) {
    return "Invalid Date";
  }
};

const exportCSV = () => {
  const headers = ["Student ID", "Full Name", "Class", "Date", "Status", "Notes"];
  const rows = attendanceRecords.value.map(record => [
    record.student_id,
    `${record.student?.user?.first_name || "N/A"} ${record.student?.user?.last_name || ""}`,
    record.class?.class_name || "N/A",
    formatDate(record.date),
    record.status,
    record.notes || "",
  ]);
  
  const csvContent = [headers.join(","), ...rows.map(row => 
    row.map(cell => `"${cell}"`).join(",")
  )].join("\n");
  
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = `attendance_${new Date().toISOString().split("T")[0]}.csv`;
  link.click();
};

onMounted(fetchAll);
</script>
