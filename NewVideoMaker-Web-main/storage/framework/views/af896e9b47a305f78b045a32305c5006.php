<?php $__env->startSection('title', 'Configuração inicial — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Vamos preparar tudo que o app precisa para gerar e publicar vídeos.'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="mx-auto max-w-4xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="font-display text-[10px] uppercase tracking-wider text-muted-foreground">Primeira execução</p>
                <h1 class="mt-1 font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Configuração inicial</h1>
                <p class="mt-2 text-sm text-muted-foreground">Vamos detectar e instalar o que falta pro app rodar.</p>
            </div>
            <form method="POST" action="<?php echo e(route('setup.skip')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-xs text-muted-foreground underline hover:text-foreground">Pular por agora</button>
            </form>
        </div>

        <div id="steps" class="mt-10 space-y-5">
            
            <section class="card" data-service="ollama">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-foreground">Ollama</h2>
                        <p class="mt-1 text-xs text-muted-foreground">Servidor de modelos de linguagem (Gemma) usado pelo pipeline.</p>
                    </div>
                    <span data-badge class="rounded-sm px-2 py-1 font-display text-[10px] uppercase tracking-wider"></span>
                </div>
                <div data-info class="mt-3 text-xs text-muted-foreground"></div>
                <div data-actions class="mt-4 flex flex-wrap gap-2"></div>
                <div data-progress class="mt-3 hidden">
                    <div class="h-1.5 w-full overflow-hidden rounded-sm bg-accent">
                        <div data-bar class="h-full bg-primary transition-all" style="width: 0%"></div>
                    </div>
                    <p data-msg class="mt-1 text-[11px] text-muted-foreground"></p>
                </div>
            </section>

            
            <section class="card" data-service="comfyui">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-foreground">ComfyUI</h2>
                        <p class="mt-1 text-xs text-muted-foreground">Gera as imagens com FLUX. Instalação manual — depois aponte a pasta aqui.</p>
                    </div>
                    <span data-badge class="rounded-sm px-2 py-1 font-display text-[10px] uppercase tracking-wider"></span>
                </div>
                <div data-info class="mt-3 text-xs text-muted-foreground"></div>
                <div data-actions class="mt-4 flex flex-wrap items-center gap-2"></div>
            </section>

            
            <section class="card" data-service="acestep">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-foreground">ACE-Step</h2>
                        <p class="mt-1 text-xs text-muted-foreground">Gera a música de fundo. Instalação manual — depois aponte a pasta aqui.</p>
                    </div>
                    <span data-badge class="rounded-sm px-2 py-1 font-display text-[10px] uppercase tracking-wider"></span>
                </div>
                <div data-info class="mt-3 text-xs text-muted-foreground"></div>
                <div data-actions class="mt-4 flex flex-wrap items-center gap-2"></div>
            </section>

            
            <section class="card" data-service="pipeline">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-foreground">Pipeline Python</h2>
                        <p class="mt-1 text-xs text-muted-foreground">O <code>pipeline.py</code> do projeto NewVideoMaker que orquestra tudo.</p>
                    </div>
                    <span data-badge class="rounded-sm px-2 py-1 font-display text-[10px] uppercase tracking-wider"></span>
                </div>
                <div data-info class="mt-3 text-xs text-muted-foreground"></div>
                <div data-actions class="mt-4 flex flex-wrap items-center gap-2"></div>
            </section>

            
            <section class="card" data-service="youtube">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-foreground">YouTube (opcional)</h2>
                        <p class="mt-1 text-xs text-muted-foreground">Necessário só para publicar Shorts diretamente do app.</p>
                    </div>
                    <span data-badge class="rounded-sm px-2 py-1 font-display text-[10px] uppercase tracking-wider"></span>
                </div>
                <div data-info class="mt-3 text-xs text-muted-foreground"></div>
                <div data-actions class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="<?php echo e(route('config')); ?>#youtube" class="btn-outline">
                        <i data-lucide="settings" class="h-4 w-4"></i>
                        Configurar em /config
                    </a>
                </div>
            </section>
        </div>

        <div class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-6">
            <p id="overallStatus" class="text-xs text-muted-foreground">Verificando ambiente...</p>
            <form method="POST" action="<?php echo e(route('setup.complete')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary">
                    <i data-lucide="check" class="h-4 w-4"></i>
                    Concluir e abrir o app
                </button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const STATUS_URL  = <?php echo json_encode(route('setup.status'), 15, 512) ?>;
const INSTALL_URL = <?php echo json_encode(route('setup.install-ollama'), 15, 512) ?>;
const PULL_URL    = <?php echo json_encode(route('setup.pull-model'), 15, 512) ?>;
const SAVE_URL    = <?php echo json_encode(route('setup.save-path'), 15, 512) ?>;
const CONFIG_URL  = <?php echo json_encode(route('config'), 15, 512) ?>;
const CSRF        = '<?php echo e(csrf_token()); ?>';

const INITIAL = {
    snapshot: <?php echo json_encode($snapshot, 15, 512) ?>,
    config:   <?php echo json_encode($config, 15, 512) ?>,
    tasks:    <?php echo json_encode($tasks, 15, 512) ?>,
};

const REQUIRED_MODEL = 'gemma2:2b';

let state = INITIAL;

// ---------- Render por serviço ----------

function renderOllama(s) {
    const card = document.querySelector('[data-service="ollama"]');
    const badge = card.querySelector('[data-badge]');
    const info  = card.querySelector('[data-info]');
    const acts  = card.querySelector('[data-actions]');
    const ok = s.installed && s.running;
    setBadge(badge, ok, ok ? (s.models.length ? 'Pronto' : 'Sem modelo') : (s.installed ? 'Parado' : 'Não instalado'));

    let html = '';
    if (s.installed) html += `<p>Binário: <code>${s.path}</code></p>`;
    if (s.running)   html += `<p>Modelos disponíveis: ${s.models.length ? s.models.map(m=>`<code>${m}</code>`).join(', ') : '—'}</p>`;
    if (!s.installed) html += `<p>Vamos instalar para você. Cerca de 200 MB.</p>`;
    info.innerHTML = html;

    acts.innerHTML = '';
    if (!s.installed) {
        acts.appendChild(button('Instalar Ollama agora', 'download', () => startInstallOllama(card)));
    } else if (!s.models.includes(REQUIRED_MODEL)) {
        acts.appendChild(button(`Baixar modelo ${REQUIRED_MODEL}`, 'package', () => startPullModel(card, REQUIRED_MODEL)));
    }

    // Atualiza barra com tasks ativas
    const activeTask = state.tasks.find(t => (t.kind === 'install_ollama' || t.kind === 'pull_model') && (t.status === 'pending' || t.status === 'running'));
    setProgress(card, activeTask);
}

function renderPathService(key, s, fileLabel, fileSuggest) {
    const card = document.querySelector(`[data-service="${key}"]`);
    const badge = card.querySelector('[data-badge]');
    const info  = card.querySelector('[data-info]');
    const acts  = card.querySelector('[data-actions]');
    const ok = s.installed;
    setBadge(badge, ok, ok ? (s.running ? 'Rodando' : 'Detectado') : 'Não encontrado');

    let html = '';
    if (s.installed) {
        html += `<p>Pasta: <code>${s.path}</code></p>`;
        if (s.venv) html += `<p>Python do venv: <code>${s.venv}</code></p>`;
        html += `<p>Porta: ${s.running ? '<span class="text-chart-2">aberta</span>' : 'fechada (inicie o serviço)'}</p>`;
    } else {
        html += `<p>Não achei em locais comuns. Cole o caminho da pasta abaixo:</p>`;
    }
    info.innerHTML = html;

    acts.innerHTML = '';
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control flex-1 min-w-[260px] font-mono text-xs';
    input.placeholder = fileSuggest;
    input.value = s.path || '';
    acts.appendChild(input);
    acts.appendChild(button('Salvar', 'save', async () => {
        const r = await postJSON(SAVE_URL, { service: key, path: input.value });
        if (!r.ok) {
            alert(r.errors ? r.errors.join('\n') : 'Erro ao salvar.');
        } else {
            await refresh();
        }
    }));
}

function renderPipeline(s, cfg) {
    const card = document.querySelector('[data-service="pipeline"]');
    const badge = card.querySelector('[data-badge]');
    const info  = card.querySelector('[data-info]');
    const acts  = card.querySelector('[data-actions]');
    const has = !!cfg.pipeline_path;
    setBadge(badge, has, has ? 'Configurado' : 'Não configurado');

    info.innerHTML = has
        ? `<p>pipeline.py: <code>${cfg.pipeline_path}</code></p>`
        : `<p>Aponte para o <code>pipeline.py</code> do projeto NewVideoMaker.</p>`;

    acts.innerHTML = '';
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control flex-1 min-w-[260px] font-mono text-xs';
    input.placeholder = 'C:\\caminho\\NewVideoMaker\\pipeline.py';
    input.value = cfg.pipeline_path || '';
    acts.appendChild(input);
    acts.appendChild(button('Salvar', 'save', async () => {
        const r = await postJSON(SAVE_URL, { service: 'pipeline', path: input.value });
        if (!r.ok) alert(r.errors ? r.errors.join('\n') : 'Erro.');
        else await refresh();
    }));
    acts.appendChild(linkButton('Configurar tudo em /config', 'settings', CONFIG_URL));
}

function renderYoutube(cfg) {
    const card = document.querySelector('[data-service="youtube"]');
    const badge = card.querySelector('[data-badge]');
    const info  = card.querySelector('[data-info]');
    setBadge(badge, cfg.youtube_ok, cfg.youtube_ok ? 'Configurado' : 'Pendente');
    info.innerHTML = cfg.youtube_ok
        ? `<p>Credenciais OAuth do Google salvas. Você já pode conectar canais e publicar.</p>`
        : `<p>Sem credenciais não dá pra publicar no YouTube. Configure quando quiser usar Shorts.</p>`;
}

// ---------- Helpers ----------

function setBadge(el, ok, text) {
    el.textContent = text;
    el.className = 'rounded-sm px-2 py-1 font-display text-[10px] uppercase tracking-wider ' +
        (ok ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground');
}

function setProgress(card, task) {
    const wrap = card.querySelector('[data-progress]');
    if (!wrap) return;
    if (!task) { wrap.classList.add('hidden'); return; }
    wrap.classList.remove('hidden');
    card.querySelector('[data-bar]').style.width = (task.progresso || 0) + '%';
    card.querySelector('[data-msg]').textContent = task.mensagem || task.status;
}

function button(label, icon, onClick) {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'btn-primary';
    b.innerHTML = `<i data-lucide="${icon}" class="h-4 w-4"></i> ${label}`;
    b.addEventListener('click', onClick);
    return b;
}

function linkButton(label, icon, href) {
    const a = document.createElement('a');
    a.className = 'btn-outline';
    a.href = href;
    a.innerHTML = `<i data-lucide="${icon}" class="h-4 w-4"></i> ${label}`;
    return a;
}

async function postJSON(url, body) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(body || {}),
    });
    if (!r.ok && r.status !== 422) throw new Error('HTTP ' + r.status);
    return r.json();
}

async function startInstallOllama(card) {
    setProgress(card, { progresso: 1, mensagem: 'Enfileirando download...' });
    try {
        await postJSON(INSTALL_URL);
        await refresh();
    } catch (e) { alert('Erro: ' + e.message); }
}

async function startPullModel(card, model) {
    setProgress(card, { progresso: 1, mensagem: 'Iniciando pull...' });
    try {
        await postJSON(PULL_URL, { model });
        await refresh();
    } catch (e) { alert('Erro: ' + e.message); }
}

async function refresh() {
    try {
        const r = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
        state = await r.json();
        // remap snapshot+config para shape esperado
        state.snapshot = state.snapshot;
        state.config = INITIAL.config; // config mudou? recarregar pagina pra YouTube
        renderAll();
    } catch { /* silencia */ }
}

function renderAll() {
    renderOllama(state.snapshot.ollama);
    renderPathService('comfyui', state.snapshot.comfyui, 'main.py', 'C:\\caminho\\ComfyUI');
    renderPathService('acestep', state.snapshot.acestep, 'app.py', 'C:\\caminho\\ACE-Step');
    renderPipeline(state.snapshot, state.config);
    renderYoutube(state.config);

    // Status geral
    const issues = [];
    if (!state.snapshot.ollama.installed) issues.push('Ollama');
    else if (!state.snapshot.ollama.models.includes(REQUIRED_MODEL)) issues.push('modelo Gemma');
    if (!state.snapshot.comfyui.installed) issues.push('ComfyUI');
    if (!state.snapshot.acestep.installed) issues.push('ACE-Step');
    if (!state.config.pipeline_path) issues.push('pipeline.py');
    document.getElementById('overallStatus').textContent = issues.length === 0
        ? 'Tudo configurado. Pode concluir.'
        : 'Faltando: ' + issues.join(', ');

    if (window.lucide) lucide.createIcons();
}

renderAll();
setInterval(refresh, 3500);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/setup/wizard.blade.php ENDPATH**/ ?>