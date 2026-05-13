<script setup>
import Layout from '../Layout.vue'

defineProps({
  videos: Array,
})

const statusColor = (v) => {
  if (v.done)   return 'text-green-400'
  if (v.failed) return 'text-red-400'
  return 'text-indigo-400'
}
</script>

<template>
  <Layout>
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl font-bold">Histórico</h1>
      <a href="/novo" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium">
        + Novo vídeo
      </a>
    </div>

    <div v-if="videos.length === 0" class="text-center text-gray-500 py-20">
      <p class="text-4xl mb-4">🎬</p>
      <p>Nenhum vídeo gerado ainda.</p>
      <a href="/novo" class="mt-4 inline-block text-indigo-400 hover:underline">Gerar primeiro vídeo →</a>
    </div>

    <ul v-else class="space-y-3">
      <li
        v-for="v in videos"
        :key="v.id"
        class="bg-gray-800 rounded-xl px-5 py-4 flex items-center gap-4"
      >
        <div class="flex-1 min-w-0">
          <p class="font-medium truncate">{{ v.tema }}</p>
          <p class="text-xs text-gray-500 mt-0.5">{{ v.duracao }}s · {{ v.criado_em }}</p>
        </div>

        <span :class="['text-sm font-medium', statusColor(v)]">
          {{ v.statusLabel }}
        </span>

        <!-- barra de progresso compacta para jobs em andamento -->
        <div v-if="v.isProcessing" class="w-24 bg-gray-700 rounded-full h-1.5">
          <div class="bg-indigo-500 h-1.5 rounded-full" :style="{ width: v.progresso + '%' }" />
        </div>

        <a
          v-if="v.done"
          :href="`/videos/${v.id}`"
          class="text-indigo-400 hover:text-indigo-300 text-sm"
        >Download →</a>

        <a
          v-else-if="!v.failed"
          :href="`/videos/${v.id}/status`"
          class="text-gray-400 hover:text-white text-sm"
        >Ver →</a>
      </li>
    </ul>
  </Layout>
</template>
