<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Layout from '../Layout.vue'

const props = defineProps({
  services: Array,
})

const services = ref(props.services)
let interval = null

async function refresh() {
  try {
    const res  = await fetch('/health/api')
    const data = await res.json()
    services.value = data.services
  } catch (_) {}
}

onMounted(() => {
  interval = setInterval(refresh, 4000)
})

onUnmounted(() => clearInterval(interval))

const allUp = () => services.value.every(s => s.up)
</script>

<template>
  <Layout>
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl font-bold">Status dos serviços</h1>
      <span
        class="text-sm font-semibold px-3 py-1 rounded-full"
        :class="allUp() ? 'bg-green-800 text-green-300' : 'bg-red-900 text-red-300'"
      >
        {{ allUp() ? '✓ Tudo rodando' : '✗ Serviço(s) offline' }}
      </span>
    </div>

    <ul class="space-y-3">
      <li
        v-for="s in services"
        :key="s.port"
        class="bg-gray-800 rounded-xl px-5 py-4 flex items-center gap-4"
      >
        <!-- indicador -->
        <span
          class="w-3 h-3 rounded-full shrink-0"
          :class="s.up ? 'bg-green-400' : 'bg-red-500 animate-pulse'"
        />

        <div class="flex-1">
          <p class="font-medium">{{ s.name }}</p>
          <p class="text-xs text-gray-500">{{ s.host }}:{{ s.port }}</p>
        </div>

        <span
          class="text-sm font-semibold"
          :class="s.up ? 'text-green-400' : 'text-red-400'"
        >
          {{ s.up ? 'Online' : 'Offline' }}
        </span>
      </li>
    </ul>

    <p class="text-xs text-gray-600 text-center mt-6">Atualiza automaticamente a cada 4s</p>

    <div v-if="!allUp()" class="mt-8 bg-gray-800 rounded-xl p-5 text-sm text-gray-400 space-y-2">
      <p class="text-white font-semibold mb-3">Como subir os serviços offline:</p>
      <template v-for="s in services" :key="s.port">
        <div v-if="!s.up">
          <p class="text-yellow-400 font-medium">{{ s.name }} (porta {{ s.port }})</p>
          <code class="block bg-gray-900 rounded px-3 py-2 mt-1 text-green-300 text-xs" v-if="s.port === 11434">
            ollama serve
          </code>
          <code class="block bg-gray-900 rounded px-3 py-2 mt-1 text-green-300 text-xs" v-else-if="s.port === 8188">
            cd C:\Users\nicol\PycharmProjects\ComfyUI &amp;&amp; python main.py --lowvram
          </code>
          <code class="block bg-gray-900 rounded px-3 py-2 mt-1 text-green-300 text-xs" v-else-if="s.port === 7860">
            cd C:\Users\nicol\PycharmProjects\ACE-Step-1.5 &amp;&amp; uv run python gradio_app.py --config_path acestep-v15-turbo
          </code>
        </div>
      </template>
    </div>
  </Layout>
</template>
