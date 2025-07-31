// Debug utility for active users
import axios from '@/api/axiosConfig'

export async function debugActiveUsers() {
  try {
    console.log('🔍 Testing active users endpoint...')
    
    // Test direct API call
    const response = await axios.get('/active-users?active_only=true&per_page=8')
    console.log('✅ API Response:', response.data)
    
    // Check data structure
    const data = response.data
    if (data.data && Array.isArray(data.data)) {
      console.log('📊 Found paginated data:', data.data.length, 'users')
      console.log('👥 Total users:', data.total)
      console.log('🧪 Sample user:', data.data[0])
    } else if (Array.isArray(data)) {
      console.log('📊 Found array data:', data.length, 'users')
      console.log('🧪 Sample user:', data[0])
    } else {
      console.log('❌ Unexpected data structure:', data)
    }
    
    return response.data
  } catch (error) {
    console.error('❌ Error testing active users:', error)
    console.error('Response status:', error.response?.status)
    console.error('Response data:', error.response?.data)
    throw error
  }
}

// Test from browser console: 
// import { debugActiveUsers } from './src/utils/debugActiveUsers.js'
// debugActiveUsers()
