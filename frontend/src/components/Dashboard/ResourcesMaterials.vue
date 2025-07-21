<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
      <h3 class="text-xl font-bold text-gray-900">Resources & Materials</h3>
    </div>

    <div class="p-6">
      <div class="space-y-3">
        <a
          v-for="resource in resources"
          :key="resource.id"
          href="#"
          class="flex items-center space-x-3 p-4 rounded-2xl hover:bg-gray-50 transition-all duration-200 group"
        >
          <div class="p-2 bg-gray-100 rounded-xl group-hover:bg-blue-100 transition-colors">
            <component :is="getResourceIcon(resource.type)" class="w-5 h-5 text-gray-500 group-hover:text-blue-500" />
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ resource.title }}</p>
            <p class="text-xs text-gray-500">{{ resource.subject }}</p>
          </div>
          <DownloadIcon class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" />
        </a>
      </div>

      <button class="w-full mt-4 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl font-medium hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
        <LibraryIcon class="w-4 h-4 inline mr-2" />
        Access Library
      </button>
    </div>
  </div>
</template>

<script setup>
const VideoIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>' }
const DocumentTextIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' }
const LinkIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>' }
const DownloadIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' }
const LibraryIcon = { template: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11"></path></svg>' }

defineProps(['resources'])

const getResourceIcon = (type) => {
  switch (type) {
    case 'video': return 'VideoIcon'
    case 'document': return 'DocumentTextIcon'
    case 'link': return 'LinkIcon'
    default: return 'DocumentTextIcon'
  }
}
</script>