// src/api/axiosConfig.js
import apiClient from './axiosConfig'

const api = apiClient.create({
  baseURL: 'http://127.0.0.1:8000/api', // Update if needed
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  }
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token') // Sanctum token
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

console.log(apiClient);


export default api
