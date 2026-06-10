<?php $__env->startSection('title', __('Criar Vídeo') . ' — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Crie vídeos automaticamente com IA local.')); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl"><?php echo e(__('Criar Vídeo')); ?></h1>
        <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Descreva o tema e deixe o pipeline local gerar o vídeo.')); ?></p>
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_380px]">
        <form action="<?php echo e(route('videos.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div>
                <label class="form-label" for="tema"><?php echo e(__('Tema do vídeo')); ?></label>
                <textarea id="tema" name="tema" maxlength="2000" rows="4" required placeholder="<?php echo e(__('ex: café artesanal, energia solar, música independente...')); ?>" class="form-control resize-y"><?php echo e(old('tema')); ?></textarea>
                <div class="mt-1 flex justify-between text-[11px] text-muted-foreground">
                    <span><?php echo e(__('Descreva o tema com o nível de detalhe que quiser.')); ?></span>
                    <span id="temaCount">0 / 2000</span>
                </div>
                <?php $__errorArgs = ['tema'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="form-label mb-0" for="duracao"><?php echo e(__('Duração')); ?></label>
                    <span id="durationValue" class="font-display text-xs font-semibold text-foreground"><?php echo e(old('duracao', 30)); ?>s</span>
                </div>
                <input id="duracao" type="range" name="duracao" min="15" max="120" step="5" value="<?php echo e(old('duracao', 30)); ?>" class="w-full accent-black">
                <div class="mt-1 flex justify-between text-[11px] text-muted-foreground">
                    <span>15s</span><span>120s</span>
                </div>
                <?php $__errorArgs = ['duracao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label" for="idioma"><?php echo e(__('Idioma')); ?></label>
                    <select id="idioma" name="idioma" class="form-control">
                        <option value="PT-BR" <?php if(old('idioma', 'PT-BR') === 'PT-BR'): echo 'selected'; endif; ?>>Português (PT-BR)</option>
                        <option value="EN-US" <?php if(old('idioma') === 'EN-US'): echo 'selected'; endif; ?>>English (EN-US)</option>
                    </select>
                    <?php $__errorArgs = ['idioma'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="form-label"><?php echo e(__('Formato')); ?></label>
                    <select class="form-control opacity-60 cursor-not-allowed" title="<?php echo e(__('Formato')); ?>">
                        <option><?php echo e(__('Vídeo curto com legenda')); ?></option>
                    </select>
                </div>
            </div>

            
            <div id="vozSection" class="card">
                <h2 class="section-title"><?php echo e(__('Voz da narração')); ?></h2>
                <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('Escolha a voz e ouça uma amostra antes de gerar.')); ?></p>

                <input type="hidden" id="voz" name="voz" value="<?php echo e(old('voz', '')); ?>">
                <?php $__errorArgs = ['voz'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                <div id="vozGrid" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    
                </div>
            </div>

            <div class="card">
                <h2 class="section-title"><?php echo e(__('Fontes do conteúdo')); ?></h2>
                <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('Escolha gerar com IA local ou enviar seus próprios arquivos.')); ?></p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label" for="imagens_modo"><?php echo e(__('Imagens')); ?></label>
                        <select id="imagens_modo" name="imagens_modo" class="form-control">
                            <option value="gerar"  <?php if(old('imagens_modo', 'gerar') === 'gerar'): echo 'selected'; endif; ?>><?php echo e(__('Gerar com IA (FLUX)')); ?></option>
                            <option value="upload" <?php if(old('imagens_modo') === 'upload'): echo 'selected'; endif; ?>><?php echo e(__('Enviar minhas imagens')); ?></option>
                        </select>
                        <?php $__errorArgs = ['imagens_modo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label" for="narracao_modo"><?php echo e(__('Narração')); ?></label>
                        <select id="narracao_modo" name="narracao_modo" class="form-control">
                            <option value="gerar"  <?php if(old('narracao_modo', 'gerar') === 'gerar'): echo 'selected'; endif; ?>><?php echo e(__('Gerar com IA (TTS)')); ?></option>
                            <option value="upload" <?php if(old('narracao_modo') === 'upload'): echo 'selected'; endif; ?>><?php echo e(__('Enviar meu áudio')); ?></option>
                            <option value="nenhum" <?php if(old('narracao_modo') === 'nenhum'): echo 'selected'; endif; ?>><?php echo e(__('Sem narração')); ?></option>
                        </select>
                        <?php $__errorArgs = ['narracao_modo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label" for="musica_modo"><?php echo e(__('Música')); ?></label>
                        <select id="musica_modo" name="musica_modo" class="form-control">
                            <option value="gerar"  <?php if(old('musica_modo', 'gerar') === 'gerar'): echo 'selected'; endif; ?>><?php echo e(__('Gerar com IA (ACE-Step)')); ?></option>
                            <option value="upload" <?php if(old('musica_modo') === 'upload'): echo 'selected'; endif; ?>><?php echo e(__('Enviar minha música')); ?></option>
                            <option value="nenhum" <?php if(old('musica_modo') === 'nenhum'): echo 'selected'; endif; ?>><?php echo e(__('Sem música')); ?></option>
                        </select>
                        <?php $__errorArgs = ['musica_modo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label" for="legendas_modo"><?php echo e(__('Legendas')); ?></label>
                        <select id="legendas_modo" name="legendas_modo" class="form-control">
                            <option value="gerar"  <?php if(old('legendas_modo', 'gerar') === 'gerar'): echo 'selected'; endif; ?>><?php echo e(__('Gerar com IA (Whisper)')); ?></option>
                            <option value="upload" <?php if(old('legendas_modo') === 'upload'): echo 'selected'; endif; ?>><?php echo e(__('Enviar meu SRT')); ?></option>
                            <option value="nenhum" <?php if(old('legendas_modo') === 'nenhum'): echo 'selected'; endif; ?>><?php echo e(__('Sem legendas')); ?></option>
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('Legendas exigem narração para serem geradas.')); ?></p>
                        <?php $__errorArgs = ['legendas_modo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div id="imagens_upload_wrap" class="mt-4 hidden">
                    <label class="form-label"><?php echo e(__('Imagens das cenas')); ?></label>
                    <p class="mb-2 text-xs text-muted-foreground"><?php echo e(__('JPG, PNG ou WebP. Até 20 imagens, 10 MB cada. Arraste para reordenar.')); ?></p>

                    <input id="imagens" type="file" name="imagens[]" accept="image/jpeg,image/png,image/webp,image/bmp" multiple class="hidden">

                    
                    <div id="imagensDropZone" class="flex cursor-pointer flex-col items-center justify-center rounded-sm border-2 border-dashed border-border p-8 transition-colors hover:border-foreground/40">
                        <i data-lucide="image-plus" class="h-8 w-8 text-muted-foreground"></i>
                        <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Clique ou arraste imagens aqui')); ?></p>
                    </div>

                    
                    <div id="imagensGrid" class="flex flex-wrap gap-2"></div>

                    
                    <button type="button" id="imagensAddMore" class="mt-3 hidden w-full rounded-sm border-2 border-dashed border-border py-3 text-xs text-muted-foreground transition-colors hover:border-foreground/40 hover:text-foreground">
                        + <?php echo e(__('Adicionar mais imagens')); ?>

                    </button>

                    <?php $__errorArgs = ['imagens'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>   <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['imagens.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div id="narracao_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="narracao"><?php echo e(__('Arquivo de áudio da narração')); ?></label>
                    <input id="narracao" type="file" name="narracao" accept="audio/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('MP3, WAV, M4A ou OGG. Até 50 MB.')); ?></p>
                    <?php $__errorArgs = ['narracao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div id="musica_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="musica"><?php echo e(__('Arquivo de música de fundo')); ?></label>
                    <input id="musica" type="file" name="musica" accept="audio/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('MP3, WAV, M4A ou OGG. Até 100 MB. Será aplicada em 20% de volume.')); ?></p>
                    <?php $__errorArgs = ['musica'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div id="legendas_upload_wrap" class="mt-4 hidden">
                    <label class="form-label" for="legendas"><?php echo e(__('Arquivo de legendas')); ?></label>
                    <input id="legendas" type="file" name="legendas" accept=".srt,.vtt,.txt" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('Formato SRT (recomendado), VTT ou TXT. Até 2 MB.')); ?></p>
                    <?php $__errorArgs = ['legendas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div class="card bg-accent/60">
                <h2 class="section-title"><?php echo e(__('Antes de começar')); ?></h2>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-muted-foreground">
                    <li>• <?php echo e(__('A geração é feita localmente e pode levar entre 6 e 10 minutos.')); ?></li>
                    <li>• <?php echo e(__('Certifique-se de que todos os serviços estão ativos na tela')); ?> <a href="<?php echo e(route('health.index')); ?>" class="underline hover:text-foreground"><?php echo e(__('Serviços')); ?></a>.</li>
                    <li>• <?php echo e(__('Após enviar, você acompanha o progresso em tempo real.')); ?></li>
                </ul>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i data-lucide="rocket" class="h-4 w-4"></i>
                <?php echo e(__('Gerar vídeo')); ?>

            </button>
        </form>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title">Pipeline</h2>
                <div class="mt-5 space-y-4">
                    <?php $__currentLoopData = [
                        [__('Roteiro'), __('IA criativa local')],
                        [__('Imagens'), __('Geração por difusão')],
                        [__('Narração'), __('Síntese de voz')],
                        [__('Música'), __('Composição generativa')],
                        [__('Montagem'), __('Edição automatizada')],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-foreground"></span>
                            <div>
                                <p class="text-sm font-medium text-foreground"><?php echo e($step[0]); ?></p>
                                <p class="text-xs text-muted-foreground"><?php echo e($step[1]); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="card overflow-hidden p-0">
                <img src="<?php echo e(asset('assets/frame-1.jpg')); ?>" alt="Preview visual" class="h-56 w-full object-cover grayscale">
                <div class="p-5">
                    <h2 class="font-display text-sm font-semibold text-foreground">NEW VideoMaker</h2>
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground"><?php echo e(__('Pipeline completo de geração, acompanhamento em tempo real e download em um só lugar.')); ?></p>
                </div>
            </div>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // ── Traduções para JS ───────────────────────────────────
    const T = {
        listen:   <?php echo json_encode(__('Ouvir'), 15, 512) ?>,
        loading:  <?php echo json_encode(__('Carregando...'), 15, 512) ?>,
        playing:  <?php echo json_encode(__('Tocando...'), 15, 512) ?>,
        error:    <?php echo json_encode(__('Erro'), 15, 512) ?>,
        male:     <?php echo json_encode(__('Masculino'), 15, 512) ?>,
        female:   <?php echo json_encode(__('Feminino'), 15, 512) ?>,
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/videos/create.blade.php ENDPATH**/ ?>