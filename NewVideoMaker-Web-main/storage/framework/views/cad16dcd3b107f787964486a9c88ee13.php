<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'NEW VideoMaker'); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('description', 'Plataforma de geração automatizada de vídeos curtos com inteligência artificial local.'); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="antialiased">
    <?php $appConfig = app('App\Services\AppConfig'); ?>
    <?php $pendencies = $appConfig->pendencies(); ?>

    <div class="flex min-h-screen">
        <?php echo $__env->make('components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="ml-[100px] flex-1">
            <?php if(!empty($pendencies) && !request()->routeIs('config') && !request()->routeIs('setup.*')): ?>
                <div class="border-b border-destructive/30 bg-destructive/5 px-6 py-2.5 text-xs">
                    <div class="flex flex-wrap items-center gap-2 text-destructive">
                        <i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i>
                        <span>Configuração incompleta — falta: <strong><?php echo e(implode(', ', $pendencies)); ?></strong>.</span>
                        <a href="<?php echo e(route('setup.index')); ?>" class="ml-auto underline hover:no-underline">Abrir wizard →</a>
                        <a href="<?php echo e(route('config')); ?>" class="underline hover:no-underline">ou /config</a>
                    </div>
                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/layouts/app.blade.php ENDPATH**/ ?>