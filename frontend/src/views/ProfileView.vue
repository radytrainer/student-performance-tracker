<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-6 sm:py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Enhanced Header Section -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
              My Profile
            </h1>
            <p class="text-base sm:text-lg text-gray-600">
              Manage your personal information and account settings
            </p>
          </div>
          <div class="hidden sm:flex items-center space-x-2">
            <div class="flex items-center px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-medium">
              <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
              Online
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Main Profile Card -->
      <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
        <!-- Enhanced Profile Header with Better Gradient -->
        <div class="relative bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 px-6 sm:px-8 py-10 sm:py-16">
          <!-- Background Pattern -->
          <div class="absolute inset-0 bg-black/10">
            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
          </div>
          
          <div class="relative flex flex-col items-center space-y-6 sm:flex-row sm:space-y-0 sm:space-x-8">
            <!-- Enhanced Profile Image Section -->
            <div class="relative group">
              <ImageUpload
                :current-image="profileImage || user.profile_picture"
                :fallback-text="user.username || user.name || 'User'"
                :alt-text="`${user.username || user.name || 'User'} Profile Picture`"
                size="large"
                :editable="editing"
                :show-delete-button="true"
                @upload-success="handleImageUploadSuccess"
                @upload-error="handleImageUploadError"
                @delete-success="handleImageDeleteSuccess"
                @delete-error="handleImageDeleteError"
              />
            </div>

            <!-- Enhanced User Information -->
            <div class="text-center sm:text-left text-white flex-1">
              <h2 class="text-2xl sm:text-4xl font-bold mb-3 drop-shadow-sm">
                {{ user.username || "Your Name" }}
              </h2>
              <p class="text-blue-100 text-lg sm:text-xl mb-4 drop-shadow-sm">
                {{ user.email || "your.email@example.com" }}
              </p>
              
              <!-- Enhanced Status Badges -->
              <div class="flex flex-wrap gap-3 justify-center sm:justify-start mb-4">
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-md text-white border border-white/30 shadow-lg">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  {{ user.role || "Student" }}
                </span>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-emerald-500/20 backdrop-blur-md text-white border border-emerald-400/30 shadow-lg">
                  <span class="w-2 h-2 bg-emerald-400 rounded-full mr-2 animate-pulse"></span>
                  Active
                </span>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-amber-500/20 backdrop-blur-md text-white border border-amber-400/30 shadow-lg">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  {{ profileCompletion }}% Complete
                </span>
              </div>

              <!-- Quick Stats -->
              <div class="grid grid-cols-2 gap-4 sm:gap-6 max-w-md mx-auto sm:mx-0">
                <div class="text-center sm:text-left">
                  <div class="text-2xl font-bold">{{ lastLoginDate }}</div>
                  <div class="text-blue-200 text-sm">Last Login</div>
                </div>
                <div class="text-center sm:text-left">
                  <div class="text-2xl font-bold">{{ getUserId }}</div>
                  <div class="text-blue-200 text-sm">{{ getUserIdLabel }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced Form Content -->
        <div class="p-6 sm:p-8">
          <!-- Enhanced Tab Navigation -->
          <div class="flex mb-8 border-b border-gray-200 overflow-x-auto">
            <button
              @click="activeTab = 'personal'"
              :class="getTabClasses('personal')"
              class="flex items-center px-6 py-4 font-semibold text-sm sm:text-base transition-all duration-300 whitespace-nowrap border-b-2 min-w-0"
            >
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <span>Personal Information</span>
            </button>
            <button
              @click="activeTab = 'account'"
              :class="getTabClasses('account')"
              class="flex items-center px-6 py-4 font-semibold text-sm sm:text-base transition-all duration-300 whitespace-nowrap border-b-2 min-w-0"
            >
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span>Account Settings</span>
            </button>
            <button
              v-if="user.role === 'student'"
              @click="activeTab = 'grades'"
              :class="getTabClasses('grades')"
              class="flex items-center px-6 py-4 font-semibold text-sm sm:text-base transition-all duration-300 whitespace-nowrap border-b-2 min-w-0"
            >
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              <span>My Grades</span>
            </button>
          </div>

          <form @submit.prevent="saveProfile" class="space-y-8" novalidate>
            <!-- Personal Information Tab -->
            <div v-if="activeTab === 'personal'" class="space-y-8">
              <!-- Basic Information Card -->
              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  Basic Information
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- User ID Field (Non-editable) -->
                  <div class="lg:col-span-2">
                    <label :for="getUserIdLabel.toLowerCase().replace(' ', '')" class="block text-sm font-semibold text-gray-700 mb-3">
                      {{ getUserIdLabel }}
                    </label>
                    <div class="relative">
                      <input
                        :id="getUserIdLabel.toLowerCase().replace(' ', '')"
                        :value="getUserId"
                        type="text"
                        class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 font-semibold focus:outline-none text-base shadow-inner"
                        disabled
                        :placeholder="getUserIdLabel"
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                      </svg>
                    </div>
                  </div>

                  <!-- Full Name -->
                  <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-3">
                      Full Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        id="name"
                        v-model="user.username"
                        type="text"
                        :class="getEnhancedInputClasses('username')"
                        :disabled="!editing"
                        placeholder="Enter your full name"
                        required
                        autocomplete="name"
                        @blur="validateField('username')"
                        @input="clearFieldError('username')"
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                    <transition name="error-fade">
                      <p v-if="errors.username" class="text-red-500 text-sm mt-2 flex items-center animate-pulse">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        {{ errors.username }}
                      </p>
                    </transition>
                  </div>

                  <!-- Email Address -->
                  <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-3">
                      Email Address <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        id="email"
                        v-model="user.email"
                        type="email"
                        :class="getEnhancedInputClasses('email')"
                        :disabled="!editing"
                        placeholder="Enter your email address"
                        required
                        autocomplete="email"
                        @blur="validateField('email')"
                        @input="clearFieldError('email')"
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                      </svg>
                    </div>
                    <transition name="error-fade">
                      <p v-if="errors.email" class="text-red-500 text-sm mt-2 flex items-center animate-pulse">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        {{ errors.email }}
                      </p>
                    </transition>
                  </div>

                  <!-- Phone Number -->
                  <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-3">
                      Phone Number
                    </label>
                    <div class="relative">
                      <input
                        id="phone"
                        v-model="user.phone"
                        type="tel"
                        :class="getEnhancedInputClasses('phone')"
                        :disabled="!editing"
                        placeholder="Enter your phone number"
                        autocomplete="tel"
                        @blur="validateField('phone')"
                        @input="clearFieldError('phone')"
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                      </svg>
                    </div>
                    <transition name="error-fade">
                      <p v-if="errors.phone" class="text-red-500 text-sm mt-2 flex items-center animate-pulse">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        {{ errors.phone }}
                      </p>
                    </transition>
                  </div>

                  <!-- Date of Birth -->
                  <div>
                    <label for="dateOfBirth" class="block text-sm font-semibold text-gray-700 mb-3">
                      Date of Birth
                    </label>
                    <div class="relative">
                      <input
                        id="dateOfBirth"
                        v-model="user.date_of_birth"
                        type="date"
                        :class="getEnhancedInputClasses('date_of_birth')"
                        :disabled="!editing"
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                    </div>
                  </div>

                  <!-- Bio -->
                  <div class="lg:col-span-2">
                    <label for="bio" class="block text-sm font-semibold text-gray-700 mb-3">
                      Bio
                    </label>
                    <div class="relative">
                      <textarea
                        id="bio"
                        v-model="user.bio"
                        :class="getEnhancedTextareaClasses()"
                        :disabled="!editing"
                        rows="4"
                        placeholder="Tell us about yourself..."
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Student-specific fields -->
              <div v-if="user.role === 'student'" class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                  </svg>
                  Student Information
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- Gender -->
                  <div>
                    <label for="gender" class="block text-sm font-semibold text-gray-700 mb-3">
                      Gender
                    </label>
                    <div class="relative">
                      <select
                        id="gender"
                        v-model="user.gender"
                        :class="getEnhancedInputClasses('gender')"
                        :disabled="!editing"
                      >
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                      </select>
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                  </div>

                  <!-- Address -->
                  <div class="lg:col-span-2">
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-3">
                      Address
                    </label>
                    <div class="relative">
                      <textarea
                        id="address"
                        v-model="user.address"
                        :class="getEnhancedTextareaClasses()"
                        :disabled="!editing"
                        rows="3"
                        placeholder="Enter your address..."
                      ></textarea>
                    </div>
                  </div>

                  <!-- Parent Information -->
                  <div>
                    <label for="parentName" class="block text-sm font-semibold text-gray-700 mb-3">
                      Parent/Guardian Name
                    </label>
                    <div class="relative">
                      <input
                        id="parentName"
                        v-model="user.parent_name"
                        type="text"
                        :class="getEnhancedInputClasses('parent_name')"
                        :disabled="!editing"
                        placeholder="Parent/Guardian name"
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                      </svg>
                    </div>
                  </div>
                  <div>
                    <label for="parentPhone" class="block text-sm font-semibold text-gray-700 mb-3">
                      Parent/Guardian Phone
                    </label>
                    <div class="relative">
                      <input
                        id="parentPhone"
                        v-model="user.parent_phone"
                        type="tel"
                        :class="getEnhancedInputClasses('parent_phone')"
                        :disabled="!editing"
                        placeholder="Parent/Guardian phone"
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Account Settings Tab -->
            <div v-if="activeTab === 'account'" class="space-y-8">
              <!-- Account Information Card -->
              <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  Account Information
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 mb-3">
                      Role
                    </label>
                    <div class="relative">
                      <input
                        id="role"
                        :value="user.role"
                        type="text"
                        class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 font-semibold focus:outline-none text-base shadow-inner"
                        disabled
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                  </div>
                  <div>
                    <label for="memberSince" class="block text-sm font-semibold text-gray-700 mb-3">
                      Member Since
                    </label>
                    <div class="relative">
                      <input
                        id="memberSince"
                        :value="formatDate(user.createdAt)"
                        type="text"
                        class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 font-semibold focus:outline-none text-base shadow-inner"
                        disabled
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                    </div>
                  </div>
                  <div class="lg:col-span-2">
                    <label for="lastLogin" class="block text-sm font-semibold text-gray-700 mb-3">
                      Last Login
                    </label>
                    <div class="relative">
                      <input
                        id="lastLogin"
                        :value="formatDate(user.lastLogin)"
                        type="text"
                        class="w-full pl-12 pr-4 py-4 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 font-semibold focus:outline-none text-base shadow-inner"
                        disabled
                      />
                      <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Enhanced Profile Completion Card -->
              <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-6 border border-emerald-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                  Profile Completion
                </h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold text-gray-700">{{ profileCompletion }}% Complete</span>
                    <span class="text-sm font-medium px-3 py-1 rounded-full" :class="getCompletionBadgeClass()">
                      {{ getCompletionStatus() }}
                    </span>
                  </div>
                  
                  <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div
                      class="h-3 rounded-full transition-all duration-1000 ease-out bg-gradient-to-r from-emerald-500 to-teal-500"
                      :style="{ width: `${profileCompletion}%` }"
                    ></div>
                  </div>
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                    <div class="text-center">
                      <div class="text-2xl font-bold text-emerald-600">{{ profileCompletion }}%</div>
                      <div class="text-xs text-gray-600">Completion</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-blue-600">{{ getCompletedFieldsCount() }}</div>
                      <div class="text-xs text-gray-600">Fields Done</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-purple-600">{{ getTotalFieldsCount() }}</div>
                      <div class="text-xs text-gray-600">Total Fields</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-orange-600">{{ getRemainingFieldsCount() }}</div>
                      <div class="text-xs text-gray-600">Remaining</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- My Grades Tab -->
            <div v-if="activeTab === 'grades' && user.role === 'student'" class="space-y-8">
              <!-- Grade Summary Cards -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- GPA Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    GPA
                  </h3>
                  <div class="text-center">
                    <div v-if="grades.loading" class="text-2xl font-bold text-gray-400">Loading...</div>
                    <div v-else-if="grades.gpa" class="text-3xl font-bold text-blue-600">{{ grades.gpa.toFixed(2) }}</div>
                    <div v-else class="text-2xl font-bold text-gray-400">N/A</div>
                    <div class="text-sm text-gray-600 mt-1">Current GPA</div>
                  </div>
                </div>

                <!-- Best Subject Card -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Best Subject
                  </h3>
                  <div class="text-center">
                    <div v-if="grades.loading" class="text-lg font-semibold text-gray-400">Loading...</div>
                    <div v-else-if="grades.summary?.best_subject" class="text-lg font-semibold text-green-600">
                      {{ grades.summary.best_subject }}
                    </div>
                    <div v-else class="text-lg font-semibold text-gray-400">N/A</div>
                    <div class="text-sm text-gray-600 mt-1">Highest Average</div>
                  </div>
                </div>

                <!-- Total Grades Card -->
                <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl p-6 border border-purple-200">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Total Grades
                  </h3>
                  <div class="text-center">
                    <div v-if="grades.loading" class="text-2xl font-bold text-gray-400">Loading...</div>
                    <div v-else class="text-3xl font-bold text-purple-600">{{ grades.data.length }}</div>
                    <div class="text-sm text-gray-600 mt-1">Recorded Grades</div>
                  </div>
                </div>
              </div>

              <!-- Grades Table -->
              <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                  <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Recent Grades
                  </h3>
                </div>
                
                <div v-if="grades.loading" class="p-8 text-center">
                  <div class="inline-flex items-center px-4 py-2 text-gray-600">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading grades...
                  </div>
                </div>
                
                <div v-else-if="grades.error" class="p-8 text-center">
                  <div class="text-red-600">
                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-semibold">{{ grades.error }}</p>
                    <button
                      @click="fetchStudentGrades(user.student_id)"
                      class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                      Retry
                    </button>
                  </div>
                </div>
                
                <div v-else-if="grades.data.length === 0" class="p-8 text-center text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <p class="text-lg font-semibold">No grades found</p>
                  <p class="text-sm">Your grades will appear here once they are recorded by your teachers.</p>
                </div>
                
                <div v-else class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assessment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="grade in grades.data" :key="grade.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900">{{ grade.subject }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900">{{ grade.assessment_type || grade.assessment }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900">{{ grade.score }}/{{ grade.max_score || grade.total }}</div>
                          <div class="text-xs text-gray-500">{{ ((grade.score / (grade.max_score || grade.total)) * 100).toFixed(1) }}%</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="getGradeBadgeClass(grade.letter_grade || grade.grade)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                            {{ grade.letter_grade || grade.grade }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {{ formatDate(grade.recorded_at || grade.date) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Enhanced Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-4 pt-8 border-t border-gray-200">
              <button
                v-if="!editing"
                type="button"
                @click="startEditing"
                class="group w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-blue-500/25 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
              >
                <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Profile
              </button>
              <template v-else>
                <button
                  type="button"
                  @click="cancelEdit"
                  :disabled="loading"
                  class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-500/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="loading || !isFormValid"
                  class="group w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/25 transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                >
                  <svg
                    v-if="loading"
                    class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg
                    v-else
                    class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-200"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  {{ loading ? "Saving..." : "Save Changes" }}
                </button>
              </template>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Enhanced Toast Notifications -->
    <Transition
      enter-active-class="transition duration-500 ease-out transform"
      enter-from-class="translate-x-full opacity-0 scale-95"
      enter-to-class="translate-x-0 opacity-100 scale-100"
      leave-active-class="transition duration-300 ease-in transform"
      leave-from-class="translate-x-0 opacity-100 scale-100"
      leave-to-class="translate-x-full opacity-0 scale-95"
    >
      <div
        v-if="notification.show"
        :class="getEnhancedNotificationClasses()"
        class="fixed top-6 right-6 p-6 rounded-2xl shadow-2xl z-50 max-w-sm border backdrop-blur-sm"
      >
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg
              v-if="notification.type === 'success'"
              class="w-6 h-6 text-emerald-600"
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
              class="w-6 h-6 text-red-600"
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
          <div class="ml-4 flex-1">
            <p class="text-sm font-semibold text-gray-900">
              {{ notification.message }}
            </p>
          </div>
          <button
            @click="notification.show = false"
            class="ml-4 text-gray-400 hover:text-gray-600 transition-colors duration-200"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
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
import { useProfileImage } from "@/composables/useProfileImage";
import ImageUpload from "@/components/ImageUpload.vue";
import userProfileAPI from "@/api/userProfile";
import gradesAPI from "@/api/grades";

const authStore = useAuthStore();
const { profileImage, getImageUrl, updateProfileImage, deleteProfileImage } = useProfileImage();

// Reactive state
const editing = ref(false);
const loading = ref(false);
const originalUser = ref({});
const activeTab = ref("personal");

const user = reactive({
  username: "",
  email: "",
  phone: "",
  bio: "",
  role: "student",
  student_id: "",
  id: "",
  profile_picture: null,
  createdAt: null,
  lastLogin: null,
  date_of_birth: "",
  gender: "",
  address: "",
  parent_name: "",
  parent_phone: "",
});

const errors = reactive({
  username: "",
  email: "",
  phone: "",
});

const notification = reactive({
  show: false,
  message: "",
  type: "success",
});

// Grades state
const grades = reactive({
  loading: false,
  data: [],
  summary: null,
  gpa: null,
  error: null,
});

// Computed properties
const isFormValid = computed(() => {
  return user.username && user.email && !Object.values(errors).some((error) => error);
});

const profileCompletion = computed(() => {
  const fields = [
    user.username,
    user.email,
    user.phone,
    user.bio,
    profileImage.value || user.profile_picture,
    user.date_of_birth,
    user.gender,
    user.address,
  ];
  const completedFields = fields.filter((field) => field && field.toString().trim()).length;
  return Math.round((completedFields / fields.length) * 100);
});

const lastLoginDate = computed(() => {
  const formatted = formatDate(user.lastLogin);
  if (formatted === 'N/A') return 'N/A';
  return formatted.split(',')[0];
});

const getUserId = computed(() => {
  if (user.role === 'teacher') {
    return user.teacher_id || user.id;
  }
  return user.student_code || user.id;
});

const getUserIdLabel = computed(() => {
  if (user.role === 'teacher') {
    return 'Teacher ID';
  }
  return 'Student ID';
});

// Enhanced styling methods
const getTabClasses = (tab) => {
  return activeTab.value === tab
    ? 'text-blue-600 border-blue-600 bg-blue-50/50'
    : 'text-gray-500 hover:text-gray-700 border-transparent hover:border-gray-300';
};

const getEnhancedInputClasses = (field) => {
  const baseClasses = "w-full pl-12 pr-4 py-4 border rounded-xl transition-all duration-300 focus:outline-none focus:ring-4 font-medium text-base shadow-sm";
  const enabledClasses = "border-gray-300 focus:border-blue-500 focus:ring-blue-500/25 bg-white hover:border-gray-400";
  const disabledClasses = "border-gray-200 bg-gray-100 text-gray-600 shadow-inner";
  const errorClasses = "border-red-300 focus:border-red-500 focus:ring-red-500/25 bg-red-50";

  if (errors[field]) {
    return `${baseClasses} ${errorClasses}`;
  }
  return editing.value ? `${baseClasses} ${enabledClasses}` : `${baseClasses} ${disabledClasses}`;
};

const getEnhancedTextareaClasses = () => {
  const baseClasses = "w-full px-4 py-4 border rounded-xl transition-all duration-300 focus:outline-none focus:ring-4 font-medium resize-none text-base shadow-sm";
  const enabledClasses = "border-gray-300 focus:border-blue-500 focus:ring-blue-500/25 bg-white hover:border-gray-400";
  const disabledClasses = "border-gray-200 bg-gray-100 text-gray-600 shadow-inner";
  return editing.value ? `${baseClasses} ${enabledClasses}` : `${baseClasses} ${disabledClasses}`;
};

const getEnhancedNotificationClasses = () => {
  return notification.type === "success"
    ? "bg-white/95 border-emerald-200 text-gray-900"
    : "bg-white/95 border-red-200 text-gray-900";
};

const getCompletionBadgeClass = () => {
  const completion = profileCompletion.value;
  if (completion === 100) return "bg-emerald-100 text-emerald-800";
  if (completion >= 80) return "bg-blue-100 text-blue-800";
  if (completion >= 60) return "bg-yellow-100 text-yellow-800";
  if (completion >= 40) return "bg-orange-100 text-orange-800";
  return "bg-gray-100 text-gray-800";
};

// Helper methods
const getCompletedFieldsCount = () => {
  const fields = [user.username, user.email, user.phone, user.bio, profileImage.value || user.profile_picture, user.date_of_birth, user.gender, user.address];
  return fields.filter((field) => field && field.toString().trim()).length;
};

const getTotalFieldsCount = () => 8;

const getRemainingFieldsCount = () => getTotalFieldsCount() - getCompletedFieldsCount();

const getCompletionStatus = () => {
  const completion = profileCompletion.value;
  if (completion === 100) return "Excellent!";
  if (completion >= 80) return "Almost there!";
  if (completion >= 60) return "Good progress";
  if (completion >= 40) return "Getting started";
  return "Just started";
};

const getGradeBadgeClass = (grade) => {
  if (!grade) return "bg-gray-100 text-gray-800";
  
  const gradeUpper = grade.toString().toUpperCase();
  if (gradeUpper === 'A' || gradeUpper === 'A+') return "bg-green-100 text-green-800";
  if (gradeUpper === 'A-' || gradeUpper === 'B+') return "bg-blue-100 text-blue-800";
  if (gradeUpper === 'B' || gradeUpper === 'B-') return "bg-yellow-100 text-yellow-800";
  if (gradeUpper === 'C+' || gradeUpper === 'C') return "bg-orange-100 text-orange-800";
  if (gradeUpper === 'C-' || gradeUpper === 'D+' || gradeUpper === 'D') return "bg-red-100 text-red-800";
  if (gradeUpper === 'F') return "bg-red-200 text-red-900";
  
  // For percentage grades
  const percentage = parseFloat(grade);
  if (!isNaN(percentage)) {
    if (percentage >= 90) return "bg-green-100 text-green-800";
    if (percentage >= 80) return "bg-blue-100 text-blue-800";
    if (percentage >= 70) return "bg-yellow-100 text-yellow-800";
    if (percentage >= 60) return "bg-orange-100 text-orange-800";
    return "bg-red-100 text-red-800";
  }
  
  return "bg-gray-100 text-gray-800";
};

// Core functionality methods
const loadProfile = async () => {
  try {
    loading.value = true;
    const response = await userProfileAPI.getProfile();
    if (response.data.success && response.data.user) {
      const userData = response.data.user;
      Object.assign(user, {
        username: userData.username || `${userData.first_name} ${userData.last_name}`.trim(),
        email: userData.email,
        phone: userData.phone || "",
        bio: userData.bio || "",
        role: userData.role,
        student_id: userData.student_id || userData.student_code || userData.id,
        id: userData.id,
        profile_picture: userData.profile_picture,
        createdAt: userData.created_at || userData.createdAt,
        lastLogin: userData.last_login || userData.lastLogin,
        first_name: userData.first_name,
        last_name: userData.last_name,
        date_of_birth: formatDateForInput(userData.date_of_birth),
        gender: userData.gender,
        address: userData.address,
        parent_name: userData.parent_name,
        parent_phone: userData.parent_phone,
        enrollment_date: userData.enrollment_date,
        teacher_code: userData.teacher_code,
        department: userData.department,
        qualification: userData.qualification,
        specialization: userData.specialization,
        hire_date: userData.hire_date,
      });
      authStore.updateUser(userData);
      originalUser.value = { ...user };
      
      // Fetch grades if user is a student
      if (userData.role === 'student' && userData.student_id) {
        console.log("👤 User is a student, fetching grades...", {
          role: userData.role,
          student_id: userData.student_id
        });
        await fetchStudentGrades(userData.student_id);
      } else {
        console.log("👤 User is not a student or no student ID, skipping grades fetch", {
          role: userData.role,
          student_id: userData.student_id
        });
      }
    }
  } catch (error) {
    console.error("Load profile error:", error);
    showNotification("Failed to load profile data", "error");
  } finally {
    loading.value = false;
  }
};

const formatDateForInput = (dateString) => {
  if (!dateString) return "";
  if (dateString.includes("T")) {
    return dateString.split("T")[0];
  }
  return dateString;
};

const startEditing = () => {
  editing.value = true;
  originalUser.value = { ...user };
  clearErrors();
};

const cancelEdit = () => {
  Object.assign(user, originalUser.value);
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
    const profileData = {
      username: user.username,
      email: user.email,
      first_name: user.first_name || user.username?.split(" ")[0] || "",
      last_name: user.last_name || user.username?.split(" ").slice(1).join(" ") || "",
      phone: user.phone,
      bio: user.bio,
    };

    if (user.role === "student") {
      if (user.date_of_birth) {
        profileData.date_of_birth = formatDateForInput(user.date_of_birth);
      }
      if (user.gender) profileData.gender = user.gender;
      if (user.address) profileData.address = user.address;
      if (user.parent_name) profileData.parent_name = user.parent_name;
      if (user.parent_phone) profileData.parent_phone = user.parent_phone;
    }

    const response = await userProfileAPI.updateProfileData(profileData);
    if (response.data.success) {
      const updatedUser = response.data.user;
      Object.assign(user, {
        ...updatedUser,
        username: updatedUser.username || `${updatedUser.first_name} ${updatedUser.last_name}`.trim(),
        student_id: updatedUser.student_id || updatedUser.student_code || updatedUser.id,
        date_of_birth: formatDateForInput(updatedUser.date_of_birth),
      });
      authStore.updateUser(updatedUser);
      originalUser.value = { ...user };
      editing.value = false;
      showNotification("Profile updated successfully!", "success");
    } else {
      throw new Error(response.data.message || "Update failed");
    }
  } catch (error) {
    console.error("Save profile error:", error);
    const errorMessage = error.response?.data?.message || error.message || "Failed to save profile";
    showNotification(errorMessage, "error");
  } finally {
    loading.value = false;
  }
};

// Image upload handlers
const handleImageUploadSuccess = async (data) => {
  try {
    showNotification("Profile image uploaded successfully!", "success");
  } catch (error) {
    showNotification("Failed to upload image", "error");
  }
};

const handleImageUploadError = (message) => {
  showNotification(message, "error");
};

const handleImageDeleteSuccess = async (data) => {
  try {
    showNotification("Profile image deleted successfully!", "success");
  } catch (error) {
    showNotification("Failed to delete image", "error");
  }
};

const handleImageDeleteError = (message) => {
  showNotification(message, "error");
};

// Validation methods
const validateForm = () => {
  clearErrors();
  let isValid = true;

  if (!user.username || !user.username.trim()) {
    errors.username = "Name is required";
    isValid = false;
  }

  if (!user.email || !user.email.trim()) {
    errors.email = "Email is required";
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(user.email.trim())) {
    errors.email = "Please enter a valid email address";
    isValid = false;
  }

  if (user.phone && user.phone.trim() && !/^[\+]?[1-9][\d\s\-()]{7,15}$/.test(user.phone.replace(/\s/g, ""))) {
    errors.phone = "Please enter a valid phone number";
    isValid = false;
  }

  return isValid;
};

const validateField = (field) => {
  switch (field) {
    case "username":
    case "name":
      if (!user.username || !user.username.trim()) {
        errors.username = "Name is required";
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
      if (user.phone && user.phone.trim() && !/^[\+]?[1-9][\d\s\-()]{7,15}$/.test(user.phone.replace(/\s/g, ""))) {
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

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  try {
    const date = new Date(dateString);
    // Check if the date is valid
    if (isNaN(date.getTime())) {
      return "N/A";
    }
    return date.toLocaleDateString("en-US", {
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

// Mock data for testing
const mockGradesData = {
  grades: [
    {
      id: 1, subject: 'Math', term_id: 2, assessment_type: 'Midterm',
      max_score: 100, score_obtained: 78, score: 78, total: 100, weightage: 30, 
      grade_letter: 'B', letter_grade: 'B', grade: 'B',
      remarks: 'Good effort', recorded_by: 'Teacher A', recorded_at: '2025-07-10',
      date: '2025-07-10', assessment: 'Midterm'
    },
    {
      id: 2, subject: 'Math', term_id: 2, assessment_type: 'Quiz',
      max_score: 20, score_obtained: 18, score: 18, total: 20, weightage: 10, 
      grade_letter: 'A', letter_grade: 'A', grade: 'A',
      remarks: 'Quick learner', recorded_by: 'Teacher A', recorded_at: '2025-07-15',
      date: '2025-07-15', assessment: 'Quiz'
    },
    {
      id: 3, subject: 'Science', term_id: 2, assessment_type: 'Project',
      max_score: 50, score_obtained: 40, score: 40, total: 50, weightage: 40, 
      grade_letter: 'B', letter_grade: 'B', grade: 'B',
      remarks: '', recorded_by: 'Teacher B', recorded_at: '2025-07-12',
      date: '2025-07-12', assessment: 'Project'
    },
    {
      id: 4, subject: 'English', term_id: 2, assessment_type: 'Final',
      max_score: 100, score_obtained: 85, score: 85, total: 100, weightage: 20, 
      grade_letter: 'A', letter_grade: 'A', grade: 'A',
      remarks: 'Great improvement', recorded_by: 'Teacher C', recorded_at: '2025-07-20',
      date: '2025-07-20', assessment: 'Final'
    },
    {
      id: 5, subject: 'Science', term_id: 2, assessment_type: 'Lab Test',
      max_score: 30, score_obtained: 25, score: 25, total: 30, weightage: 15, 
      grade_letter: 'B', letter_grade: 'B', grade: 'B',
      remarks: 'Good practical skills', recorded_by: 'Teacher B', recorded_at: '2025-07-18',
      date: '2025-07-18', assessment: 'Lab Test'
    }
  ],
  summary: {
    best_subject: 'English',
    weakest_subject: 'Science',
    total_assessments: 5,
    average_score: 82.6
  },
  gpa: 3.4
};

// Grades functions
const fetchStudentGrades = async (studentId) => {
  if (!studentId) {
    console.log("🚫 No student ID provided for grades fetch");
    return;
  }
  
  console.log("📊 Fetching grades for student ID:", studentId);
  
  try {
    grades.loading = true;
    grades.error = null;
    
    // 🧪 TEMPORARY: Use mock data instead of API calls
    console.log("🧪 Using mock data for testing...");
    
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    // Load mock data
    grades.data = mockGradesData.grades;
    grades.summary = mockGradesData.summary;
    grades.gpa = mockGradesData.gpa;
    
    console.log("✅ Mock data loaded successfully:");
    console.log("📊 Mock API Response Structure:");
    console.log({
      "API Endpoint 1": "/students/{studentId}/grades",
      "Expected Response 1": {
        success: true,
        grades: mockGradesData.grades
      },
      "API Endpoint 2": "/students/{studentId}/grades/summary", 
      "Expected Response 2": {
        success: true,
        summary: mockGradesData.summary
      },
      "API Endpoint 3": "/students/{studentId}/gpa",
      "Expected Response 3": {
        success: true,
        gpa: mockGradesData.gpa
      }
    });
    
    console.log("📋 Final grades state:", {
      data: grades.data,
      summary: grades.summary,
      gpa: grades.gpa,
      error: grades.error
    });
    
  } catch (error) {
    console.error("❌ Failed to fetch student grades:", error);
    console.error("Error details:", {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    grades.error = error.response?.data?.message || "Failed to load grades";
    showNotification("Failed to load grades data", "error");
  } finally {
    grades.loading = false;
    console.log("🏁 Grades fetch completed. Loading:", grades.loading);
  }
};

onMounted(async () => {
  await nextTick();
  await loadProfile();
});
</script>

<style scoped>
.error-fade-enter-active,
.error-fade-leave-active {
  transition: all 0.3s ease;
}

.error-fade-enter-from,
.error-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Custom scrollbar for better UX */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Enhanced focus styles */
input:focus,
textarea:focus,
select:focus {
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
