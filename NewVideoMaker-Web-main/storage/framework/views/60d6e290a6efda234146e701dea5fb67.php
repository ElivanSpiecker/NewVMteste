<?php $__env->startSection('title', 'Meus Vídeos — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Histórico de vídeos gerados.'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Meus Vídeos</h1>
            <p class="mt-2 text-sm text-muted-foreground">Histórico real dos vídeos criados pelo sistema.</p>
        </div>
        <a href="<?php echo e(route('videos.create')); ?>" class="btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Novo vídeo
        </a>
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-3">
        <?php $__currentLoopData = ['Todos', 'Concluídos', 'Em processamento', 'Com erro']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" data-filter="<?php echo e($filter); ?>" class="video-filter rounded-sm px-3 py-1.5 font-display text-[10px] font-medium tracking-wider uppercase transition-colors <?php echo e($index === 0 ? 'bg-primary text-primary-foreground' : 'bg-accent text-accent-foreground hover:bg-primary hover:text-primary-foreground'); ?>">
                <?php echo e($filter); ?>

            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="relative ml-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"></i>
            <input id="videoSearch" type="text" placeholder="Buscar por tema..." class="w-56 rounded-sm border border-border bg-card py-2 pl-9 pr-3 text-xs text-foreground outline-none transition-colors focus:border-foreground">
        </div>
    </div>

    <div id="videosGrid" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php $__empty_1 = true; $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $statusKey = $video->isDone() ? 'concluído' : ($video->hasFailed() ? 'erro' : 'processando');
                $statusClass = match ($statusKey) {
                    'concluído' => 'bg-primary text-primary-foreground',
                    'processando' => 'bg-accent text-accent-foreground',
                    default => 'bg-destructive text-destructive-foreground',
                };
                $thumb = asset('assets/frame-' . (($loop->iteration - 1) % 6 + 1) . '.jpg');
            ?>
            <article data-title="<?php echo e(strtolower($video->tema)); ?>" data-status="<?php echo e($statusKey); ?>" class="video-card group overflow-hidden rounded-sm border border-border bg-card transition-transform duration-300 hover:-translate-y-1">
                <div class="relative aspect-[4/3] overflow-hidden bg-accent">
                    <img src="<?php echo e($thumb); ?>" alt="<?php echo e($video->tema); ?>" class="h-full w-full object-cover grayscale transition-all duration-300 group-hover:scale-105 group-hover:grayscale-0">
                    <div class="absolute right-2 top-2 rounded-sm px-2 py-0.5 font-display text-[10px] tracking-wider uppercase <?php echo e($statusClass); ?>">
                        <?php echo e($video->statusLabel()); ?>

                    </div>
                    <?php if(!$video->isDone() && !$video->hasFailed()): ?>
                        <div class="absolute bottom-0 left-0 h-1 bg-primary transition-all" style="width: <?php echo e($video->progresso); ?>%"></div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="truncate font-display text-sm font-semibold text-foreground"><?php echo e($video->tema); ?></h3>
                    <div class="mt-1 flex items-center gap-3 text-[11px] text-muted-foreground">
                        <span><?php echo e($video->created_at?->format('d/m/Y H:i')); ?></span>
                        <span>•</span>
                        <span><?php echo e($video->duracao); ?>s</span>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="<?php echo e($video->isDone() ? route('videos.show', $video) : route('videos.status', $video)); ?>" class="flex items-center gap-1 rounded-sm border border-border px-2.5 py-1.5 text-[10px] font-medium text-foreground transition-colors hover:bg-accent"><i data-lucide="eye" class="h-3 w-3"></i> Ver</a>
                        <?php if($video->isDone()): ?>
                            <a href="<?php echo e(route('videos.download', $video)); ?>" class="flex items-center gap-1 rounded-sm border border-border px-2.5 py-1.5 text-[10px] font-medium text-foreground transition-colors hover:bg-accent"><i data-lucide="download" class="h-3 w-3"></i> Baixar</a>
                            <a href="<?php echo e(route('videos.download-srt', $video)); ?>" class="flex items-center gap-1 rounded-sm border border-border px-2.5 py-1.5 text-[10px] font-medium text-foreground transition-colors hover:bg-accent"><i data-lucide="file-text" class="h-3 w-3"></i> SRT</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full rounded-sm border border-dashed border-border p-16 text-center">
                <i data-lucide="video" class="mx-auto h-10 w-10 text-muted-foreground"></i>
                <p class="mt-4 text-sm text-muted-foreground">Nenhum vídeo gerado ainda.</p>
                <a href="<?php echo e(route('videos.create')); ?>" class="btn-primary mt-6">Gerar primeiro vídeo</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const filterButtons = document.querySelectorAll('.video-filter');
    const searchInput = document.getElementById('videoSearch');
    const cards = document.querySelectorAll('.video-card');
    let activeFilter = 'Todos';

    function normalizeStatus(filter) {
        if (filter === 'Concluídos') return 'concluído';
        if (filter === 'Em processamento') return 'processando';
        if (filter === 'Com erro') return 'erro';
        return 'Todos';
    }

    function applyFilters() {
        const query = (searchInput?.value || '').toLowerCase();
        const status = normalizeStatus(activeFilter);
        cards.forEach((card) => {
            const matchTitle = card.dataset.title.includes(query);
            const matchStatus = status === 'Todos' || card.dataset.status === status;
            card.classList.toggle('hidden', !(matchTitle && matchStatus));
        });
    }

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.filter;
            filterButtons.forEach((item) => {
                item.className = 'video-filter rounded-sm bg-accent px-3 py-1.5 font-display text-[10px] font-medium tracking-wider text-accent-foreground uppercase transition-colors hover:bg-primary hover:text-primary-foreground';
            });
            button.className = 'video-filter rounded-sm bg-primary px-3 py-1.5 font-display text-[10px] font-medium tracking-wider text-primary-foreground uppercase transition-colors';
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/videos/index.blade.php ENDPATH**/ ?>