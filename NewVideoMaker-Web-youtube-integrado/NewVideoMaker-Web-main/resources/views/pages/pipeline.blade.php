@extends('layouts.app')

@section('title', 'Pipeline — NEW VideoMaker')
@section('description', 'Fluxo técnico de geração de vídeos com IA local.')

@section('content')
@php
    $steps = [
        ['icon' => 'message-square', 'title' => 'Descrição textual', 'tech' => 'Usuário', 'desc' => 'O usuário envia uma descrição do vídeo desejado.'],
        ['icon' => 'file-text', 'title' => 'Roteiro e direção criativa', 'tech' => 'Gemma via Ollama', 'desc' => 'LLM local gera roteiro, prompts de imagem e direção artística.'],
        ['icon' => 'image', 'title' => 'Geração de imagens', 'tech' => 'FLUX.1 Schnell', 'desc' => 'Modelo de difusão gera imagens de alta qualidade para cada cena.'],
        ['icon' => 'mic', 'title' => 'Narração em áudio', 'tech' => 'Kokoro TTS', 'desc' => 'Sintetizador de voz gera narração natural a partir do roteiro.'],
        ['icon' => 'music', 'title' => 'Trilha sonora', 'tech' => 'ACE-Step 1.5', 'desc' => 'Modelo generativo cria música original para o vídeo.'],
        ['icon' => 'subtitles', 'title' => 'Legendas sincronizadas', 'tech' => 'Whisper', 'desc' => 'Modelo de transcrição gera legendas automaticamente.'],
        ['icon' => 'film', 'title' => 'Montagem final', 'tech' => 'FFmpeg / MoviePy', 'desc' => 'Composição de vídeo, áudio, música e legendas.'],
        ['icon' => 'server', 'title' => 'Orquestração', 'tech' => 'Laravel', 'desc' => 'A plataforma coordena e sequencia todas as etapas do pipeline automaticamente.'],
    ];
    $features = [
        ['icon' => 'cpu', 'title' => 'Execução local', 'desc' => 'Todos os modelos rodam na sua máquina.'],
        ['icon' => 'shield', 'title' => 'Privacidade total', 'desc' => 'Nenhum dado sai do ambiente local.'],
        ['icon' => 'dollar-sign', 'title' => 'Custo zero', 'desc' => 'Sem APIs pagas, sem cobranças por geração.'],
        ['icon' => 'server', 'title' => 'Processamento sequencial', 'desc' => 'Otimizado para VRAM limitada.'],
    ];
@endphp

<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Pipeline</h1>
        <p class="mt-2 text-sm text-muted-foreground">Fluxo completo de geração automatizada de vídeos.</p>
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            @foreach ($steps as $index => $step)
                <div class="group flex items-start gap-4 rounded-sm border border-border bg-card p-5 transition-all duration-300 hover:translate-x-1 hover:shadow-sm">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-sm bg-primary text-primary-foreground">
                        <i data-lucide="{{ $step['icon'] }}" class="h-4.5 w-4.5"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-display text-sm font-semibold text-foreground">{{ $step['title'] }}</h3>
                            <span class="rounded-sm bg-accent px-2 py-0.5 font-display text-[10px] tracking-wider text-muted-foreground">{{ $step['tech'] }}</span>
                        </div>
                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $step['desc'] }}</p>
                    </div>
                    @if ($index < count($steps) - 1)
                        <i data-lucide="arrow-right" class="mt-3 h-3.5 w-3.5 shrink-0 text-border"></i>
                    @endif
                </div>
            @endforeach
        </div>

        <aside class="space-y-4">
            <h2 class="section-title">Diferenciais</h2>
            @foreach ($features as $feature)
                <div class="rounded-sm border border-border bg-card p-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="{{ $feature['icon'] }}" class="h-3.5 w-3.5 text-foreground"></i>
                        <h3 class="font-display text-xs font-semibold text-foreground">{{ $feature['title'] }}</h3>
                    </div>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-muted-foreground">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </aside>
    </div>
</div>
@endsection
