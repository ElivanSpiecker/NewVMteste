<?php $__env->startSection('title', __('Serviços') . ' — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Status em tempo real dos serviços necessários para geração de vídeos.')); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl"><?php echo e(__('Serviços')); ?></h1>
            <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Status em tempo real dos serviços necessários para geração de vídeos.')); ?></p>
        </div>
        <span id="allStatus" class="rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase <?php echo e(collect($services)->every(fn($s) => $s['up']) ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'); ?>">
            <?php echo e(collect($services)->every(fn($s) => $s['up']) ? __('Tudo rodando') : __('Serviço offline')); ?>

        </span>
    </div>

    <div id="servicesList" class="mt-10 grid gap-5 lg:grid-cols-3">
        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="service-card card" data-port="<?php echo e($service['port']); ?>">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-foreground"><?php echo e($service['name']); ?></h2>
                        <p class="mt-1 text-xs text-muted-foreground"><?php echo e($service['host']); ?>:<?php echo e($service['port']); ?></p>
                    </div>
                    <span class="service-dot h-3 w-3 rounded-full <?php echo e($service['up'] ? 'bg-primary' : 'bg-destructive animate-pulse'); ?>"></span>
                </div>
                <p class="service-label mt-6 font-display text-sm font-semibold <?php echo e($service['up'] ? 'text-foreground' : 'text-destructive'); ?>">
                    <?php echo e($service['up'] ? __('Online') : __('Offline')); ?>

                </p>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <section class="card mt-8">
        <h2 class="section-title"><?php echo e(__('Inicialização dos serviços')); ?></h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-foreground">Ollama</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">ollama serve</code>
            </div>
            <div>
                <p class="text-sm font-medium text-foreground">ComfyUI</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">python main.py --lowvram</code>
            </div>
            <div>
                <p class="text-sm font-medium text-foreground">ACE-Step</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">uv run python gradio_app.py</code>
            </div>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const T_ALL_RUNNING = <?php echo json_encode(__('Tudo rodando'), 15, 512) ?>;
    const T_OFFLINE     = <?php echo json_encode(__('Serviço offline'), 15, 512) ?>;
    const T_ONLINE      = <?php echo json_encode(__('Online'), 15, 512) ?>;
    const T_OFF         = <?php echo json_encode(__('Offline'), 15, 512) ?>;

    async function refreshServices() {
        try {
            const response = await fetch('<?php echo e(route('health.api')); ?>');
            const data = await response.json();
            const allStatus = document.getElementById('allStatus');
            allStatus.textContent = data.all_up ? T_ALL_RUNNING : T_OFFLINE;
            allStatus.className = `rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase ${data.all_up ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'}`;

            data.services.forEach((service) => {
                const card = document.querySelector(`.service-card[data-port="${service.port}"]`);
                if (!card) return;
                const dot = card.querySelector('.service-dot');
                const label = card.querySelector('.service-label');
                dot.className = `service-dot h-3 w-3 rounded-full ${service.up ? 'bg-primary' : 'bg-destructive animate-pulse'}`;
                label.textContent = service.up ? T_ONLINE : T_OFF;
                label.className = `service-label mt-6 font-display text-sm font-semibold ${service.up ? 'text-foreground' : 'text-destructive'}`;
            });
        } catch (error) {
            console.error(error);
        }
    }

    setInterval(refreshServices, 4000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/health/index.blade.php ENDPATH**/ ?>