@extends('layouts.app')

@section('title', 'Criar Vídeo — NEW VideoMaker')
@section('description', 'Crie vídeos automaticamente com IA local.')

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Criar Vídeo</h1>
        <p class="mt-2 text-sm text-muted-foreground">Descreva o tema e deixe o pipeline local gerar o vídeo.</p>
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_380px]">
        <form action="{{ route('videos.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="form-label" for="tema">Tema do vídeo</label>
                <input id="tema" type="text" name="tema" value="{{ old('tema') }}" maxlength="200" required placeholder="ex: café artesanal, futebol amador, inteligência artificial..." class="form-control">
                @error('tema')
                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="form-label mb-0" for="duracao">Duração</label>
                    <span id="durationValue" class="font-display text-xs font-semibold text-foreground">{{ old('duracao', 30) }}s</span>
                </div>
                <input id="duracao" type="range" name="duracao" min="15" max="120" step="5" value="{{ old('duracao', 30) }}" class="w-full accent-black">
                <div class="mt-1 flex justify-between text-[11px] text-muted-foreground">
                    <span>15s</span><span>120s</span>
                </div>
                @error('duracao')
                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Idioma</label>
                    <select class="form-control" disabled>
                        <option>Português PT-BR</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Formato</label>
                    <select class="form-control" disabled>
                        <option>Vídeo curto com legenda</option>
                    </select>
                </div>
            </div>

            <div class="card bg-accent/60">
                <h2 class="section-title">Observações</h2>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-muted-foreground">
                    <li>• O pipeline roda localmente via fila Laravel.</li>
                    <li>• Verifique Ollama, ComfyUI e ACE-Step antes de gerar.</li>
                    <li>• Após enviar, você será direcionado para o status do processamento.</li>
                </ul>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i data-lucide="rocket" class="h-4 w-4"></i>
                Gerar vídeo
            </button>
        </form>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title">Pipeline</h2>
                <div class="mt-5 space-y-4">
                    @foreach ([['Roteiro', 'Gemma via Ollama'], ['Imagens', 'FLUX via ComfyUI'], ['Narração', 'Kokoro TTS'], ['Música', 'ACE-Step'], ['Montagem', 'MoviePy + FFmpeg']] as $step)
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-foreground"></span>
                            <div>
                                <p class="text-sm font-medium text-foreground">{{ $step[0] }}</p>
                                <p class="text-xs text-muted-foreground">{{ $step[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card overflow-hidden p-0">
                <img src="{{ asset('assets/frame-1.jpg') }}" alt="Preview visual" class="h-56 w-full object-cover grayscale">
                <div class="p-5">
                    <h2 class="font-display text-sm font-semibold text-foreground">NEW VideoMaker</h2>
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">Interface integrada ao backend Laravel existente, mantendo geração, status e downloads.</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const duracao = document.getElementById('duracao');
    const durationValue = document.getElementById('durationValue');
    if (duracao && durationValue) {
        duracao.addEventListener('input', () => durationValue.textContent = `${duracao.value}s`);
    }
</script>
@endpush
