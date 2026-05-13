<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">
    <header class="border-b border-gray-800 px-6 py-4 flex items-center gap-4">
      <a href="/" class="text-xl font-bold text-indigo-400 hover:text-indigo-300">
        🎬 New VideoMaker
      </a>
      <nav class="ml-auto flex gap-4 text-sm items-center">
        <a href="/" class="text-gray-400 hover:text-white">Histórico</a>
        <a href="/health" class="text-gray-400 hover:text-white">Serviços</a>
        <a href="/novo" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-1.5 rounded-lg">
          + Novo vídeo
        </a>
      </nav>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-10">
      <slot />
    </main>
  </div>
</template>
