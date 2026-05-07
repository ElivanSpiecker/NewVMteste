@extends('layouts.app')

@section('title', 'Dashboard — NEW VideoMaker')
@section('description', 'Painel principal do NEW VideoMaker.')

@section('content')
@php
    $total = $stats['total'] ?? 0;
    $done = $stats['done'] ?? 0;
    $processing = $stats['processing'] ?? 0;
    $failed = $stats['failed'] ?? 0;
@endphp

<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Dashboard</h1>
        <p class="mt-2 text-sm text-muted-foreground">Resumo do pipeline e dos vídeos gerados localmente.</p>
    </div>

    <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title">Total</h2>
                <i data-lucide="video" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground">{{ $total }}</p>
            <p class="mt-1 text-xs text-muted-foreground">vídeos cadastrados</p>
        </article>
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title">Concluídos</h2>
                <i data-lucide="check-circle" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground">{{ $done }}</p>
            <p class="mt-1 text-xs text-muted-foreground">prontos para download</p>
        </article>
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title">Processando</h2>
                <i data-lucide="loader" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground">{{ $processing }}</p>
            <p class="mt-1 text-xs text-muted-foreground">em execução ou fila</p>
        </article>
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title">Falhas</h2>
                <i data-lucide="alert-triangle" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground">{{ $failed }}</p>
            <p class="mt-1 text-xs text-muted-foreground">jobs com erro</p>
        </article>
    </div>

    <div class="mt-10 grid gap-8 xl:grid-cols-[1fr_380px]">
        <section class="card">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-xl font-semibold text-foreground">Últimos vídeos</h2>
                    <p class="mt-1 text-xs text-muted-foreground">Acompanhamento dos jobs mais recentes.</p>
                </div>
                <a href="{{ route('videos.index') }}" class="btn-outline">Ver todos</a>
            </div>

            <div class="mt-6 space-y-3">
                @forelse ($recentVideos as $video)
                    <div class="flex items-center gap-4 rounded-sm border border-border bg-background px-4 py-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-sm bg-accent">
                            <i data-lucide="film" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-foreground">{{ $video->tema }}</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $video->duracao }}s · {{ $video->created_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="rounded-sm bg-accent px-2.5 py-1 font-display text-[10px] tracking-wider text-muted-foreground uppercase">{{ $video->statusLabel() }}</span>
                        <a href="{{ $video->isDone() ? route('videos.show', $video) : route('videos.status', $video) }}" class="text-xs font-medium text-foreground hover:underline">Abrir</a>
                    </div>
                @empty
                    <div class="rounded-sm border border-dashed border-border p-10 text-center">
                        <p class="text-sm text-muted-foreground">Nenhum vídeo gerado ainda.</p>
                        <a href="{{ route('videos.create') }}" class="mt-4 inline-flex text-xs font-semibold text-foreground hover:underline">Criar primeiro vídeo →</a>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-6">
            <div class="card">
                <h2 class="section-title">Pipeline local</h2>
                <div class="mt-4 space-y-3 text-sm text-muted-foreground">
                    <div class="flex items-center justify-between"><span>Roteiro</span><span>Gemma/Ollama</span></div>
                    <div class="flex items-center justify-between"><span>Imagens</span><span>ComfyUI/FLUX</span></div>
                    <div class="flex items-center justify-between"><span>Narração</span><span>Kokoro</span></div>
                    <div class="flex items-center justify-between"><span>Música</span><span>ACE-Step</span></div>
                    <div class="flex items-center justify-between"><span>Montagem</span><span>MoviePy</span></div>
                </div>
            </div>
            <div class="card">
                <h2 class="section-title">Atalho</h2>
                <p class="mt-3 text-sm leading-relaxed text-muted-foreground">Crie um novo vídeo informando tema e duração. O backend mantém a fila e a tela de status atualiza o progresso automaticamente.</p>
                <a href="{{ route('videos.create') }}" class="btn-primary mt-5 w-full">Novo vídeo</a>
            </div>
        </section>
    </div>
</div>
@endsection
