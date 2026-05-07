@extends('layouts.app')

@section('title', 'Configurações — NEW VideoMaker')
@section('description', 'Configurações da plataforma NEW VideoMaker.')

@section('content')
@php
    $models = [
        ['icon' => 'image', 'label' => 'Modelo de imagem', 'value' => 'FLUX.1 Schnell'],
        ['icon' => 'mic', 'label' => 'Modelo de voz', 'value' => 'Kokoro TTS'],
        ['icon' => 'music', 'label' => 'Modelo de música', 'value' => 'ACE-Step 1.5'],
        ['icon' => 'subtitles', 'label' => 'Modelo de legendas', 'value' => 'Whisper'],
        ['icon' => 'brain', 'label' => 'Modelo de linguagem', 'value' => 'Gemma via Ollama'],
    ];
    $hardware = [
        ['icon' => 'cpu', 'label' => 'GPU detectada', 'value' => 'NVIDIA RTX 4060 8GB'],
        ['icon' => 'hard-drive', 'label' => 'VRAM disponível', 'value' => '7.4 GB / 8 GB'],
        ['icon' => 'activity', 'label' => 'Modo de execução', 'value' => 'Sequencial'],
    ];
@endphp

<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Configurações</h1>
        <p class="mt-2 text-sm text-muted-foreground">Gerencie modelos, preferências e hardware.</p>
    </div>

    <div class="mt-10 max-w-3xl space-y-8">
        <section class="card">
            <h2 class="section-title">Modelos locais</h2>
            <div class="mt-4 space-y-4">
                @foreach ($models as $model)
                    <div class="flex items-center justify-between border-b border-border pb-3 last:border-0">
                        <div class="flex items-center gap-2">
                            <i data-lucide="{{ $model['icon'] }}" class="h-3.5 w-3.5 text-muted-foreground"></i>
                            <span class="text-sm text-foreground">{{ $model['label'] }}</span>
                        </div>
                        <span class="rounded-sm bg-accent px-3 py-1 font-display text-xs text-muted-foreground">{{ $model['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2 class="section-title">Preferências</h2>
            <div class="mt-4 space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Idioma padrão</span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option>Português PT-BR</option>
                        <option>Inglês EN-US</option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Duração padrão</span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option>15 segundos</option>
                        <option selected>30 segundos</option>
                        <option>45 segundos</option>
                        <option>60 segundos</option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Qualidade das imagens</span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option>Alta (1024px)</option>
                        <option>Média (768px)</option>
                        <option>Baixa (512px)</option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Pasta de saída</span>
                    <input type="text" value="/output/videos" class="w-48 rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-foreground">Usar legendas automaticamente</span>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" checked class="peer sr-only">
                        <div class="h-5 w-9 rounded-full bg-border transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:bg-card after:transition-transform peer-checked:bg-primary peer-checked:after:translate-x-4"></div>
                    </label>
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="section-title">Hardware</h2>
            <div class="mt-4 space-y-4">
                @foreach ($hardware as $item)
                    <div class="flex items-center justify-between border-b border-border pb-3 last:border-0">
                        <div class="flex items-center gap-2">
                            <i data-lucide="{{ $item['icon'] }}" class="h-3.5 w-3.5 text-muted-foreground"></i>
                            <span class="text-sm text-foreground">{{ $item['label'] }}</span>
                        </div>
                        <span class="rounded-sm bg-accent px-3 py-1 font-display text-xs text-muted-foreground">{{ $item['value'] }}</span>
                    </div>
                @endforeach
                <div class="mt-2 flex items-center gap-2">
                    <div class="h-2 w-2 animate-pulse rounded-full bg-chart-2"></div>
                    <span class="text-xs text-muted-foreground">Todos os modelos disponíveis</span>
                </div>
            </div>
        </section>

        <button class="btn-primary">Salvar configurações</button>
    </div>
</div>
@endsection
