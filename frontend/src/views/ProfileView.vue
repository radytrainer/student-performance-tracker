<template>
  <div
    class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-4 sm:py-8"
  >
    <div class="max-w-4xl mx-auto px-6 sm:px-8 lg:px-10">
      <!-- Header Section -->
      <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-4xl font-bold text-gray-900 mb-2">
          My Profile
        </h1>
        <p class="text-sm sm:text-base text-gray-600">
          Manage your personal information and account settings
        </p>
      </div>

      <!-- Main Profile Card -->
      <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl overflow-hidden">
        <!-- Profile Header with Gradient Background -->
        <div
          class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 px-4 sm:px-8 py-8 sm:py-12"
        >
          <div
            class="flex flex-col items-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-8"
          >
            <!-- Profile Image Section -->
            <ImageUpload
              :current-image="profileImage"
              :fallback-text="user.name || 'User'"
              :alt-text="`${user.name || 'User'} Profile Picture`"
              size="large"
              :editable="editing"
              :show-delete-button="true"
              @upload-success="handleImageUploadSuccess"
              @upload-error="handleImageUploadError"
              @delete-success="handleImageDeleteSuccess"
              @delete-error="handleImageDeleteError"
            />

            <!-- User Information -->
            <div class="text-center sm:text-left text-white flex-1">
              <h2 class="text-xl sm:text-3xl font-bold mb-2">
                {{ user.name || "Your Name" }}
              </h2>
              <p class="text-blue-100 text-base sm:text-lg mb-3">
                {{ user.email || "your.email@example.com" }}
              </p>
              <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                <span
                  class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-medium bg-white/20 backdrop-blur-sm text-white border border-white/30"
                >
                  <svg
                    class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                  {{ user.role || "Student" }}
                </span>
                <span
                  class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-medium bg-green-500/20 backdrop-blur-sm text-white border border-green-400/30"
                >
                  <span
                    class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-400 rounded-full mr-1 sm:mr-2 animate-pulse"
                  ></span>
                  Active
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Content -->
        <div class="p-4 sm:p-8">
          <div class="flex mb-6 border-b">
            <button
              @click="activeTab = 'personal'"
              :class="[
                'px-4 py-2 font-medium text-sm sm:text-base transition-colors duration-200',
                activeTab === 'personal'
                  ? 'text-blue-600 border-b-2 border-blue-600'
                  : 'text-gray-500 hover:text-gray-700',
              ]"
            >
              <div class="flex items-center">
                <svg
                  class="w-4 h-4 sm:w-5 sm:h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
                Personal Information
              </div>
            </button>
            <button
              @click="activeTab = 'account'"
              :class="[
                'px-4 py-2 font-medium text-sm sm:text-base transition-colors duration-200',
                activeTab === 'account'
                  ? 'text-blue-600 border-b-2 border-blue-600'
                  : 'text-gray-500 hover:text-gray-700',
              ]"
            >
              <div class="flex items-center">
                <svg
                  class="w-4 h-4 sm:w-5 sm:h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                Account Settings
              </div>
            </button>
          </div>

          <form
            @submit.prevent="saveProfile"
            class="space-y-6 sm:space-y-8"
            novalidate
          >
            <!-- Personal Information Tab -->
            <div v-if="activeTab === 'personal'" class="space-y-6">
              <div>
                <label
                  for="name"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                  >Full Name *</label
                >
                <div class="relative">
                  <input
                    id="name"
                    v-model="user.name"
                    type="text"
                    :class="getInputClasses('name')"
                    :disabled="!editing"
                    placeholder="Enter your full name"
                    required
                    autocomplete="name"
                    @blur="validateField('name')"
                    @input="clearFieldError('name')"
                  />
                  <svg
                    class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                </div>
                <p
                  v-if="errors.name"
                  class="text-red-500 text-xs sm:text-sm mt-1 flex items-center"
                >
                  <svg
                    class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  {{ errors.name }}
                </p>
              </div>

              <div>
                <label
                  for="email"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                  >Email Address *</label
                >
                <div class="relative">
                  <input
                    id="email"
                    v-model="user.email"
                    type="email"
                    :class="getInputClasses('email')"
                    :disabled="!editing"
                    placeholder="Enter your email address"
                    required
                    autocomplete="email"
                    @blur="validateField('email')"
                    @input="clearFieldError('email')"
                  />
                  <svg
                    class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                    />
                  </svg>
                </div>
                <p
                  v-if="errors.email"
                  class="text-red-500 text-xs sm:text-sm mt-1 flex items-center"
                >
                  <svg
                    class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  {{ errors.email }}
                </p>
              </div>

              <div>
                <label
                  for="phone"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                  >Phone Number</label
                >
                <div class="relative">
                  <input
                    id="phone"
                    v-model="user.phone"
                    type="tel"
                    :class="getInputClasses('phone')"
                    :disabled="!editing"
                    placeholder="Enter your phone number"
                    autocomplete="tel"
                    @blur="validateField('phone')"
                    @input="clearFieldError('phone')"
                  />
                  <svg
                    class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                </div>
                <p
                  v-if="errors.phone"
                  class="text-red-500 text-xs sm:text-sm mt-1 flex items-center"
                >
                  <svg
                    class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  {{ errors.phone }}
                </p>
              </div>

              <div>
                <label
                  for="bio"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                  >Bio</label
                >
                <div class="relative">
                  <textarea
                    id="bio"
                    v-model="user.bio"
                    :class="getTextareaClasses()"
                    :disabled="!editing"
                    rows="3"
                    placeholder="Tell us about yourself..."
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Account Settings Tab -->
            <div v-if="activeTab === 'account'" class="space-y-6">
              <div>
                <label
                  for="role"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                  >Role</label
                >
                <div class="relative">
                  <input
                    id="role"
                    :value="user.role"
                    type="text"
                    class="w-full pl-8 sm:pl-10 pr-4 py-2.5 sm:py-3 border border-gray-200 rounded-lg sm:rounded-xl bg-gray-50 text-gray-600 font-medium focus:outline-none text-sm sm:text-base"
                    disabled
                  />
                  <svg
                    class="absolute left-2.5 sm:left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
              </div>

              <div>
                <label
                  for="memberSince"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                  >Member Since</label
                >
                <div class="relative">
                  <input
                    id="memberSince"
                    :value="formatDate(user.createdAt)"
                    type="text"
                    class="w-full pl-8 sm:pl-10 pr-4 py-2.5 sm:py-3 border border-gray-200 rounded-lg sm:rounded-xl bg-gray-50 text-gray-600 font-medium focus:outline-none text-sm sm:text-base"
                    disabled
                  />
                  <svg
                    class="absolute left-2.5 sm:left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>
              </div>

              <div>
                <label
                  for="lastLogin"
                  class="block text-sm font-semibold text-gray-700 mb-2"
                  >Last Login</label
                >
                <div class="relative">
                  <input
                    id="lastLogin"
                    :value="formatDate(user.lastLogin)"
                    type="text"
                    class="w-full pl-8 sm:pl-10 pr-4 py-2.5 sm:py-3 border border-gray-200 rounded-lg sm:rounded-xl bg-gray-50 text-gray-600 font-medium focus:outline-none text-sm sm:text-base"
                    disabled
                  />
                  <svg
                    class="absolute left-2.5 sm:left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 pointer-events-none"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
              </div>

              <!-- Profile Completion -->
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"
                  >Profile Completion</label
                >
                <div class="bg-gray-50 rounded-lg sm:rounded-xl p-3 sm:p-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm font-medium text-gray-700"
                      >{{ profileCompletion }}% Complete</span
                    >
                    <span class="text-xs sm:text-sm text-gray-500">{{
                      getCompletionStatus()
                    }}</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                      :style="{ width: `${profileCompletion}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div
              class="flex flex-col space-y-3 sm:flex-row sm:justify-end sm:space-y-0 sm:space-x-4 pt-6 sm:pt-8 border-t border-gray-200"
            >
              <button
                v-if="!editing"
                type="button"
                @click="startEditing"
                class="w-full sm:w-auto inline-flex items-center justify-center px-6 sm:px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg sm:rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg touch-manipulation"
              >
                <svg
                  class="w-4 h-4 sm:w-5 sm:h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
                Edit Profile
              </button>

              <template v-else>
                <button
                  type="button"
                  @click="cancelEdit"
                  :disabled="loading"
                  class="w-full sm:w-auto inline-flex items-center justify-center px-6 sm:px-8 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg sm:rounded-xl hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 touch-manipulation"
                >
                  <svg
                    class="w-4 h-4 sm:w-5 sm:h-5 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="loading || !isFormValid"
                  class="w-full sm:w-auto inline-flex items-center justify-center px-6 sm:px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-lg sm:rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 disabled:opacity-50 shadow-lg touch-manipulation"
                >
                  <svg
                    v-if="loading"
                    class="animate-spin -ml-1 mr-2 h-4 w-4 sm:h-5 sm:w-5 text-white"
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
                  <svg
                    v-else
                    class="w-4 h-4 sm:w-5 sm:h-5 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  {{ loading ? "Saving..." : "Save Changes" }}
                </button>
              </template>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-x-full opacity-0"
      enter-to-class="transform translate-x-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-x-0 opacity-100"
      leave-to-class="transform translate-x-full opacity-0"
    >
      <div
        v-if="notification.show"
        :class="getNotificationClasses()"
        class="fixed top-20 right-4 left-9 sm:left-auto sm:right-4 p-4 rounded-xl shadow-2xl z-50 max-w-sm mx-auto sm:mx-0"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg
              v-if="notification.type === 'success'"
              class="w-5 h-5 sm:w-6 sm:h-6 text-green-600"
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
              class="w-5 h-5 sm:w-6 sm:h-6 text-red-600"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900">
              {{ notification.message }}
            </p>
          </div>
          <button
            @click="notification.show = false"
            class="ml-4 text-gray-400 hover:text-gray-600 touch-manipulation"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from "vue";
import { useAuthStore } from "@/stores/auth";
import ImageUpload from "@/components/ImageUpload.vue";
import profileImageAPI from "@/api/profileImage";

const authStore = useAuthStore();

// Reactive state
const editing = ref(false);
const loading = ref(false);
const originalUser = ref({});
const profileImage = ref("");
const activeTab = ref("personal");

const user = reactive({
  name: "Em Sophy",
  email: "sophy.em@student.passerellesnumeriques.org",
  phone: "+855 12 345 678",
  bio: "Computer Science student passionate about web development and technology.",
  role: "Student",
  createdAt: "2023-01-15",
  lastLogin: "2024-01-20",
});

const errors = reactive({
  name: "",
  email: "",
  phone: "",
});

const notification = reactive({
  show: false,
  message: "",
  type: "success",
});

// Computed properties
const isFormValid = computed(() => {
  return (
    user.name && user.email && !Object.values(errors).some((error) => error)
  );
});

const profileCompletion = computed(() => {
  const fields = [
    user.name,
    user.email,
    user.phone,
    user.bio,
    profileImage.value,
  ];
  const completedFields = fields.filter(
    (field) => field && field.toString().trim()
  ).length;
  return Math.round((completedFields / fields.length) * 100);
});

// Methods
const getInitials = (name) => {
  if (!name) return "";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

const loadProfile = async () => {
  try {
    loading.value = true;

    // Initialize auth store
    await authStore.initialize();

    // Get user data from auth store
    const userData = authStore.user;

    if (userData) {
      // Update user data
      Object.assign(user, userData);

      // Load profile image from API
      try {
        const imageResponse = await profileImageAPI.getProfileImage();
        if (imageResponse.data.image_url) {
          profileImage.value = imageResponse.data.image_path;
        }
      } catch (err) {
        // No profile image or error loading - that's ok
        console.log("No profile image found");
      }

      // Store original values
      originalUser.value = {
        ...userData,
        profileImage: profileImage.value,
      };
    }
  } catch (error) {
    console.error("Load profile error:", error);
    showNotification("Failed to load profile", "error");
  } finally {
    loading.value = false;
  }
};

const startEditing = () => {
  editing.value = true;
  originalUser.value = { ...user, profileImage: profileImage.value };
  clearErrors();
};

const cancelEdit = () => {
  Object.assign(user, originalUser.value);
  profileImage.value = originalUser.value.profileImage || "";
  editing.value = false;
  clearErrors();
};

const saveProfile = async () => {
  if (!validateForm()) {
    showNotification("Please fix the errors before saving", "error");
    return;
  }

  try {
    loading.value = true;

    // Update the auth store with new user data
    const updatedUser = { ...user, profile_picture: profileImage.value };

    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 1000));

    // Update the auth store
    authStore.updateUser(updatedUser);

    originalUser.value = { ...updatedUser };
    editing.value = false;
    showNotification("Profile updated successfully!", "success");
  } catch (error) {
    console.error("Save profile error:", error);
    showNotification("Failed to save profile", "error");
  } finally {
    loading.value = false;
  }
};

// Image upload event handlers
const handleImageUploadSuccess = (data) => {
  profileImage.value = data.imagePath;
  // Update the auth store with the new profile picture
  authStore.updateUser({
    ...authStore.user,
    profile_picture: data.imagePath,
  });
  showNotification("Profile image uploaded successfully!", "success");
};

const handleImageUploadError = (message) => {
  showNotification(message, "error");
};

const handleImageDeleteSuccess = (data) => {
  profileImage.value = null;
  // Update the auth store to remove the profile picture
  authStore.updateUser({
    ...authStore.user,
    profile_picture: null,
  });
  showNotification("Profile image deleted successfully!", "success");
};

const handleImageDeleteError = (message) => {
  showNotification(message, "error");
};

const validateForm = () => {
  clearErrors();
  let isValid = true;

  if (!user.name || !user.name.trim()) {
    errors.name = "Name is required";
    isValid = false;
  }

  if (!user.email || !user.email.trim()) {
    errors.email = "Email is required";
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user.email.trim())) {
    errors.email = "Please enter a valid email address";
    isValid = false;
  }

  if (
    user.phone &&
    user.phone.trim() &&
    !/^[\+]?[1-9][\d\s\-()]{7,15}$/.test(user.phone.replace(/\s/g, ""))
  ) {
    errors.phone = "Please enter a valid phone number";
    isValid = false;
  }

  return isValid;
};

const validateField = (field) => {
  switch (field) {
    case "name":
      if (!user.name || !user.name.trim()) {
        errors.name = "Name is required";
      }
      break;
    case "email":
      if (!user.email || !user.email.trim()) {
        errors.email = "Email is required";
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user.email.trim())) {
        errors.email = "Please enter a valid email address";
      }
      break;
    case "phone":
      if (
        user.phone &&
        user.phone.trim() &&
        !/^[\+]?[1-9][\d\s\-()]{7,15}$/.test(user.phone.replace(/\s/g, ""))
      ) {
        errors.phone = "Please enter a valid phone number";
      }
      break;
  }
};

const clearFieldError = (field) => {
  if (errors[field]) {
    errors[field] = "";
  }
};

const clearErrors = () => {
  Object.keys(errors).forEach((key) => {
    errors[key] = "";
  });
};

const getInputClasses = (field) => {
  const baseClasses =
    "w-full pl-8 sm:pl-10 pr-4 py-2.5 sm:py-3 border rounded-lg sm:rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 font-medium text-sm sm:text-base";
  const enabledClasses =
    "border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white";
  const disabledClasses = "border-gray-200 bg-gray-50 text-gray-600";
  const errorClasses =
    "border-red-300 focus:border-red-500 focus:ring-red-500 bg-red-50";

  if (errors[field]) {
    return `${baseClasses} ${errorClasses}`;
  }
  return editing.value
    ? `${baseClasses} ${enabledClasses}`
    : `${baseClasses} ${disabledClasses}`;
};

const getTextareaClasses = () => {
  const baseClasses =
    "w-full px-4 py-2.5 sm:py-3 border rounded-lg sm:rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 font-medium resize-none text-sm sm:text-base";
  const enabledClasses =
    "border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white";
  const disabledClasses = "border-gray-200 bg-gray-50 text-gray-600";

  return editing.value
    ? `${baseClasses} ${enabledClasses}`
    : `${baseClasses} ${disabledClasses}`;
};

const getNotificationClasses = () => {
  return notification.type === "success"
    ? "bg-white border border-green-200 text-gray-900"
    : "bg-white border border-red-200 text-gray-900";
};

const getCompletionStatus = () => {
  if (profileCompletion.value === 100) return "Excellent!";
  if (profileCompletion.value >= 80) return "Almost there!";
  if (profileCompletion.value >= 60) return "Good progress";
  if (profileCompletion.value >= 40) return "Getting started";
  return "Just started";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  } catch (error) {
    return "N/A";
  }
};

const showNotification = (message, type = "success") => {
  notification.message = message;
  notification.type = type;
  notification.show = true;

  setTimeout(() => {
    notification.show = false;
  }, 5000);
};

onMounted(async () => {
  await nextTick();
  await loadProfile();
});
</script>
