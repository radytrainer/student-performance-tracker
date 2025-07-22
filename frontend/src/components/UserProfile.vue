<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-8">
          <h1 class="text-3xl font-bold text-white">My Profile</h1>
          <p class="text-blue-100 mt-2">Manage your account information</p>
        </div>

        <!-- Profile Content -->
        <div class="p-6">
          <!-- Profile Image Section -->
          <div
            class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6 mb-8"
          >
            <div class="relative">
              <div
                class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 border-4 border-white shadow-lg"
              >
                <img
                  v-if="user.profileImage"
                  :src="user.profileImage"
                  :alt="user.name"
                  class="w-full h-full object-cover"
                />
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center bg-gray-300"
                >
                  <svg
                    class="w-16 h-16 text-gray-600"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      d="M10 2a4 4 0 100 8 4 4 0 000-8zM4 14a6 6 0 1112 0v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1z"
                    />
                  </svg>
                </div>
              </div>

              <!-- Image Upload Button -->
              <button
                v-if="editing"
                @click="triggerImageUpload"
                class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow-lg hover:bg-blue-700 transition-colors"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
              </button>

              <!-- Hidden file input -->
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                class="hidden"
              />
            </div>

            <div class="text-center sm:text-left">
              <h2 class="text-2xl font-bold text-gray-900">
                {{ user.name || "User Name" }}
              </h2>
              <p class="text-gray-600 mt-1">
                {{ user.email || "user@example.com" }}
              </p>
              <span
                class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full mt-2"
              >
                {{ user.role || "Member" }}
              </span>
            </div>
          </div>

          <!-- Form Fields -->
          <form @submit.prevent="saveProfile" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Personal Information -->
              <div class="space-y-6">
                <h3
                  class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2"
                >
                  Personal Information
                </h3>

                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Full Name</label
                    >
                    <input
                      v-model="user.name"
                      type="text"
                      :disabled="!editing"
                      :class="inputClasses"
                      placeholder="Enter your full name"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Email</label
                    >
                    <input
                      v-model="user.email"
                      type="email"
                      :disabled="!editing"
                      :class="inputClasses"
                      placeholder="Enter your email"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Phone</label
                    >
                    <input
                      v-model="user.phone"
                      type="tel"
                      :disabled="!editing"
                      :class="inputClasses"
                      placeholder="Enter your phone number"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Bio</label
                    >
                    <textarea
                      v-model="user.bio"
                      :disabled="!editing"
                      :class="inputClasses"
                      rows="3"
                      placeholder="Tell us about yourself"
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Account Settings -->
              <div class="space-y-6">
                <h3
                  class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2"
                >
                  Account Settings
                </h3>

                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Role</label
                    >
                    <input
                      :value="user.role"
                      type="text"
                      disabled
                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Member Since</label
                    >
                    <input
                      :value="formatDate(user.createdAt)"
                      type="text"
                      disabled
                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Last Login</label
                    >
                    <input
                      :value="formatDate(user.lastLogin)"
                      type="text"
                      disabled
                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Account Status</label
                    >
                    <div class="flex items-center">
                      <span :class="statusClasses">
                        {{ user.status || "Active" }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div
              class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200"
            >
              <button
                v-if="!editing"
                type="button"
                @click="startEditing"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
              >
                Edit Profile
              </button>

              <template v-else>
                <button
                  type="button"
                  @click="cancelEdit"
                  :disabled="loading"
                  class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="loading"
                  class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors disabled:opacity-50 flex items-center"
                >
                  <svg
                    v-if="loading"
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  {{ loading ? "Saving..." : "Save Changes" }}
                </button>
              </template>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div
      v-if="message"
      :class="messageClasses"
      class="fixed top-4 right-4 p-4 rounded-md shadow-lg z-50"
    >
      <div class="flex items-center">
        <svg
          v-if="messageType === 'success'"
          class="w-5 h-5 mr-2"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        <svg
          v-else
          class="w-5 h-5 mr-2"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useProfileStore } from "@/stores/profile";

const profileStore = useProfileStore();

const editing = ref(false);
const loading = ref(false);
const originalUser = ref({});
const fileInput = ref(null);
const message = ref("");
const messageType = ref("success");

const user = reactive({
  id: "",
  name: "",
  email: "",
  phone: "",
  bio: "",
  role: "",
  status: "",
  profileImage: "",
  createdAt: "",
  lastLogin: "",
});

const inputClasses = computed(() => {
  return editing.value
    ? "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 transition-colors"
    : "w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500";
});

const statusClasses = computed(() => {
  const status = user.status?.toLowerCase();
  return {
    "px-2 py-1 text-xs rounded-full": true,
    "bg-green-100 text-green-800": status === "active",
    "bg-yellow-100 text-yellow-800": status === "pending",
    "bg-red-100 text-red-800": status === "inactive",
  };
});

const messageClasses = computed(() => {
  return messageType.value === "success"
    ? "bg-green-100 border border-green-400 text-green-700"
    : "bg-red-100 border border-red-400 text-red-700";
});

onMounted(async () => {
  await loadProfile();
});

const loadProfile = async () => {
  try {
    loading.value = true;
    const profile = await profileStore.fetchProfile();
    Object.assign(user, profile);
    originalUser.value = { ...profile };
  } catch (error) {
    showMessage("Failed to load profile", "error");
  } finally {
    loading.value = false;
  }
};

const startEditing = () => {
  editing.value = true;
  originalUser.value = { ...user };
};

const cancelEdit = () => {
  Object.assign(user, originalUser.value);
  editing.value = false;
};

const saveProfile = async () => {
  try {
    loading.value = true;
    await profileStore.updateProfile(user);
    originalUser.value = { ...user };
    editing.value = false;
    showMessage("Profile updated successfully!", "success");
  } catch (error) {
    showMessage("Failed to save profile", "error");
  } finally {
    loading.value = false;
  }
};

const triggerImageUpload = () => {
  fileInput.value?.click();
};

const handleImageUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith("image/")) {
    showMessage("Please select a valid image file", "error");
    return;
  }

  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    showMessage("Image size must be less than 5MB", "error");
    return;
  }

  try {
    loading.value = true;
    const imageUrl = await profileStore.uploadProfileImage(file);
    user.profileImage = imageUrl;
    showMessage("Profile image updated successfully!", "success");
  } catch (error) {
    showMessage("Failed to upload image", "error");
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const showMessage = (text, type = "success") => {
  message.value = text;
  messageType.value = type;
  setTimeout(() => {
    message.value = "";
  }, 5000);
};
</script>
<!-- </merged_code> -->
