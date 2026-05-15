<?php $__env->startSection('title', 'Status da publicação — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Acompanhe o envio do vídeo ao YouTube.'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="mx-auto max-w-3xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-display text-[10px] tracking-wider uppercase text-muted-foreground">Publicação no YouTube</p>
                <h1 class="mt-1 font-display text-3xl font-bold tracking-tight text-foreground lg:text-4xl"><?php echo e($upload->title); ?></h1>
                <p class="mt-2 text-xs text-muted-foreground">
                    Canal: <span class="text-foreground"><?php echo e($upload->account?->display_name ?? '—'); ?></span>
                    · Privacidade: <span class="text-foreground"><?php echo e($upload->privacy_status); ?></span>
                    <?php if($upload->scheduled_at): ?>
                        · Agendado para <span class="text-foreground"><?php echo e($upload->scheduled_at->format('d/m/Y H:i')); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?php echo e(route('shorts.index')); ?>" class="btn-outline">Voltar</a>
        </div>

        <?php if(session('success')): ?>
            <div class="mt-6 rounded-sm border border-border bg-card px-4 py-3 text-sm text-foreground">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="mt-8 card">
            <div class="flex items-center gap-3">
                <span id="statusDot" class="h-2 w-2 animate-pulse rounded-full bg-chart-2"></span>
                <span id="statusLabel" class="font-display text-sm font-semibold text-foreground"><?php echo e($upload->statusLabel()); ?></span>
            </div>
            <div class="mt-4 h-2 w-full overflow-hidden rounded-sm bg-accent">
                <div id="progressBar" class="h-full bg-primary transition-all" style="width: <?php echo e($upload->progresso); ?>%"></div>
            </div>
            <p id="progressText" class="mt-2 text-[11px] text-muted-foreground"><?php echo e($upload->progresso); ?>%</p>

            <div id="erroBlock" class="mt-4 hidden rounded-sm border border-destructive/40 bg-destructive/5 px-3 py-2 text-xs text-destructive">
                <span id="erroText"><?php echo e($upload->erro); ?></span>
            </div>

            <div id="successBlock" class="mt-4 hidden rounded-sm border border-border bg-card px-3 py-2 text-xs text-foreground">
                <span id="successText"></span>
                <a id="successLink" href="#" target="_blank" rel="noopener" class="ml-2 underline">Abrir no YouTube ↗</a>
            </div>
        </div>

        <div class="mt-6 card">
            <h2 class="section-title">Detalhes</h2>
            <dl class="mt-3 grid grid-cols-1 gap-3 text-xs sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">Categoria</dt>
                    <dd class="text-foreground"><?php echo e($upload->category_id); ?></dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Direcionado a crianças</dt>
                    <dd class="text-foreground"><?php echo e($upload->made_for_kids ? 'Sim' : 'Não'); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-muted-foreground">Tags</dt>
                    <dd class="text-foreground"><?php echo e($upload->tags ?: '—'); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-muted-foreground">Descrição</dt>
                    <dd class="whitespace-pre-wrap text-foreground"><?php echo e($upload->description ?: '—'); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-muted-foreground">Arquivo de origem</dt>
                    <dd class="break-all text-foreground"><?php echo e($upload->source_path); ?></dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="<?php echo e(route('shorts.destroy', $upload)); ?>" class="mt-6 text-right" onsubmit="return confirm('Remover este registro?');">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="rounded-sm border border-border px-3 py-1.5 text-[10px] uppercase tracking-wider text-muted-foreground transition-colors hover:bg-destructive hover:text-destructive-foreground hover:border-destructive">
                Remover registro
            </button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const pollUrl = <?php echo json_encode(route('shorts.poll', $upload), 512) ?>;
    const statusLabel = document.getElementById('statusLabel');
    const statusDot = document.getElementById('statusDot');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const erroBlock = document.getElementById('erroBlock');
    const erroText = document.getElementById('erroText');
    const successBlock = document.getElementById('successBlock');
    const successText = document.getElementById('successText');
    const successLink = document.getElementById('successLink');

    let timer = null;

    async function poll() {
        try {
            const res = await fetch(pollUrl, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();

            statusLabel.textContent = data.statusLabel;
            progressBar.style.width = `${data.progresso}%`;
            progressText.textContent = `${data.progresso}%`;

            if (data.erro) {
                erroBlock.classList.remove('hidden');
                erroText.textContent = data.erro;
            } else {
                erroBlock.classList.add('hidden');
            }

            if (data.status === 'published' || data.status === 'scheduled') {
                statusDot.classList.remove('animate-pulse');
                if (data.youtubeUrl) {
                    successBlock.classList.remove('hidden');
                    successText.textContent = data.status === 'published' ? 'Publicado com sucesso.' : 'Agendado no YouTube.';
                    successLink.href = data.youtubeUrl;
                }
                clearInterval(timer);
            }

            if (data.status === 'failed') {
                statusDot.classList.remove('animate-pulse');
                statusDot.classList.add('bg-destructive');
                clearInterval(timer);
            }
        } catch (e) { /* silencia falhas de rede transitórias */ }
    }

    timer = setInterval(poll, 3500);
    poll();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/shorts/show.blade.php ENDPATH**/ ?>