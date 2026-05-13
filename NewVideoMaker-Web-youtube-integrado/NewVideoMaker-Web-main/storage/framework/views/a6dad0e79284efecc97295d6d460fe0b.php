<?php $__env->startSection('title', 'YouTube — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Postagens automáticas e agendadas para o YouTube.'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">YouTube</h1>
            <p class="mt-2 text-sm text-muted-foreground">Integração para publicar vídeos gerados automaticamente.</p>
        </div>

        <?php if($connected): ?>
            <span class="rounded-sm bg-primary px-3 py-2 font-display text-[10px] font-medium tracking-wider text-primary-foreground uppercase">Conta conectada</span>
        <?php else: ?>
            <a href="<?php echo e(route('youtube.connect')); ?>" class="btn-primary">
                <i data-lucide="youtube" class="h-4 w-4"></i>
                Conectar YouTube
            </a>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div class="mt-6 rounded-sm border border-primary/30 bg-primary/10 p-4 text-sm text-foreground"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="mt-6 rounded-sm border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <section class="card mt-8">
        <h2 class="section-title">Postagens agendadas</h2>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="border-b border-border text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="py-3 pr-4">Vídeo</th>
                        <th class="py-3 pr-4">Título</th>
                        <th class="py-3 pr-4">Agendamento</th>
                        <th class="py-3 pr-4">Privacidade</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="py-4 pr-4 text-muted-foreground">#<?php echo e($post->video_id); ?></td>
                            <td class="py-4 pr-4">
                                <div class="font-medium text-foreground"><?php echo e($post->title); ?></div>
                                <?php if($post->youtube_video_id): ?>
                                    <a href="https://www.youtube.com/watch?v=<?php echo e($post->youtube_video_id); ?>" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block text-xs text-muted-foreground underline hover:text-foreground">Abrir no YouTube</a>
                                <?php endif; ?>
                                <?php if($post->error): ?>
                                    <p class="mt-1 max-w-md text-xs text-destructive"><?php echo e($post->error); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 pr-4 text-muted-foreground"><?php echo e($post->scheduled_at?->format('d/m/Y H:i') ?? 'Publicar quando o comando rodar'); ?></td>
                            <td class="py-4 pr-4 text-muted-foreground"><?php echo e($post->privacy_status); ?></td>
                            <td class="py-4 pr-4">
                                <span class="rounded-sm bg-accent px-2 py-1 font-display text-[10px] tracking-wider text-muted-foreground uppercase"><?php echo e($post->statusLabel()); ?></span>
                            </td>
                            <td class="py-4 pr-4">
                                <?php if(in_array($post->status, ['scheduled', 'failed'], true)): ?>
                                    <form action="<?php echo e(route('youtube.publish-now', $post)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn-outline text-xs">Publicar agora</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-muted-foreground">Sem ação</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-muted-foreground">Nenhuma postagem agendada ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\55519\Desktop\TCC AUTPOST\NewVideoMaker-Web-youtube-integrado\NewVideoMaker-Web-main\resources\views/youtube/index.blade.php ENDPATH**/ ?>