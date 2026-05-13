@extends('layouts.app')

@section('title', 'Gerando Vídeo — NEW VideoMaker')
@section('description', 'Acompanhe o status de geração do vídeo.')

@section('content')
@php
    $steps = [
        ['key' => 'generating_script', 'label' => 'Roteiro', 'description' => 'Gemma via Ollama', 'pct' => 17],
        ['key' => 'generating_images', 'label' => 'Imagens', 'description' => 'FLUX via ComfyUI', 'pct' => 33],
        ['key' => 'generating_narration', 'label' => 'Narração', 'description' => 'Kokoro TTS', 'pct' => 50],
        ['key' => 'generating_music', 'label' => 'Música', 'description' => 'ACE-Step', 'pct' => 67],
        ['key' => 'generating_subtitles', 'label' => 'Legendas', 'description' => 'Whisper', 'pct' => 83],
        ['key' => 'assembling', 'label' => 'Montagem', 'description' => 'MoviePy + FFmpeg', 'pct' => 95],
    ];
@endphp

<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Gerando vídeo</h1>
        <p class="mt-2 text-sm text-muted-foreground">Tema: <span class="font-medium text-foreground">{{ $video->tema }}</span> · {{ $video->duracao }}s</p>
    </div>

    <div class="mt-10 grid gap-8 xl:grid-cols-[1fr_380px]">
        <section class="card">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-semibold text-foreground">Progresso</h2>
                <span id="statusLabel" class="rounded-sm bg-accent px-3 py-1 font-display text-[10px] tracking-wider text-muted-foreground uppercase">{{ $video->statusLabel() }}</span>
            </div>

            <div class="mt-8 h-3 overflow-hidden rounded-full bg-accent">
                <div id="progressBar" class="h-full rounded-full bg-primary transition-all duration-700" style="width: {{ $video->progresso }}%"></div>
            </div>
            <p id="progressText" class="mt-2 text-right font-display text-xs text-muted-foreground">{{ $video->progresso }}%</p>

            <ol class="mt-8 space-y-4">
                @foreach ($steps as $step)
                    <li class="pipeline-step flex items-center gap-4 rounded-sm border border-border bg-background p-4" data-step="{{ $step['key'] }}">
                        <span class="step-marker flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent font-display text-xs text-muted-foreground">○</span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-foreground">{{ $step['label'] }}</p>
                            <p class="text-xs text-muted-foreground">{{ $step['description'] }}</p>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ $step['pct'] }}%</span>
                    </li>
                @endforeach
            </ol>

            <div id="errorBox" class="mt-6 hidden rounded-sm border border-destructive/40 bg-destructive/10 p-4">
                <p class="font-display text-sm font-semibold text-destructive">Falha na geração</p>
                <pre id="errorText" class="mt-2 max-h-48 overflow-auto whitespace-pre-wrap text-xs text-destructive"></pre>
                <a href="{{ route('videos.create') }}" class="mt-4 inline-flex text-xs font-semibold text-foreground hover:underline">Tentar novamente →</a>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title">Status atual</h2>
                <p id="sideStatusLabel" class="mt-4 font-display text-2xl font-semibold text-foreground">{{ $video->statusLabel() }}</p>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">O status é atualizado automaticamente. Você será redirecionado assim que o vídeo estiver pronto.</p>
            </div>
            <a href="{{ route('videos.index') }}" class="btn-outline w-full">Voltar aos vídeos</a>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const videoId = @json($video->id);
    const stepOrder = @json(array_column($steps, 'key'));
    let interval = null;

    function setStepState(currentStatus, done) {
        const currentIndex = stepOrder.indexOf(currentStatus);
        document.querySelectorAll('.pipeline-step').forEach((item) => {
            const marker = item.querySelector('.step-marker');
            const index = stepOrder.indexOf(item.dataset.step);
            item.classList.remove('border-primary', 'bg-accent');
            marker.className = 'step-marker flex h-7 w-7 shrink-0 items-center justify-center rounded-full font-display text-xs';

            if (done || (currentIndex >= 0 && index < currentIndex)) {
                marker.textContent = '✓';
                marker.classList.add('bg-primary', 'text-primary-foreground');
            } else if (index === currentIndex) {
                marker.textContent = '●';
                marker.classList.add('animate-pulse', 'bg-primary', 'text-primary-foreground');
                item.classList.add('border-primary', 'bg-accent');
            } else {
                marker.textContent = '○';
                marker.classList.add('bg-accent', 'text-muted-foreground');
            }
        });
    }

    async function poll() {
        try {
            const response = await fetch(`/videos/${videoId}/poll`);
            const data = await response.json();
            document.getElementById('progressBar').style.width = `${data.progresso}%`;
            document.getElementById('progressText').textContent = `${data.progresso}%`;
            document.getElementById('statusLabel').textContent = data.statusLabel;
            document.getElementById('sideStatusLabel').textContent = data.statusLabel;
            setStepState(data.status, data.done);

            if (data.failed) {
                clearInterval(interval);
                document.getElementById('errorBox').classList.remove('hidden');
                document.getElementById('errorText').textContent = data.erro || 'Erro não informado.';
            }

            if (data.done) {
                clearInterval(interval);
                window.location.href = `/videos/${videoId}`;
            }
        } catch (error) {
            console.error(error);
        }
    }

    setStepState(@json($video->status), @json($video->isDone()));
    @if (!$video->isDone() && !$video->hasFailed())
        interval = setInterval(poll, 3000);
    @endif
</script>
@endpush
