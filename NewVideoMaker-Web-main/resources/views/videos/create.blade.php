@extends('layouts.app')

@section('title', __('Criar Vídeo') . ' — NEW VideoMaker')
@section('description', __('Crie vídeos automaticamente com IA local.'))

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">{{ __('Criar Vídeo') }}</h1>
        <p class="mt-2 text-sm text-muted-foreground">{{ __('Descreva o tema e deixe o pipeline local gerar o vídeo.') }}</p>
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_380px]">
        <form action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="form-label" for="tema">{{ __('Tema do vídeo') }}</label>
                <textarea id="tema" name="tema" maxlength="2000" rows="4" required placeholder="{{ __('ex: café artesanal, energia solar, música independente...') }}" class="form-control resize-y">{{ old('tema') }}</textarea>
                <div class="mt-1 flex justify-between text-[11px] text-muted-foreground">
                    <span>{{ __('Descreva o tema com o nível de detalhe que quiser.') }}</span>
                    <span id="temaCount">0 / 2000</span>
                </div>
                @error('tema')
                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="form-label mb-0" for="duracao">{{ __('Duração') }}</label>
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
                    <label class="form-label" for="idioma">{{ __('Idioma') }}</label>
                    <select id="idioma" name="idioma" class="form-control">
                        <option value="PT-BR" @selected(old('idioma', 'PT-BR') === 'PT-BR')>Português (PT-BR)</option>
                        <option value="EN-US" @selected(old('idioma') === 'EN-US')>English (EN-US)</option>
                    </select>
                    @error('idioma')
                        <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Formato') }}</label>
                    <select class="form-control opacity-60 cursor-not-allowed" title="{{ __('Formato') }}">
                        <option>{{ __('Vídeo curto com legenda') }}</option>
                    </select>
                </div>
            </div>

            {{-- Seletor de voz com preview --}}
            <div id="vozSection" class="card">
                <h2 class="section-title">{{ __('Voz da narração') }}</h2>
                <p class="mt-1 text-xs text-muted-foreground">{{ __('Escolha a voz e ouça uma amostra antes de gerar.') }}</p>

                <input type="hidden" id="voz" name="voz" value="{{ old('voz', '') }}">
                @error('voz')
                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror

                <div id="vozGrid" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    {{-- Preenchido via JS --}}
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">{{ __('Fontes do conteúdo') }}</h2>
                <p class="mt-1 text-xs text-muted-foreground">{{ __('Escolha gerar com IA local ou enviar seus próprios arquivos.') }}</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label" for="imagens_modo">{{ __('Imagens') }}</label>
                        <select id="imagens_modo" name="imagens_modo" class="form-control">
                            <option value="gerar"  @selected(old('imagens_modo', 'gerar') === 'gerar')>{{ __('Gerar com IA (FLUX)') }}</option>
                            <option value="upload" @selected(old('imagens_modo') === 'upload')>{{ __('Enviar minhas imagens') }}</option>
                        </select>
                        @error('imagens_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="narracao_modo">{{ __('Narração') }}</label>
                        <select id="narracao_modo" name="narracao_modo" class="form-control">
                            <option value="gerar"  @selected(old('narracao_modo', 'gerar') === 'gerar')>{{ __('Gerar com IA (TTS)') }}</option>
                            <option value="upload" @selected(old('narracao_modo') === 'upload')>{{ __('Enviar meu áudio') }}</option>
                            <option value="nenhum" @selected(old('narracao_modo') === 'nenhum')>{{ __('Sem narração') }}</option>
                        </select>
                        @error('narracao_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="musica_modo">{{ __('Música') }}</label>
                        <select id="musica_modo" name="musica_modo" class="form-control">
                            <option value="gerar"  @selected(old('musica_modo', 'gerar') === 'gerar')>{{ __('Gerar com IA (ACE-Step)') }}</option>
                            <option value="upload" @selected(old('musica_modo') === 'upload')>{{ __('Enviar minha música') }}</option>
                            <option value="nenhum" @selected(old('musica_modo') === 'nenhum')>{{ __('Sem música') }}</option>
                        </select>
                        @error('musica_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="legendas_modo">{{ __('Legendas') }}</label>
                        <select id="legendas_modo" name="legendas_modo" class="form-control">
                            <option value="gerar"  @selected(old('legendas_modo', 'gerar') === 'gerar')>{{ __('Gerar com IA (Whisper)') }}</option>
                            <option value="upload" @selected(old('legendas_modo') === 'upload')>{{ __('Enviar meu SRT') }}</option>
                            <option value="nenhum" @selected(old('legendas_modo') === 'nenhum')>{{ __('Sem legendas') }}</option>
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('Legendas exigem narração para serem geradas.') }}</p>
                        @error('legendas_modo') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div id="imagens_upload_wrap" class="mt-4 hidden">
                    <label class="form-label">{{ __('Imagens das cenas') }}</label>
                    <p class="mb-2 text-xs text-muted-foreground">{{ __('JPG, PNG ou WebP. Até 20 imagens, 10 MB cada. Arraste para reordenar.') }}</p>

                    <input id="imagens" type="file" name="imagens[]" accept="image/jpeg,image/png,image/webp,image/bmp" multiple class="hidden">

                    {{-- Drop zone (visível quando não há imagens) --}}
                    <div id="imagensDropZone" class="flex cursor-pointer flex-col items-center justify-center rounded-sm border-2 border-dashed border-border p-8 transition-colors hover:border-foreground/40">
                        <i data-lucide="image-plus" class="h-8 w-8 text-muted-foreground"></i>
                        <p class="mt-2 text-sm text-muted-foreground">{{ __('Clique ou arraste imagens aqui') }}</p>
                    </div>

                    {{-- Grid de previews reordenável --}}
                    <div id="imagensGrid" class="flex flex-wrap gap-2"></div>

                    {{-- Botão para adicionar mais (visível quando já há imagens) --}}
                    <button type="button" id="imagensAddMore" class="mt-3 hidden w-full rounded-sm border-2 border-dashed border-border py-3 text-xs text-muted-foreground transition-colors hover:border-foreground/40 hover:text-foreground">
                        + {{ __('Adicionar mais imagens') }}
                    </button>

                    @error('imagens')   <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    @error('imagens.*') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div id="narracao_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="narracao">{{ __('Arquivo de áudio da narração') }}</label>
                    <input id="narracao" type="file" name="narracao" accept="audio/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('MP3, WAV, M4A ou OGG. Até 50 MB.') }}</p>
                    @error('narracao') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div id="musica_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="musica">{{ __('Arquivo de música de fundo') }}</label>
                    <input id="musica" type="file" name="musica" accept="audio/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('MP3, WAV, M4A ou OGG. Até 100 MB. Será aplicada em 20% de volume.') }}</p>
                    @error('musica') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div id="legendas_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="legendas">{{ __('Arquivo de legendas') }}</label>
                    <input id="legendas" type="file" name="legendas" accept=".srt,.vtt,.txt" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('Formato SRT (recomendado), VTT ou TXT. Até 2 MB.') }}</p>
                    @error('legendas') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card bg-accent/60">
                <h2 class="section-title">{{ __('Antes de começar') }}</h2>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-muted-foreground">
                    <li>• {{ __('A geração é feita localmente e pode levar entre 6 e 10 minutos.') }}</li>
                    <li>• {{ __('Certifique-se de que todos os serviços estão ativos na tela') }} <a href="{{ route('health.index') }}" class="underline hover:text-foreground">{{ __('Serviços') }}</a>.</li>
                    <li>• {{ __('Após enviar, você acompanha o progresso em tempo real.') }}</li>
                </ul>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i data-lucide="rocket" class="h-4 w-4"></i>
                {{ __('Gerar vídeo') }}
            </button>
        </form>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title">Pipeline</h2>
                <div class="mt-5 space-y-4">
                    @foreach ([
                        [__('Roteiro'), __('IA criativa local')],
                        [__('Imagens'), __('Geração por difusão')],
                        [__('Narração'), __('Síntese de voz')],
                        [__('Música'), __('Composição generativa')],
                        [__('Montagem'), __('Edição automatizada')],
                    ] as $step)
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
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">{{ __('Pipeline completo de geração, acompanhamento em tempo real e download em um só lugar.') }}</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Traduções para JS ───────────────────────────────────
    const T = {
        listen:   @json(__('Ouvir')),
        loading:  @json(__('Carregando...')),
        playing:  @json(__('Tocando...')),
        error:    @json(__('Erro')),
        male:     @json(__('Masculino')),
        female:   @json(__('Feminino')),
    };

    // ── Contador de caracteres do tema ─────────────────────
    const temaEl = document.getElementById('tema');
    const temaCount = document.getElementById('temaCount');
    function updateTemaCount() {
        temaCount.textContent = `${temaEl.value.length} / 2000`;
    }
    temaEl.addEventListener('input', updateTemaCount);
    updateTemaCount();

    // ── Duração slider ─────────────────────────────────────
    const duracao = document.getElementById('duracao');
    const durationValue = document.getElementById('durationValue');
    if (duracao && durationValue) {
        duracao.addEventListener('input', () => durationValue.textContent = `${duracao.value}s`);
    }

    // ── Fontes de conteúdo (upload toggles + legendas/narração) ──
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

        const vozSection = document.getElementById('vozSection');
        if (narracaoModo.value === 'gerar') {
            vozSection.classList.remove('hidden');
        } else {
            vozSection.classList.add('hidden');
        }
    }
    imagensModo.addEventListener('change', syncSources);
    narracaoModo.addEventListener('change', syncSources);
    musicaModo.addEventListener('change', syncSources);
    legendasModo.addEventListener('change', syncSources);

    // ── Vozes por idioma ────────────────────────────────────
    const VOICES = {
        'PT-BR': [
            { id: 'pf_dora',  name: 'Dora',  gender: T.female },
            { id: 'pm_alex',  name: 'Alex',  gender: T.male   },
            { id: 'pm_santa', name: 'Santa', gender: T.male   },
        ],
        'EN-US': [
            { id: 'af_heart',   name: 'Heart',   gender: T.female },
            { id: 'af_bella',   name: 'Bella',   gender: T.female },
            { id: 'am_michael', name: 'Michael', gender: T.male   },
        ],
    };

    const idiomaSelect = document.getElementById('idioma');
    const vozInput     = document.getElementById('voz');
    const vozGrid      = document.getElementById('vozGrid');
    let currentAudio   = null;

    function renderVoices() {
        const idioma = idiomaSelect.value;
        const voices = VOICES[idioma] || [];
        const oldVoz = vozInput.value;
        const validIds = voices.map(v => v.id);
        if (!validIds.includes(oldVoz)) {
            vozInput.value = voices[0]?.id || '';
        }

        vozGrid.innerHTML = '';
        voices.forEach((voice) => {
            const selected = vozInput.value === voice.id;
            const card = document.createElement('div');
            card.className = `voice-card relative flex flex-col items-center gap-2 rounded-sm border p-4 cursor-pointer transition-all ${selected ? 'border-primary bg-accent/80 ring-1 ring-primary' : 'border-border bg-card hover:border-foreground/30'}`;
            card.dataset.voiceId = voice.id;

            card.innerHTML = `
                <div class="flex h-10 w-10 items-center justify-center rounded-full ${selected ? 'bg-primary text-primary-foreground' : 'bg-accent text-muted-foreground'}">
                    <i data-lucide="${voice.gender === T.female ? 'user-round' : 'user'}" class="h-5 w-5"></i>
                </div>
                <p class="font-display text-sm font-semibold text-foreground">${voice.name}</p>
                <p class="text-[11px] text-muted-foreground">${voice.gender}</p>
                <button type="button" class="voice-play-btn mt-1 flex items-center gap-1.5 rounded-sm border border-border px-3 py-1.5 text-[10px] font-medium text-foreground transition-colors hover:bg-accent" data-voice-id="${voice.id}">
                    <i data-lucide="play" class="h-3 w-3"></i>
                    <span>${T.listen}</span>
                </button>
            `;

            card.addEventListener('click', (e) => {
                if (e.target.closest('.voice-play-btn')) return;
                vozInput.value = voice.id;
                renderVoices();
            });
            vozGrid.appendChild(card);
        });

        if (window.lucide) lucide.createIcons();

        vozGrid.querySelectorAll('.voice-play-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                playVoicePreview(btn.dataset.voiceId, btn);
            });
        });
    }

    async function playVoicePreview(voiceId, btn) {
        if (currentAudio) {
            currentAudio.pause();
            currentAudio = null;
            document.querySelectorAll('.voice-play-btn span').forEach(s => s.textContent = T.listen);
        }

        const spanEl = btn.querySelector('span');
        const iconEl = btn.querySelector('[data-lucide]');
        spanEl.textContent = T.loading;

        try {
            const audio = new Audio(`/api/voices/${voiceId}/preview`);
            currentAudio = audio;

            audio.addEventListener('canplaythrough', () => {
                spanEl.textContent = T.playing;
                if (iconEl) { iconEl.setAttribute('data-lucide', 'pause'); lucide.createIcons(); }
                audio.play();
            }, { once: true });

            audio.addEventListener('ended', () => {
                spanEl.textContent = T.listen;
                if (iconEl) { iconEl.setAttribute('data-lucide', 'play'); lucide.createIcons(); }
                currentAudio = null;
            });

            audio.addEventListener('error', () => {
                spanEl.textContent = T.error;
                setTimeout(() => { spanEl.textContent = T.listen; }, 2000);
                currentAudio = null;
            });

            audio.load();
        } catch (err) {
            spanEl.textContent = T.error;
            setTimeout(() => { spanEl.textContent = T.listen; }, 2000);
        }
    }

    idiomaSelect.addEventListener('change', renderVoices);
    renderVoices();
    syncSources();

    // ── Upload de imagens com drag-and-drop para reordenar ──
    const imagensInput   = document.getElementById('imagens');
    const imagensGridEl  = document.getElementById('imagensGrid');
    const imgDropZone    = document.getElementById('imagensDropZone');
    const imgAddMoreBtn  = document.getElementById('imagensAddMore');

    let imageEntries = []; // [{file, previewUrl}]
    let dragFromIndex = null;

    function imgAddFiles(fileList) {
        const newFiles = Array.from(fileList).filter(f => f.type.startsWith('image/'));
        if (!newFiles.length) return;
        if (imageEntries.length + newFiles.length > 20) {
            alert('Máximo de 20 imagens.');
            return;
        }
        newFiles.forEach(f => imageEntries.push({ file: f, previewUrl: URL.createObjectURL(f) }));
        imgSyncAndRender();
    }

    function imgSyncAndRender() {
        const dt = new DataTransfer();
        imageEntries.forEach(e => dt.items.add(e.file));
        imagensInput.files = dt.files;
        imgRenderGrid();
    }

    function imgRenderGrid() {
        imagensGridEl.innerHTML = '';
        const has = imageEntries.length > 0;
        imgDropZone.classList.toggle('hidden', has);
        imgAddMoreBtn.classList.toggle('hidden', !has);

        imageEntries.forEach((entry, idx) => {
            const el = document.createElement('div');
            el.className = 'img-sortable relative group rounded-sm border border-border overflow-hidden bg-accent transition-all';
            el.style.width = '72px';
            el.style.height = '128px';
            el.draggable = true;
            el.dataset.idx = idx;

            el.innerHTML =
                '<div class="h-full w-full cursor-grab active:cursor-grabbing">' +
                    '<img src="' + entry.previewUrl + '" class="h-full w-full object-cover pointer-events-none" draggable="false">' +
                '</div>' +
                '<div style="position:absolute;top:2px;left:2px;width:18px;height:18px;display:flex;align-items:center;justify-content:center;border-radius:9999px;background:rgba(0,0,0,0.75);color:#fff;font-size:10px;font-weight:700;pointer-events:none;z-index:5;">' + (idx + 1) + '</div>' +
                '<button type="button" draggable="false" class="img-rm" title="Remover" style="position:absolute;top:2px;right:2px;width:20px;height:20px;display:flex;align-items:center;justify-content:center;border-radius:9999px;background:#dc2626;color:#fff;border:2px solid #fff;cursor:pointer;padding:0;z-index:10;box-shadow:0 1px 3px rgba(0,0,0,0.5);">' +
                    '<svg style="width:10px;height:10px;pointer-events:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                '</button>';

            // Drag start
            el.addEventListener('dragstart', (e) => {
                dragFromIndex = idx;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
                requestAnimationFrame(() => el.classList.add('opacity-30', 'scale-95'));
            });
            el.addEventListener('dragend', () => {
                el.classList.remove('opacity-30', 'scale-95');
                dragFromIndex = null;
                document.querySelectorAll('.img-sortable').forEach(c => c.classList.remove('ring-2', 'ring-primary'));
            });

            // Drag over / drop
            el.addEventListener('dragover', (e) => {
                if (dragFromIndex === null) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                el.classList.add('ring-2', 'ring-primary');
            });
            el.addEventListener('dragleave', () => el.classList.remove('ring-2', 'ring-primary'));
            el.addEventListener('drop', (e) => {
                e.preventDefault();
                el.classList.remove('ring-2', 'ring-primary');
                if (dragFromIndex === null || dragFromIndex === idx) return;
                const [moved] = imageEntries.splice(dragFromIndex, 1);
                imageEntries.splice(idx, 0, moved);
                dragFromIndex = null;
                imgSyncAndRender();
            });

            // Remove button — stop drag from hijacking the click
            const rmBtn = el.querySelector('.img-rm');
            rmBtn.addEventListener('mousedown', (e) => e.stopPropagation());
            rmBtn.addEventListener('dragstart', (e) => { e.preventDefault(); e.stopPropagation(); });
            rmBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                URL.revokeObjectURL(entry.previewUrl);
                imageEntries.splice(idx, 1);
                imgSyncAndRender();
            });

            imagensGridEl.appendChild(el);
        });
    }

    // Drop zone: click
    imgDropZone.addEventListener('click', () => imagensInput.click());

    // Drop zone: file drag from desktop
    imgDropZone.addEventListener('dragover', (e) => { e.preventDefault(); imgDropZone.classList.add('border-primary', 'bg-accent/60'); });
    imgDropZone.addEventListener('dragleave', () => imgDropZone.classList.remove('border-primary', 'bg-accent/60'));
    imgDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        imgDropZone.classList.remove('border-primary', 'bg-accent/60');
        imgAddFiles(e.dataTransfer.files);
    });

    // Add more button
    imgAddMoreBtn.addEventListener('click', () => {
        const tmp = document.createElement('input');
        tmp.type = 'file'; tmp.accept = 'image/*'; tmp.multiple = true;
        tmp.addEventListener('change', () => imgAddFiles(tmp.files));
        tmp.click();
    });

    // Native input change (from drop zone click)
    imagensInput.addEventListener('change', () => {
        if (imagensInput.files.length && !imageEntries.length) {
            imgAddFiles(imagensInput.files);
        }
    });
</script>
@endpush
