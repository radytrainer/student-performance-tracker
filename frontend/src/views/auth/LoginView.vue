    <template>
  <div class="min-h-screen bg-gray-900 relative">
    <!-- Glassmorphism Background Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 via-blue-900/20 to-green-900/20 backdrop-blur-sm"></div>
    
    <!-- Main Container -->
    <div class="relative w-screen h-screen rounded-3xl overflow-hidden shadow-2xl backdrop-blur-lg bg-white/5">
      <!-- Background Video -->
      <video
        autoplay
        muted
        loop
        playsinline
        class="absolute inset-0 w-full h-full object-cover"
        style="z-index: 1;"
      >
        <source src="/src/videos/loginBackground.mp4" type="video/mp4">
        <!-- Fallback background image if video fails to load -->
        <div 
          class="absolute inset-0 bg-cover bg-center"
          style="background-image: url('https://placehold.co/1200x600/4a5568/4a5568?text=Person+Working+Background')"
        ></div>
      </video>

      <!-- Content Container -->
      <div class="relative h-full" style="z-index: 10;">
        <div class="flex h-full">
          <!-- Left Section -->
          <div class="flex-1 relative">
            <!-- Subtle overlay for left section -->
            <div class="absolute inset-0 bg-black bg-opacity-20 backdrop-blur-sm" style="z-index: 2;"></div>
            
            <div class="relative h-full flex items-center justify-center p-12" style="z-index: 3;">
              <div class="max-w-md">
                <h1 class="text-5xl font-bold text-white mb-6 leading-tight drop-shadow-lg">
                  Let's Get Started
                </h1>
                <p class="text-white text-lg leading-relaxed opacity-90 drop-shadow-md">
                  Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
                  Maecenas placerat ultricies libero eu pharetra. Vestibulum a 
                  ultricies augue.
                </p>
              </div>
            </div>
          </div>

          <!-- Right Section -->
          <div class="flex-1 relative">
            <!-- Semi-transparent overlay for right section -->
            <div class="absolute inset-0 bg-black bg-opacity-70" style="z-index: 2;"></div>
            
            <!-- Form Content -->
            <div class="relative h-full flex items-center justify-center p-8" style="z-index: 10;">
              <div class="w-full max-w-sm">
                <!-- Dynamic Form Title -->
                <h2 class="text-3xl font-bold text-white mb-8 text-center">
                  {{ isSignIn ? 'Login' : 'Register' }}
                </h2>
                
                <!-- Login Form -->
                <form v-if="isSignIn" class="space-y-6" @submit.prevent="handleSignIn">
                  <!-- Your Email -->
                  <div class="relative">
                    <input
                      v-model="signInData.email"
                      type="email"
                      placeholder="Your Email"
                      class="w-full bg-transparent text-white placeholder-gray-400 border-0 border-b border-gray-500 pb-2 focus:border-green-500 focus:outline-none transition-colors duration-200"
                      required
                    />
                  </div>

                  <!-- Password -->
                  <div class="relative">
                    <input
                      v-model="signInData.password"
                      type="password"
                      placeholder="Password"
                      class="w-full bg-transparent text-white placeholder-gray-400 border-0 border-b border-gray-500 pb-2 focus:border-green-500 focus:outline-none transition-colors duration-200"
                      required
                    />
                  </div>

                  <!-- Login Button -->
                  <button
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 mt-8 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                  >
                    Login
                  </button>
                </form>

                <!-- Register Form -->
                <form v-else class="space-y-6" @submit.prevent="handleRegister">
                  <!-- Your Name -->
                  <div class="relative">
                    <input
                      v-model="registerData.name"
                      type="text"
                      placeholder="Your Name"
                      class="w-full bg-transparent text-white placeholder-gray-400 border-0 border-b border-gray-500 pb-2 focus:border-green-500 focus:outline-none transition-colors duration-200"
                      required
                    />
                  </div>

                  <!-- Your Email -->
                  <div class="relative">
                    <input
                      v-model="registerData.email"
                      type="email"
                      placeholder="Your Email"
                      class="w-full bg-transparent text-white placeholder-gray-400 border-0 border-b border-gray-500 pb-2 focus:border-green-500 focus:outline-none transition-colors duration-200"
                      required
                    />
                  </div>

                  <!-- Create Password -->
                  <div class="relative">
                    <input
                      v-model="registerData.password"
                      type="password"
                      placeholder="Create Password"
                      class="w-full bg-transparent text-white placeholder-gray-400 border-0 border-b border-gray-500 pb-2 focus:border-green-500 focus:outline-none transition-colors duration-200"
                      required
                    />
                  </div>

                  <!-- Repeat Password -->
                  <div class="relative">
                    <input
                      v-model="registerData.confirmPassword"
                      type="password"
                      placeholder="Repeat Password"
                      class="w-full bg-transparent text-white placeholder-gray-400 border-0 border-b border-gray-500 pb-2 focus:border-green-500 focus:outline-none transition-colors duration-200"
                      required
                    />
                  </div>

                  <!-- Register Button -->
                  <button
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 mt-8 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                  >
                    Register
                  </button>
                </form>

                <!-- Social Media Icons -->
                <div class="flex items-center justify-center mt-8">
                  <!-- OR with lines -->
                  <div class="flex items-center w-full">
                    <div class="flex-1 h-px bg-gray-500"></div>
                    <span class="text-white font-medium px-4">OR</span>
                    <div class="flex-1 h-px bg-gray-500"></div>
                  </div>
                </div>

                <!-- Social Icons Row -->
                <div class="flex justify-center space-x-4 mt-6">
                  <!-- Facebook -->
                  <button 
                    @click="handleSocialLogin('facebook')"
                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-lg"
                    aria-label="Continue with Facebook"
                  >
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                  </button>

                  <!-- Twitter -->
                  <button 
                    @click="handleSocialLogin('twitter')"
                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 shadow-lg"
                    aria-label="Continue with Twitter"
                  >
                    <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                  </button>

                  <!-- Google -->
                  <button 
                    @click="handleSocialLogin('google')"
                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 shadow-lg"
                    aria-label="Continue with Google"
                  >
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                      <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                      <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                      <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                      <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                  </button>
                </div>

                <!-- Form Toggle Link -->
                <div class="text-center mt-6">
                  <p class="text-gray-300">
                    {{ isSignIn ? "Don't have account? " : "Already have an account? " }}
                    <a 
                      href="#" 
                      @click.prevent="isSignIn = !isSignIn" 
                      class="text-green-500 hover:text-green-400 font-medium transition-colors duration-200 focus:outline-none focus:underline"
                    >
                      {{ isSignIn ? "Let's register here" : "Login here" }}
                    </a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Presented by badge -->
      <div class="absolute bottom-6 left-6" style="z-index: 20;">
        <div class="bg-white/90 backdrop-blur-sm rounded-full px-4 py-2 flex items-center space-x-2 shadow-lg">
          <span class="text-gray-700 text-sm font-medium">presented by</span>
          <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
            <span class="text-white text-xs font-bold">A</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

// Form toggle state
const isSignIn = ref(true)

// Sign In form data
const signInData = reactive({
  email: '',
  password: ''
})

// Register form data
const registerData = reactive({
  name: '',
  email: '',
  password: '',
  confirmPassword: ''
})

// Sign In handler
const handleSignIn = () => {
  console.log('Login submitted:', signInData)
  // Add your login logic here
}

// Register handler
const handleRegister = () => {
  if (registerData.password !== registerData.confirmPassword) {
    alert('Passwords do not match!')
    return
  }
  
  console.log('Register submitted:', registerData)
  // Add your registration logic here
}

// Social login handler
const handleSocialLogin = (provider) => {
  console.log(`Social login with ${provider}`)
  // Add your social login logic here
}
</script>

<style scoped>
/* Custom styles for input focus states */
input:focus {
  outline: none;
}

/* Video background styling */
video {
  object-fit: cover;
  object-position: center;
}

/* Enhanced text shadows for better readability */
.drop-shadow-lg {
  text-shadow: 0 10px 15px rgba(0, 0, 0, 0.3);
}

.drop-shadow-md {
  text-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

/* Glassmorphism effects */
.backdrop-blur-lg {
  backdrop-filter: blur(16px);
}

.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .min-h-screen > div:last-child {
    height: 100vh;
    min-height: 100vh;
  }
}

@media (max-width: 768px) {
  .min-h-screen > div:last-child {
    height: 100vh;
    min-height: 100vh;
    border-radius: 0;
  }
  
  .flex {
    flex-direction: column;
  }
  
  .flex-1 {
    flex: none;
    min-height: 50vh;
  }
  
  .text-5xl {
    font-size: 2.5rem;
  }
  
  .p-12 {
    padding: 2rem;
  }
  
  .p-8 {
    padding: 1.5rem;
  }
  
  /* Adjust social icons for mobile */
  .space-x-4 > * + * {
    margin-left: 1rem;
  }
}

@media (max-width: 640px) {
  .rounded-3xl {
    border-radius: 0;
  }
  
  .space-x-4 > * + * {
    margin-left: 0.75rem;
  }
  
  .w-12.h-12 {
    width: 2.5rem;
    height: 2.5rem;
  }
}

/* Performance optimization for video */
video {
  will-change: transform;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}

/* Ensure proper layering */
.relative {
  position: relative;
}

/* Enhanced shadow for the main container */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Form transition animations */
form {
  transition: opacity 0.3s ease-in-out;
}
</style>