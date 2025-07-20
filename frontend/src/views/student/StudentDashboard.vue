<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-8">
          <h1 class="text-2xl font-semibold text-gray-800">Overview</h1>
          <div class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded-full">
            <span class="w-2 h-2 bg-white rounded-full"></span>
            <span class="text-sm font-medium">Turkish</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <h2 class="text-xl font-medium text-gray-600">My Schedule</h2>
        </div>
        <button class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
          </svg>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-orange-400 to-orange-500 rounded-2xl p-6 text-white">
              <div class="flex items-center space-x-2 mb-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span class="text-sm font-medium">Vocabulary</span>
              </div>
              <div class="text-3xl font-bold">280</div>
            </div>

            <div class="bg-gradient-to-br from-green-400 to-green-500 rounded-2xl p-6 text-white">
              <div class="flex items-center space-x-2 mb-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="text-sm font-medium">Grammar</span>
              </div>
              <div class="text-3xl font-bold">280</div>
              <div class="text-sm opacity-90">Words</div>
            </div>

            <div class="bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl p-6 text-white">
              <div class="flex items-center space-x-2 mb-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span class="text-sm font-medium">Score</span>
              </div>
              <div class="text-3xl font-bold">1250</div>
              <div class="text-sm opacity-90">Points</div>
            </div>
          </div>

          <!-- Progress Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Level Progress -->
            <div class="bg-white rounded-2xl p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Your Level</h3>
              <p class="text-sm text-gray-500 mb-4">Your Level: A2</p>
              
              <div class="flex items-center space-x-4">
                <div class="relative w-20 h-20">
                  <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 36 36">
                    <path
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                      fill="none"
                      stroke="#e5e7eb"
                      stroke-width="2"
                    />
                    <path
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                      fill="none"
                      stroke="#10b981"
                      stroke-width="2"
                      :stroke-dasharray="`${78}, 100`"
                    />
                  </svg>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl font-bold text-gray-800">78%</span>
                  </div>
                </div>
                
                <div class="flex-1">
                  <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                      <div class="w-3 h-3 bg-orange-400 rounded-full"></div>
                      <span class="text-sm text-gray-600">Reading</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                      <span class="text-sm text-gray-600">Listening</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Hours Spent -->
            <div class="bg-white rounded-2xl p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Hours Spent</h3>
                <div class="flex space-x-2 text-xs">
                  <button class="px-2 py-1 text-gray-500">Day</button>
                  <button class="px-2 py-1 bg-orange-100 text-orange-600 rounded">Weekly</button>
                  <button class="px-2 py-1 text-gray-500">Monthly</button>
                </div>
              </div>
              
              <div class="mb-4">
                <div class="text-2xl font-bold text-gray-800">6 Hours</div>
                <div class="text-sm text-gray-500">32 Minutes</div>
              </div>

              <div class="flex items-end space-x-2 h-24">
                <div v-for="(day, index) in weeklyData" :key="index" class="flex-1 flex flex-col items-center">
                  <div 
                    class="w-full bg-orange-400 rounded-t"
                    :style="`height: ${day.hours * 20}px`"
                  ></div>
                  <span class="text-xs text-gray-500 mt-2">{{ day.day }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Language Leaderboard -->
          <div class="bg-white rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-800">Language Leaderboard</h3>
              <div class="flex items-center space-x-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm">
                <span class="w-2 h-2 bg-white rounded-full"></span>
                <span>Turkish</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-12 gap-4 text-xs text-gray-500 font-medium uppercase tracking-wide">
                <div class="col-span-1">Ranking</div>
                <div class="col-span-5">Member</div>
                <div class="col-span-3">Rank</div>
                <div class="col-span-3">Score</div>
              </div>

              <div v-for="user in leaderboard" :key="user.id" class="grid grid-cols-12 gap-4 items-center py-3">
                <div class="col-span-1">
                  <span class="text-lg font-semibold text-gray-800">{{ user.ranking }}</span>
                  <span v-if="user.change" :class="user.change > 0 ? 'text-green-500' : 'text-red-500'" class="text-xs ml-1">
                    {{ user.change > 0 ? '↑' : '↓' }}
                  </span>
                </div>
                <div class="col-span-5 flex items-center space-x-3">
                  <img :src="user.avatar" :alt="user.name" class="w-8 h-8 rounded-full">
                  <span class="font-medium text-gray-800">{{ user.name }}</span>
                </div>
                <div class="col-span-3">
                  <span class="text-sm text-gray-600">{{ user.rank }}</span>
                </div>
                <div class="col-span-3">
                  <span class="font-semibold text-gray-800">{{ user.score }} points</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Schedule -->
          <div class="bg-white rounded-2xl p-6">
            <div class="space-y-4">
              <div v-for="task in schedule" :key="task.id" class="flex items-center space-x-3">
                <div :class="task.color" class="w-12 h-12 rounded-xl flex items-center justify-center">
                  <component :is="task.icon" class="w-6 h-6 text-white" />
                </div>
                <div class="flex-1">
                  <h4 class="font-medium text-gray-800">{{ task.title }}</h4>
                  <p class="text-sm text-gray-500">{{ task.date }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Friends -->
          <div class="bg-white rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-800">Friends</h3>
              <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
              </button>
            </div>

            <div class="space-y-3">
              <div v-for="friend in friends" :key="friend.id" class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <img :src="friend.avatar" :alt="friend.name" class="w-8 h-8 rounded-full">
                  <span class="font-medium text-gray-800">{{ friend.name }}</span>
                </div>
                <button class="text-orange-400 hover:text-orange-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                  </svg>
                </button>
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

// Weekly data for the chart
const weeklyData = ref([
  { day: 'Su', hours: 2 },
  { day: 'Mo', hours: 1.5 },
  { day: 'Tu', hours: 3 },
  { day: 'We', hours: 4 },
  { day: 'Th', hours: 2.5 },
  { day: 'Fr', hours: 3.5 },
  { day: 'Sa', hours: 2 }
])

// Leaderboard data
const leaderboard = ref([
  {
    id: 1,
    ranking: 1,
    name: 'Jerome Bell',
    avatar: '/placeholder.svg?height=32&width=32',
    rank: 'Turkish Star Master',
    score: '5520',
    change: null
  },
  {
    id: 2,
    ranking: 2,
    name: 'Courtney Henry',
    avatar: '/placeholder.svg?height=32&width=32',
    rank: 'Turkish Certified',
    score: '5140',
    change: 1
  },
  {
    id: 3,
    ranking: 3,
    name: 'Arlene McCoy',
    avatar: '/placeholder.svg?height=32&width=32',
    rank: 'Turkish Specialist',
    score: '4780',
    change: -1
  },
  {
    id: 4,
    ranking: 4,
    name: 'Darrell Steward',
    avatar: '/placeholder.svg?height=32&width=32',
    rank: 'Turkish Specialist',
    score: '4520',
    change: null
  }
])

// Schedule data
const schedule = ref([
  {
    id: 1,
    title: 'Turkish A2 Exam',
    date: 'August 9, 2020',
    color: 'bg-orange-400',
    icon: 'BookIcon'
  },
  {
    id: 2,
    title: 'Listening 30 Minute',
    date: 'August 11, 2020',
    color: 'bg-orange-400',
    icon: 'HeadphonesIcon'
  },
  {
    id: 3,
    title: 'Reading Turkish Blog',
    date: 'August 13, 2020',
    color: 'bg-orange-400',
    icon: 'BookOpenIcon'
  }
])

// Friends data
const friends = ref([
  {
    id: 1,
    name: 'Jacob Jonas',
    avatar: '/placeholder.svg?height=32&width=32'
  },
  {
    id: 2,
    name: 'Samuel Ethan',
    avatar: '/placeholder.svg?height=32&width=32'
  },
  {
    id: 3,
    name: 'Eleanor Pena',
    avatar: '/placeholder.svg?height=32&width=32'
  },
  {
    id: 4,
    name: 'Annette Black',
    avatar: '/placeholder.svg?height=32&width=32'
  }
])

// Simple icon components
const BookIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 24 24">
      <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/>
    </svg>
  `
}

const HeadphonesIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 24 24">
      <path d="M12 1c-4.97 0-9 4.03-9 9v7c0 1.66 1.34 3 3 3h3v-8H5v-2c0-3.87 3.13-7 7-7s7 3.13 7 7v2h-4v8h3c1.66 0 3-1.34 3-3v-7c0-4.97-4.03-9-9-9z"/>
    </svg>
  `
}

const BookOpenIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 24 24">
      <path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5S2.45 4.9 1 6v14.65c0 .25.25.5.5.5.1 0 .15-.05.25-.05C3.1 20.45 5.05 20 6.5 20c1.95 0 4.05.4 5.5 1.5 1.35-.85 3.8-1.5 5.5-1.5 1.65 0 3.35.3 4.75 1.05.1.05.15.05.25.05.25 0 .5-.25.5-.5V6c-.6-.45-1.25-.75-2-1zm0 13.5c-1.1-.35-2.3-.5-3.5-.5-1.7 0-4.15.65-5.5 1.5V8c1.35-.85 3.8-1.5 5.5-1.5 1.2 0 2.4.15 3.5.5v11.5z"/>
    </svg>
  `
}
</script>

<style scoped>
/* Additional custom styles if needed */
</style>