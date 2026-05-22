<?php $__env->startSection('title', 'Vídeo Pronto — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Download do vídeo gerado.'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="mx-auto max-w-3xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
            <i data-lucide="check" class="h-8 w-8"></i>
        </div>
        <h1 class="mt-6 font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Vídeo pronto</h1>
        <p class="mt-3 text-sm text-muted-foreground">Tema: <span class="font-medium text-foreground"><?php echo e($video->tema); ?></span> · <?php echo e($video->duracao); ?>s</p>

        <div class="mt-10 rounded-sm border border-border bg-card p-8">
            <div class="aspect-video overflow-hidden rounded-sm bg-accent">
                <?php if($video->video_path && file_exists($video->video_path)): ?>
                    <video controls class="h-full w-full bg-black">
                        <source src="<?php echo e(route('videos.download', $video)); ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <div class="flex h-full items-center justify-center text-sm text-muted-foreground">Arquivo de vídeo não encontrado no caminho configurado.</div>
                <?php endif; ?>
            </div>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="<?php echo e(route('videos.download', $video)); ?>" class="btn-primary">
                    <i data-lucide="download" class="h-4 w-4"></i>
                    Baixar MP4
                </a>
                <a href="<?php echo e(route('videos.download-srt', $video)); ?>" class="btn-outline">
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    Baixar legenda
                </a>
                <a href="<?php echo e(route('videos.index')); ?>" class="btn-outline">Voltar</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/videos/show.blade.php ENDPATH**/ ?>