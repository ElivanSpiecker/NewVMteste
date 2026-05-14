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
        <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="form-label" for="tema">Tema do vídeo</label>
                <input id="tema" type="text" name="tema" value="{{ old('tema') }}" maxlength="200" required placeholder="ex: café artesanal, energia solar, música independente..." class="form-control">
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
                    <select class="form-control opacity-60 cursor-not-allowed" title="Fixo nesta versão">
                        <option>Português PT-BR</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Formato</label>
                    <select class="form-control opacity-60 cursor-not-allowed" title="Fixo nesta versão">
                        <option>Vídeo curto com legenda</option>
                    </select>
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Fontes do conteúdo</h2>
                <p class="mt-1 text-xs text-muted-foreground">Escolha gerar com IA local ou enviar seus próprios arquivos.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label" for="imagens_modo">Imagens</label>
                        <select id="imagens_modo" name="imagens_modo" class="form-control">
                            <option value="gerar"  @selected(old('imagens_modo', 'gerar') === 'gerar')>Gerar com IA (FLUX)</option>
                            <option value="upload" @selected(old('imagens_modo') === 'upload')>Enviar minhas imagens</option>
                        </select>
                        @error('imagens_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="narracao_modo">Narração</label>
                        <select id="narracao_modo" name="narracao_modo" class="form-control">
                            <option value="gerar"  @selected(old('narracao_modo', 'gerar') === 'gerar')>Gerar com IA (TTS)</option>
                            <option value="upload" @selected(old('narracao_modo') === 'upload')>Enviar meu áudio</option>
                            <option value="nenhum" @selected(old('narracao_modo') === 'nenhum')>Sem narração</option>
                        </select>
                        @error('narracao_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="musica_modo">Música</label>
                        <select id="musica_modo" name="musica_modo" class="form-control">
                            <option value="gerar"  @selected(old('musica_modo', 'gerar') === 'gerar')>Gerar com IA (ACE-Step)</option>
                            <option value="upload" @selected(old('musica_modo') === 'upload')>Enviar minha música</option>
                            <option value="nenhum" @selected(old('musica_modo') === 'nenhum')>Sem música</option>
                        </select>
                        @error('musica_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="legendas_modo">Legendas</label>
                        <select id="legendas_modo" name="legendas_modo" class="form-control">
                            <option value="gerar"  @selected(old('legendas_modo', 'gerar') === 'gerar')>Gerar com IA (Whisper)</option>
                            <option value="upload" @selected(old('legendas_modo') === 'upload')>Enviar meu SRT</option>
                            <option value="nenhum" @selected(old('legendas_modo') === 'nenhum')>Sem legendas</option>
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground">Legendas exigem narração para serem geradas.</p>
                        @error('legendas_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div id="imagens_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="imagens">Arquivos de imagem (uma por cena, em ordem)</label>
                    <input id="imagens" type="file" name="imagens[]" accept="image/*" multiple class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">JPG, PNG ou WebP. Até 20 imagens, 10 MB cada.</p>
                    @error('imagens')   <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    @error('imagens.*') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div id="narracao_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="narracao">Arquivo de áudio da narração</label>
                    <input id="narracao" type="file" name="narracao" accept="audio/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">MP3, WAV, M4A ou OGG. Até 50 MB.</p>
                    @error('narracao') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div id="musica_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="musica">Arquivo de música de fundo</label>
                    <input id="musica" type="file" name="musica" accept="audio/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">MP3, WAV, M4A ou OGG. Até 100 MB. Será aplicada em 20% de volume.</p>
                    @error('musica') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div id="legendas_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="legendas">Arquivo de legendas</label>
                    <input id="legendas" type="file" name="legendas" accept=".srt,.vtt,.txt" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">Formato SRT (recomendado), VTT ou TXT. Até 2 MB.</p>
                    @error('legendas') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card bg-accent/60">
                <h2 class="section-title">Antes de começar</h2>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-muted-foreground">
                    <li>• A geração é feita localmente e pode levar entre 6 e 10 minutos.</li>
                    <li>• Certifique-se de que todos os serviços estão ativos na tela <a href="{{ route('health.index') }}" class="underline hover:text-foreground">Serviços</a>.</li>
                    <li>• Após enviar, você acompanha o progresso em tempo real.</li>
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
                    @foreach ([['Roteiro', 'IA criativa local'], ['Imagens', 'Geração por difusão'], ['Narração', 'Síntese de voz'], ['Música', 'Composição generativa'], ['Montagem', 'Edição automatizada']] as $step)
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
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">Pipeline completo de geração, acompanhamento em tempo real e download em um só lugar.</p>
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

    const imagensModo  = document.getElementById('imagens_modo');
    const imagensWrap  = document.getElementById('imagens_upload_wrap');
    const narracaoModo = document.getElementById('narracao_modo');
    const narracaoWrap = document.getElementById('narracao_upload_wrap');
    const musicaModo   = document.getElementById('musica_modo');
    const musicaWrap   = document.getElementById('musica_upload_wrap');
    const legendasModo = document.getElementById('legendas_modo');
    const legendasWrap = document.getElementById('legendas_upload_wrap');

    function syncSources() {
        imagensWrap.classList.toggle('hidden',  imagensModo.value  !== 'upload');
        narracaoWrap.classList.toggle('hidden', narracaoModo.value !== 'upload');
        musicaWrap.classList.toggle('hidden',   musicaModo.value   !== 'upload');
        legendasWrap.classList.toggle('hidden', legendasModo.value !== 'upload');

        // Legenda só pode ser gerada quando há narração — se narração=nenhum, força legendas a sair de "gerar".
        const opcaoGerarLegenda = legendasModo.querySelector('option[value="gerar"]');
        if (narracaoModo.value === 'nenhum') {
            opcaoGerarLegenda.disabled = true;
            if (legendasModo.value === 'gerar') {
                legendasModo.value = 'nenhum';
                legendasWrap.classList.add('hidden');
            }
        } else {
            opcaoGerarLegenda.disabled = false;
        }
    }
    imagensModo.addEventListener('change', syncSources);
    narracaoModo.addEventListener('change', syncSources);
    musicaModo.addEventListener('change', syncSources);
    legendasModo.addEventListener('change', syncSources);
    syncSources();
</script>
@endpush
