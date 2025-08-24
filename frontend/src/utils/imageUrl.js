export function resolveImageUrl(input) {
  if (!input) return null

  // If an object is provided (e.g., user), prefer explicit URL
  if (typeof input === 'object') {
    if (input.profile_picture_url) return input.profile_picture_url
    input = input.profile_picture || input.image || input.path || null
    if (!input) return null
  }

  const value = String(input)

  // Accept data URLs and absolute URLs as-is
  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('data:')) {
    return value
  }

  // Build absolute URL from relative storage path
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
  const origin = baseUrl.replace(/\/api\/?$/, '')
  const cleaned = value.replace(/^\/+/, '')
  const path = cleaned.startsWith('storage/') ? cleaned : `storage/${cleaned}`
  return `${origin}/${path}`
}
