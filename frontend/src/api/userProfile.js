import apiClient from './axiosConfig';

export default {
  /**
   * Get current user's profile with complete data
   */
  async getProfile() {
    return apiClient.get('/profile');
  },

  /**
   * Update current user's profile
   */
  async updateProfile(profileData) {
    // Format the data to match backend expectations
    const formData = new FormData();
    
    // Add text fields
    if (profileData.username) formData.append('username', profileData.username);
    if (profileData.email) formData.append('email', profileData.email);
    if (profileData.first_name) formData.append('first_name', profileData.first_name);
    if (profileData.last_name) formData.append('last_name', profileData.last_name);
    if (profileData.phone) formData.append('phone', profileData.phone);
    if (profileData.bio) formData.append('bio', profileData.bio);
    
    // Handle profile picture if provided
    if (profileData.profile_picture && profileData.profile_picture instanceof File) {
      formData.append('profile_picture', profileData.profile_picture);
    }

    return apiClient.post('/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  /**
   * Update profile without file upload (JSON)
   */
  async updateProfileData(profileData) {
    // Clean data - remove file-related fields and read-only fields
    const cleanData = { ...profileData };
    delete cleanData.profile_picture;
    delete cleanData.id;
    delete cleanData.student_id;
    delete cleanData.student_code;
    delete cleanData.teacher_code;
    delete cleanData.role;
    delete cleanData.is_active;
    delete cleanData.created_at;
    delete cleanData.updated_at;
    delete cleanData.createdAt;
    delete cleanData.lastLogin;
    delete cleanData.last_login;
    delete cleanData.enrollment_date;
    delete cleanData.hire_date;

    try {
      const response = await apiClient.put('/profile', cleanData);
      return response;
    } catch (error) {
      console.error('Profile update error details:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
        data: cleanData
      });
      throw error;
    }
  },

  /**
   * Get user by ID (for general user fetching)
   */
  async getUserById(userId) {
    return apiClient.get(`/users/${userId}`);
  },

  /**
   * Update user by ID (admin function)
   */
  async updateUserById(userId, userData) {
    return apiClient.put(`/users/${userId}`, userData);
  }
};
