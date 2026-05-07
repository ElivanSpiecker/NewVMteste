<script setup>
import { useForm } from '@inertiajs/vue3'
import Layout from '../Layout.vue'

const form = useForm({
  tema: '',
  duracao: 30,
})

function submit() {
  form.post('/videos')
}
</script>

<template>
  <Layout>
    <h1 class="text-2xl font-bold mb-8">Novo vídeo</h1>

    <form @submit.prevent="submit" class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Tema do vídeo</label>
        <input
          v-model="form.tema"
          type="text"
          placeholder="ex: café artesanal, cachoeiras do Brasil, inteligência artificial..."
          class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          required
          maxlength="200"
        />
        <p v-if="form.errors.tema" class="text-red-400 text-sm mt-1">{{ form.errors.tema }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">
          Duração: <span class="text-indigo-400 font-bold">{{ form.duracao }}s</span>
        </label>
        <input
          v-model.number="form.duracao"
          type="range"
          min="15"
          max="120"
          step="5"
          class="w-full accent-indigo-500"
        />
        <div class="flex justify-between text-xs text-gray-500 mt-1">
          <span>15s</span><span>120s</span>
        </div>
        <p v-if="form.errors.duracao" class="text-red-400 text-sm mt-1">{{ form.errors.duracao }}</p>
      </div>

      <div class="bg-gray-800 rounded-lg p-4 text-sm text-gray-400 space-y-1">
        <p>⚙️ O pipeline roda localmente e leva <strong class="text-white">~6-10 minutos</strong>.</p>
        <p>🖥️ Certifique-se que ComfyUI (8188), ACE-Step (7860) e Ollama (11434) estão rodando.</p>
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-semibold py-3 rounded-lg transition"
      >
        {{ form.processing ? 'Enviando...' : '🚀 Gerar vídeo' }}
      </button>
    </form>
  </Layout>
</template>
