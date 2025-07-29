<template>
  <header class="bg-white shadow-md rounded-lg">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center space-x-4">
          <div>
            <h1
              class="text-5xl font-bold tracking-tight mb-4 bg-gradient-to-r from-purple-600 to-blue-500 text-transparent bg-clip-text"
            >
              Welcome back, {{ user?.first_name }}!
            </h1>
            <p class="text-sm text-gray-600 font-medium mb-2">
              Teacher code: {{ user?.teacher_code || "N/A" }}
            </p>
            <p class="text-sm text-gray-600 font-medium mb-2">
              Department: {{ user?.department || "N/A" }}
            </p>
            <p class="text-sm text-gray-600 font-medium mb-2">
              Email: {{ user?.email || "N/A" }}
            </p>
            <p class="text-xs text-gray-500">
              Last login: {{ formattedLastLogin }}
            </p>
          </div>
        </div>

        <div>
          <!-- add image -->
          <img src="@/assets/TeacherHeader.png" alt="" class="w-66 h-60" />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useAuth } from "@/composables/useAuth";
import { computed } from "vue";
// import moment from "moment";

const { user } = useAuth();

const formattedLastLogin = computed(() => {
  return user.value?.last_login
    ? moment(user.value.last_login).fromNow()
    : "N/A";
});
</script>
