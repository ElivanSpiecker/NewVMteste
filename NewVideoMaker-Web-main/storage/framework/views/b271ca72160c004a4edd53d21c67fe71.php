<?php $__env->startSection('title', 'Serviços — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Status dos serviços locais do pipeline.'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Serviços</h1>
            <p class="mt-2 text-sm text-muted-foreground">Status em tempo real dos serviços necessários para geração de vídeos.</p>
        </div>
        <span id="allStatus" class="rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase <?php echo e(collect($services)->every(fn($s) => $s['up']) ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'); ?>">
            <?php echo e(collect($services)->every(fn($s) => $s['up']) ? 'Tudo rodando' : 'Serviço offline'); ?>

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
                    <?php echo e($service['up'] ? 'Online' : 'Offline'); ?>

                </p>
                <p class="service-detail mt-2 text-[11px] text-muted-foreground"><?php echo e($service['detail'] ?? ''); ?></p>
                <?php if(!$service['up'] && !empty($service['install'])): ?>
                    <a href="<?php echo e($service['install']); ?>" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 text-[11px] text-muted-foreground underline hover:text-foreground">
                        Como instalar ↗
                    </a>
                <?php endif; ?>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if(isset($pipeline)): ?>
        <section class="card mt-8">
            <h2 class="section-title">Pipeline Python</h2>
            <p class="mt-1 text-xs text-muted-foreground">Caminhos configurados em <a href="<?php echo e(route('config')); ?>" class="underline">CONFIG → Pipeline</a>.</p>
            <div class="mt-4 space-y-2 text-xs">
                <?php $__currentLoopData = ['python' => 'Python', 'pipeline' => 'pipeline.py', 'output_dir' => 'Pasta de saída']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $info = $pipeline[$k]; ?>
                    <div class="flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full <?php echo e($info['ok'] ? 'bg-chart-2' : 'bg-destructive'); ?>"></span>
                        <span class="w-32 text-foreground"><?php echo e($label); ?>:</span>
                        <span class="flex-1 break-all text-muted-foreground"><?php echo e($info['path'] ?: '—'); ?></span>
                        <?php if(!$info['ok']): ?><span class="text-destructive"><?php echo e($info['reason']); ?></span><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="card mt-8">
        <h2 class="section-title">Inicialização dos serviços</h2>
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
    async function refreshServices() {
        try {
            const response = await fetch('<?php echo e(route('health.api')); ?>');
            const data = await response.json();
            const allStatus = document.getElementById('allStatus');
            allStatus.textContent = data.all_up ? 'Tudo rodando' : 'Serviço offline';
            allStatus.className = `rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase ${data.all_up ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'}`;

            data.services.forEach((service) => {
                const card = document.querySelector(`.service-card[data-port="${service.port}"]`);
                if (!card) return;
                const dot = card.querySelector('.service-dot');
                const label = card.querySelector('.service-label');
                const detail = card.querySelector('.service-detail');
                dot.className = `service-dot h-3 w-3 rounded-full ${service.up ? 'bg-primary' : 'bg-destructive animate-pulse'}`;
                label.textContent = service.up ? 'Online' : 'Offline';
                label.className = `service-label mt-6 font-display text-sm font-semibold ${service.up ? 'text-foreground' : 'text-destructive'}`;
                if (detail) detail.textContent = service.detail || '';
            });
        } catch (error) {
            console.error(error);
        }
    }

    setInterval(refreshServices, 4000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/health/index.blade.php ENDPATH**/ ?>