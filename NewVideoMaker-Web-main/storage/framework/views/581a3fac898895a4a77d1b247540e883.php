<?php $__env->startSection('title', __('Dashboard') . ' — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Resumo geral da atividade da plataforma.')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $total = $stats['total'] ?? 0;
    $done = $stats['done'] ?? 0;
    $processing = $stats['processing'] ?? 0;
    $failed = $stats['failed'] ?? 0;
?>

<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl"><?php echo e(__('Dashboard')); ?></h1>
        <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Resumo geral da atividade da plataforma.')); ?></p>
    </div>

    <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title"><?php echo e(__('Total')); ?></h2>
                <i data-lucide="video" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground"><?php echo e($total); ?></p>
            <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('vídeos cadastrados')); ?></p>
        </article>
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title"><?php echo e(__('Concluídos')); ?></h2>
                <i data-lucide="check-circle" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground"><?php echo e($done); ?></p>
            <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('prontos para download')); ?></p>
        </article>
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title"><?php echo e(__('Processando')); ?></h2>
                <i data-lucide="loader" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground"><?php echo e($processing); ?></p>
            <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('em execução ou fila')); ?></p>
        </article>
        <article class="card">
            <div class="flex items-center justify-between">
                <h2 class="section-title"><?php echo e(__('Falhas')); ?></h2>
                <i data-lucide="alert-triangle" class="h-4 w-4 text-muted-foreground"></i>
            </div>
            <p class="mt-4 font-display text-4xl font-semibold text-foreground"><?php echo e($failed); ?></p>
            <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('jobs com erro')); ?></p>
        </article>
    </div>

    <div class="mt-10 grid gap-8 xl:grid-cols-[1fr_380px]">
        <section class="card">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-xl font-semibold text-foreground"><?php echo e(__('Últimos vídeos')); ?></h2>
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('Vídeos gerados mais recentemente.')); ?></p>
                </div>
                <a href="<?php echo e(route('videos.index')); ?>" class="btn-outline"><?php echo e(__('Ver todos')); ?></a>
            </div>

            <div class="mt-6 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $recentVideos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center gap-4 rounded-sm border border-border bg-background px-4 py-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-sm bg-accent">
                            <i data-lucide="film" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-foreground"><?php echo e($video->tema); ?></p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground"><?php echo e($video->duracao); ?>s · <?php echo e($video->created_at?->format('d/m/Y H:i')); ?></p>
                        </div>
                        <span class="rounded-sm bg-accent px-2.5 py-1 font-display text-[10px] tracking-wider text-muted-foreground uppercase"><?php echo e($video->statusLabel()); ?></span>
                        <a href="<?php echo e($video->isDone() ? route('videos.show', $video) : route('videos.status', $video)); ?>" class="text-xs font-medium text-foreground hover:underline"><?php echo e(__('Abrir')); ?></a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-sm border border-dashed border-border p-10 text-center">
                        <p class="text-sm text-muted-foreground"><?php echo e(__('Nenhum vídeo gerado ainda.')); ?></p>
                        <a href="<?php echo e(route('videos.create')); ?>" class="mt-4 inline-flex text-xs font-semibold text-foreground hover:underline"><?php echo e(__('Criar primeiro vídeo →')); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="space-y-6">
            <div class="card">
                <h2 class="section-title"><?php echo e(__('Pipeline local')); ?></h2>
                <div class="mt-4 space-y-3 text-sm text-muted-foreground">
                    <div class="flex items-center justify-between"><span><?php echo e(__('Roteiro')); ?></span><span>Gemma/Ollama</span></div>
                    <div class="flex items-center justify-between"><span><?php echo e(__('Imagens')); ?></span><span>ComfyUI/FLUX</span></div>
                    <div class="flex items-center justify-between"><span><?php echo e(__('Narração')); ?></span><span>Kokoro</span></div>
                    <div class="flex items-center justify-between"><span><?php echo e(__('Música')); ?></span><span>ACE-Step</span></div>
                    <div class="flex items-center justify-between"><span><?php echo e(__('Montagem')); ?></span><span>MoviePy</span></div>
                </div>
            </div>
            <div class="card">
                <h2 class="section-title"><?php echo e(__('Atalho')); ?></h2>
                <p class="mt-3 text-sm leading-relaxed text-muted-foreground"><?php echo e(__('Crie um novo vídeo informando tema e duração. A geração é feita automaticamente e você acompanha o progresso em tempo real.')); ?></p>
                <a href="<?php echo e(route('videos.create')); ?>" class="btn-primary mt-5 w-full"><?php echo e(__('Novo vídeo')); ?></a>
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/pages/dashboard.blade.php ENDPATH**/ ?>