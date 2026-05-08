<?php
    $navItems = [
        ['label' => 'CRIAR', 'route' => 'videos.create', 'icon' => 'plus-square'],
        ['label' => 'DASH', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'VÍDEOS', 'route' => 'videos.index', 'icon' => 'video'],
        ['label' => 'PIPELINE', 'route' => 'pipeline', 'icon' => 'workflow'],
        ['label' => 'CONFIG', 'route' => 'config', 'icon' => 'settings'],
        ['label' => 'SOBRE', 'route' => 'sobre', 'icon' => 'info'],
    ];
?>

<aside class="fixed bottom-0 left-0 top-0 z-50 flex w-[100px] flex-col items-center border-r border-border bg-surface py-8">
    <div class="mb-10">
        <a href="<?php echo e(route('videos.create')); ?>" class="font-display text-lg font-bold tracking-[0.2em] text-foreground">NVM</a>
    </div>

    <nav class="flex flex-1 flex-col items-center gap-2">
        <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route($item['route'])); ?>"
               title="<?php echo e($item['label']); ?>"
               class="w-[76px] rounded-sm px-2 py-2 text-center font-display text-[10px] font-medium tracking-[0.15em] transition-all duration-200 <?php echo e(request()->routeIs($item['route']) || ($item['route'] === 'videos.index' && request()->routeIs('videos.show', 'videos.status')) ? 'bg-primary text-primary-foreground' : 'bg-accent text-accent-foreground hover:bg-primary hover:text-primary-foreground'); ?>">
                <?php echo e($item['label']); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <div class="flex flex-col items-center gap-4 pt-6">
        <a href="<?php echo e(route('health.index')); ?>" class="text-muted-foreground transition-colors hover:text-foreground" aria-label="Serviços">
            <i data-lucide="activity" class="h-4 w-4"></i>
        </a>
        <a href="https://github.com" target="_blank" rel="noopener noreferrer" class="text-muted-foreground transition-colors hover:text-foreground" aria-label="GitHub">
            <i data-lucide="github" class="h-4 w-4"></i>
        </a>
        <a href="mailto:contato@nvm.com" class="text-muted-foreground transition-colors hover:text-foreground" aria-label="E-mail">
            <i data-lucide="mail" class="h-4 w-4"></i>
        </a>
    </div>
</aside>
<?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/components/sidebar.blade.php ENDPATH**/ ?>