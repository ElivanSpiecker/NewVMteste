<script setup>
import Layout from '../Layout.vue'

const props = defineProps({
  video: Object,
})
</script>

<template>
  <Layout>
    <div class="text-center space-y-6">
      <div class="text-6xl">🎉</div>
      <h1 class="text-2xl font-bold">Vídeo pronto!</h1>
      <p class="text-gray-400">Tema: <span class="text-white">{{ video.tema }}</span> · {{ video.duracao }}s</p>

      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a
          :href="`/videos/${video.id}/download`"
          class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition"
        >
          ⬇️ Baixar MP4
        </a>
        <a
          :href="`/videos/${video.id}/legenda`"
          class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition"
        >
          📄 Baixar legenda (SRT)
        </a>
      </div>

      <a href="/" class="block text-sm text-gray-500 hover:text-white mt-4">← Voltar ao histórico</a>
    </div>
  </Layout>
</template>
