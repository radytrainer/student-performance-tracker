import { ref, reactive } from "vue"

export function useProfile() {
  const loading = ref(false)
  const error = ref(null)

  const user = reactive({
    id: "",
    name: "",
    email: "",
    phone: "",
    bio: "",
    role: "",
    status: "Active",
    profileImage: "",
    createdAt: "",
    lastLogin: "",
  })

  const fetchProfile = async () => {
    loading.value = true
    error.value = null

    try {
      // Simulate API call - replace with actual implementation
      const response = await fetch("/api/profile")
      const data = await response.json()

      Object.assign(user, data)
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch("/api/profile", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(profileData),
      })

      const data = await response.json()
      Object.assign(user, data)
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const uploadImage = async (file) => {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      formData.append("image", file)

      const response = await fetch("/api/profile/upload", {
        method: "POST",
        body: formData,
      })

      const data = await response.json()
      return data.imageUrl
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    loading,
    error,
    fetchProfile,
    updateProfile,
    uploadImage,
  }
}
