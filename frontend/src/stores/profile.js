import { defineStore } from "pinia"

export const useProfileStore = defineStore("profile", {
  state: () => ({
    user: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProfile() {
      this.loading = true
      this.error = null

      try {
        // Replace with your actual API endpoint
        const response = await fetch("/api/profile", {
          method: "GET",
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
            "Content-Type": "application/json",
          },
        })

        if (!response.ok) {
          throw new Error("Failed to fetch profile")
        }

        const data = await response.json()
        this.user = data
        return data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateProfile(profileData) {
      this.loading = true
      this.error = null

      try {
        const response = await fetch("/api/profile", {
          method: "PUT",
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify(profileData),
        })

        if (!response.ok) {
          throw new Error("Failed to update profile")
        }

        const data = await response.json()
        this.user = data
        return data
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    async uploadProfileImage(file) {
      this.loading = true
      this.error = null

      try {
        const formData = new FormData()
        formData.append("image", file)

        const response = await fetch("/api/profile/upload-image", {
          method: "POST",
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
          },
          body: formData,
        })

        if (!response.ok) {
          throw new Error("Failed to upload image")
        }

        const data = await response.json()
        return data.imageUrl
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },
  },
})
