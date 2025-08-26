<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Submit Feedbacks</h1>
    
    <div class="bg-white rounded-lg shadow-lg p-8">
      <div class="p-8">
        <form @submit.prevent="submitFeedback" class="space-y-6">
          <div>
            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
              Subject
            </label>
            <select 
              id="subject"
              v-model="feedback.subject" 
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out"
              required
            >
              <option value="">Select a subject</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                {{ subject.name }}
              </option>
            </select>
          </div>

          <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
              Feedback Category
            </label>
            <select 
              id="category"
              v-model="feedback.category" 
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out"
              required
            >
              <option value="">Select category</option>
              <option value="teaching_quality">Teaching Quality</option>
              <option value="course_content">Course Content</option>
              <option value="assignments">Assignments</option>
              <option value="exams">Exams</option>
              <option value="facilities">Facilities</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">
              Rating
            </label>
            <div class="flex space-x-2">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                @click="feedback.rating = star"
                class="text-3xl focus:outline-none transition duration-200 ease-in-out transform hover:scale-110"
                :class="star <= feedback.rating ? 'text-yellow-400' : 'text-gray-300'"
              >
                ★
              </button>
            </div>
          </div>

          <div>
            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
              Feedback Message
            </label>
            <textarea 
              id="message"
              v-model="feedback.message"
              rows="6"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out"
              placeholder="Please provide your detailed feedback..."
              required
            ></textarea>
          </div>

          <div class="flex items-center">
            <input 
              id="anonymous"
              v-model="feedback.anonymous"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="anonymous" class="ml-2 block text-sm text-gray-900">
              Submit anonymously
            </label>
          </div>

          <div class="flex justify-end space-x-4">
            <button
              type="button"
              @click="resetForm"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 ease-in-out"
            >
              Reset
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition duration-200 ease-in-out"
            >
              {{ submitting ? 'Submitting...' : 'Submit Feedback' }}
            </button>
          </div>
        </form>

        <!-- Previous Feedback -->
        <div v-if="previousFeedback.length > 0" class="mt-8 border-t pt-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Previous Feedback</h2>
          <div class="space-y-4">
            <div 
              v-for="item in previousFeedback" 
              :key="item.id"
              class="border rounded-lg p-4 bg-gray-50 transition duration-200 ease-in-out hover:shadow-lg"
            >
              <div class="flex justify-between items-start mb-2">
                <div>
                  <span class="font-medium">{{ item.subject_name }}</span>
                  <span class="mx-2">•</span>
                  <span class="text-sm text-gray-600">{{ item.category }}</span>
                </div>
                <div class="flex">
                  <span 
                    v-for="star in 5" 
                    :key="star"
                    class="text-sm"
                    :class="star <= item.rating ? 'text-yellow-400' : 'text-gray-300'"
                  >
                    ★
                  </span>
                </div>
              </div>
              <p class="text-gray-700 text-sm">{{ item.message }}</p>
              <p class="text-xs text-gray-500 mt-2">
                Submitted on {{ formatDate(item.created_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axiosConfig'

const router = useRouter()

const feedback = ref({
  subject: '',
  category: '',
  rating: 0,
  message: '',
  anonymous: false
})

const subjects = ref([])
const previousFeedback = ref([])
const submitting = ref(false)

const submitFeedback = async () => {
  try {
    submitting.value = true
    await api.post('/feedback', feedback.value)
    
    // Show success message
    alert('Feedback submitted successfully!')
    
    // Reset form and reload previous feedback
    resetForm()
    await loadPreviousFeedback()
  } catch (error) {
    console.error('Error submitting feedback:', error)
    alert('Error submitting feedback. Please try again.')
  } finally {
    submitting.value = false
  }
}

const resetForm = () => {
  feedback.value = {
    subject: '',
    category: '',
    rating: 0,
    message: '',
    anonymous: false
  }
}

const loadSubjects = async () => {
  try {
    // Use common subjects endpoint
    const response = await api.get('/subjects')
    const list = response.data?.data || response.data || []
    // Normalize to id/name for the dropdown
    subjects.value = list.map(s => ({ id: s.id, name: s.subject_name || s.name || s.subject_code }))
  } catch (error) {
    console.error('Error loading subjects:', error)
  }
}

const loadPreviousFeedback = async () => {
  try {
    // Use student survey stats and map recent_surveys to previous feedback cards
    const response = await api.get('/student/survey-stats')
    const stats = response.data?.data || {}
    const recent = stats.recent_surveys || []
    previousFeedback.value = recent.map(r => ({
      id: `${r.title}-${r.submitted_at}`,
      subject_name: r.title,
      category: 'survey',
      rating: r.average_score ? Math.round(Number(r.average_score) / 2) : 0, // 10-scale to 5-star
      message: '',
      created_at: r.submitted_at
    }))
  } catch (error) {
    console.error('Error loading previous feedback:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
  loadSubjects()
  loadPreviousFeedback()
})
</script>
