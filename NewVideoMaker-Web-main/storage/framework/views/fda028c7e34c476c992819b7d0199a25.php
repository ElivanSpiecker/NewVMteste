<?php $__env->startSection('title', __('Gerando vídeo') . ' — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Gerando vídeo')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $steps = [
        ['key' => 'generating_script', 'label' => __('Roteiro'), 'description' => __('Gemma via Ollama'), 'pct' => 17],
        ['key' => 'generating_images', 'label' => __('Imagens'), 'description' => __('FLUX via ComfyUI'), 'pct' => 33],
        ['key' => 'generating_narration', 'label' => __('Narração'), 'description' => __('Kokoro TTS'), 'pct' => 50],
        ['key' => 'generating_music', 'label' => __('Música'), 'description' => __('ACE-Step'), 'pct' => 67],
        ['key' => 'generating_subtitles', 'label' => __('Legendas'), 'description' => __('Whisper'), 'pct' => 83],
        ['key' => 'assembling', 'label' => __('Montagem'), 'description' => __('MoviePy + FFmpeg'), 'pct' => 95],
    ];
?>

<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl"><?php echo e(__('Gerando vídeo')); ?></h1>
        <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Tema:')); ?> <span class="font-medium text-foreground"><?php echo e($video->tema); ?></span> · <?php echo e($video->duracao); ?>s · <?php echo e($video->idioma ?? 'PT-BR'); ?></p>
    </div>

    <div class="mt-10 grid gap-8 xl:grid-cols-[1fr_380px]">
        <section class="card">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-semibold text-foreground"><?php echo e(__('Progresso')); ?></h2>
                <span id="statusLabel" class="rounded-sm bg-accent px-3 py-1 font-display text-[10px] tracking-wider text-muted-foreground uppercase"><?php echo e($video->statusLabel()); ?></span>
            </div>

            <div class="mt-8 h-3 overflow-hidden rounded-full bg-accent">
                <div id="progressBar" class="h-full rounded-full bg-primary transition-all duration-700" style="width: <?php echo e($video->progresso); ?>%"></div>
            </div>
            <p id="progressText" class="mt-2 text-right font-display text-xs text-muted-foreground"><?php echo e($video->progresso); ?>%</p>

            <ol class="mt-8 space-y-4">
                <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="pipeline-step flex items-center gap-4 rounded-sm border border-border bg-background p-4" data-step="<?php echo e($step['key']); ?>">
                        <span class="step-marker flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent font-display text-xs text-muted-foreground">&#9675;</span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-foreground"><?php echo e($step['label']); ?></p>
                            <p class="text-xs text-muted-foreground"><?php echo e($step['description']); ?></p>
                        </div>
                        <span class="text-xs text-muted-foreground"><?php echo e($step['pct']); ?>%</span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>

            <div id="errorBox" class="mt-6 hidden rounded-sm border border-destructive/40 bg-destructive/10 p-4">
                <p class="font-display text-sm font-semibold text-destructive"><?php echo e(__('Falha na geração')); ?></p>
                <pre id="errorText" class="mt-2 max-h-48 overflow-auto whitespace-pre-wrap text-xs text-destructive"></pre>
                <a href="<?php echo e(route('videos.create')); ?>" class="mt-4 inline-flex text-xs font-semibold text-foreground hover:underline"><?php echo e(__('Tentar novamente →')); ?></a>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title"><?php echo e(__('Status atual')); ?></h2>
                <p id="sideStatusLabel" class="mt-4 font-display text-2xl font-semibold text-foreground"><?php echo e($video->statusLabel()); ?></p>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground"><?php echo e(__('O status é atualizado automaticamente. Você será redirecionado assim que o vídeo estiver pronto.')); ?></p>
            </div>
            <a href="<?php echo e(route('videos.index')); ?>" class="btn-outline w-full"><?php echo e(__('Voltar aos vídeos')); ?></a>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const videoId = <?php echo json_encode($video->id, 15, 512) ?>;
    const stepOrder = <?php echo json_encode(array_column($steps, 'key'), 512) ?>;
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
                document.getElementById('errorText').textContent = data.erro || 'Error';
            }

            if (data.done) {
                clearInterval(interval);
                window.location.href = `/videos/${videoId}`;
            }
        } catch (error) {
            console.error(error);
        }
    }

    setStepState(<?php echo json_encode($video->status, 15, 512) ?>, <?php echo json_encode($video->isDone(), 15, 512) ?>);
    <?php if(!$video->isDone() && !$video->hasFailed()): ?>
        interval = setInterval(poll, 3000);
    <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/videos/status.blade.php ENDPATH**/ ?>