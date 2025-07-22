<template>
  <div class="relative group">
    <!-- Profile Image Display -->
    <div class="relative overflow-hidden bg-white shadow-2xl border-4 border-white/20 backdrop-blur-sm" :class="sizeClasses">
      <img 
        v-if="currentImage"
        :src="getImageUrl(currentImage)"
        :alt="altText"
        class="w-full h-full object-cover"
        @error="handleImageError"
      />
      <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600">
        <span class="text-white font-bold" :class="textSizeClasses">
          {{ getInitials(fallbackText) }}
        </span>
      </div>
      
      <!-- Upload overlay on hover when editable -->
      <div 
        v-if="editable" 
        class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200"
      >
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
    </div>
    
    <!-- Upload Button -->
    <button
      v-if="editable"
      @click="triggerFileInput"
      :disabled="uploading"
      type="button"
      class="absolute -bottom-1 -right-1 sm:-bottom-2 sm:-right-2 bg-white text-blue-600 p-2 sm:p-3 rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation"
    >
      <svg v-if="uploading" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <svg v-else class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </button>
    
    <!-- Delete Button -->
    <button
      v-if="editable && currentImage && showDeleteButton"
      @click="handleDelete"
      :disabled="uploading"
      type="button"
      class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 bg-red-500 text-white p-2 sm:p-3 rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation"
    >
      <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
    </button>
    
    <!-- Hidden file input -->
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      @change="handleFileSelect"
      class="hidden"
      :disabled="uploading"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useProfileImage } from '@/composables/useProfileImage'

const props = defineProps({
  currentImage: {
    type: String,
    default: null
  },
  fallbackText: {
    type: String,
    default: ''
  },
  altText: {
    type: String,
    default: 'Profile Image'
  },
  size: {
    type: String,
    default: 'large', // 'small', 'medium', 'large'
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  editable: {
    type: Boolean,
    default: true
  },
  showDeleteButton: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['upload-success', 'upload-error', 'delete-success', 'delete-error'])

const fileInput = ref(null)
const { isLoading, getImageUrl: getImageURL, updateProfileImage, deleteProfileImage } = useProfileImage()
const uploading = computed(() => isLoading.value)

const sizeClasses = computed(() => {
  const classes = {
    small: 'w-12 h-12 rounded-full',
    medium: 'w-16 h-16 rounded-full',
    large: 'w-24 h-24 sm:w-32 sm:h-32 rounded-full'
  }
  return classes[props.size]
})

const textSizeClasses = computed(() => {
  const classes = {
    small: 'text-sm',
    medium: 'text-lg',
    large: 'text-2xl sm:text-3xl'
  }
  return classes[props.size]
})

const getInitials = (name) => {
  if (!name) return ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getImageUrl = (imagePath) => {
  return getImageURL(imagePath)
}

const handleImageError = () => {
  console.log('Image failed to load, falling back to initials')
}

const triggerFileInput = () => {
  if (fileInput.value && !uploading.value) {
    fileInput.value.click()
  }
}

const handleFileSelect = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Validate file
  if (!file.type.startsWith('image/')) {
    emit('upload-error', 'Please select a valid image file')
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    emit('upload-error', 'Image size must be less than 5MB')
    return
  }

  const result = await updateProfileImage(file)
  
  if (result.success) {
    emit('upload-success', {
      imagePath: result.data.image_path,
      imageUrl: result.data.image_url,
      user: result.data.user
    })
  } else {
    emit('upload-error', result.error)
  }
  
  // Clear the file input
  if (event.target) {
    event.target.value = ''
  }
}

const handleDelete = async () => {
  if (!props.currentImage) return
  
  const result = await deleteProfileImage()
  
  if (result.success) {
    emit('delete-success', {
      user: result.data.user
    })
  } else {
    emit('delete-error', result.error)
  }
}
</script>
