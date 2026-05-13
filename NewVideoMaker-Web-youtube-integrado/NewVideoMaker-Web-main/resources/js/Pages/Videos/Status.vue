<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const props = defineProps({
  video: Object,
})

const state = ref({ ...props.video })
let pollInterval = null

const steps = [
  { key: 'generating_script',    label: '🧠 Roteiro (Gemma 4)',    pct: 17 },
  { key: 'generating_images',    label: '🖼️ Imagens (FLUX)',        pct: 33 },
  { key: 'generating_narration', label: '🎤 Narração (Kokoro)',      pct: 50 },
  { key: 'generating_music',     label: '🎵 Música (ACE-Step)',      pct: 67 },
  { key: 'generating_subtitles', label: '📝 Legendas (Whisper)',     pct: 83 },
  { key: 'assembling',           label: '🎬 Montagem (MoviePy)',     pct: 95 },
]

function stepState(step) {
  const idx = steps.findIndex(s => s.key === state.value.status)
  const sIdx = steps.findIndex(s => s.key === step.key)
  if (state.value.done) return 'done'
  if (sIdx < idx) return 'done'
  if (sIdx === idx) return 'active'
  return 'pending'
}

async function poll() {
  try {
    const res = await fetch(`/videos/${props.video.id}/poll`)
    const data = await res.json()
    state.value = { ...state.value, ...data }
    if (data.done || data.failed) {
      clearInterval(pollInterval)
      if (data.done) router.visit(`/videos/${props.video.id}`)
    }
  } catch (_) {}
}

onMounted(() => {
  if (!state.value.done && !state.value.failed) {
    pollInterval = setInterval(poll, 3000)
  }
})

onUnmounted(() => clearInterval(pollInterval))
</script>

<template>
  <Layout>
    <h1 class="text-2xl font-bold mb-2">Gerando vídeo</h1>
    <p class="text-gray-400 mb-8">Tema: <span class="text-white">{{ state.tema }}</span> · {{ state.duracao }}s</p>

    <!-- barra de progresso -->
    <div class="bg-gray-800 rounded-full h-3 mb-8 overflow-hidden">
      <div
        class="h-full bg-indigo-500 transition-all duration-700"
        :style="{ width: state.progresso + '%' }"
      />
    </div>

    <!-- etapas -->
    <ol class="space-y-3 mb-8">
      <li
        v-for="step in steps"
        :key="step.key"
        class="flex items-center gap-3"
      >
        <span
          class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
          :class="{
            'bg-green-600 text-white': stepState(step) === 'done',
            'bg-indigo-500 text-white animate-pulse': stepState(step) === 'active',
            'bg-gray-700 text-gray-500': stepState(step) === 'pending',
          }"
        >
          <template v-if="stepState(step) === 'done'">✓</template>
          <template v-else-if="stepState(step) === 'active'">●</template>
          <template v-else>○</template>
        </span>
        <span :class="stepState(step) === 'pending' ? 'text-gray-500' : 'text-white'">
          {{ step.label }}
        </span>
      </li>
    </ol>

    <p class="text-center text-gray-400 text-sm">{{ state.statusLabel }}</p>

    <div v-if="state.failed" class="mt-6 bg-red-900/40 border border-red-700 rounded-lg p-4">
      <p class="text-red-400 font-semibold mb-1">Falha no pipeline</p>
      <pre class="text-xs text-red-300 whitespace-pre-wrap overflow-auto max-h-48">{{ state.erro }}</pre>
      <a href="/novo" class="mt-3 inline-block text-sm text-indigo-400 hover:underline">Tentar novamente →</a>
    </div>
  </Layout>
</template>
