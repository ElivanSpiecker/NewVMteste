<?php $__env->startSection('title', __('Configurações') . ' — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Gerencie modelos, preferências e hardware.')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $models = [
        ['icon' => 'image', 'label' => __('Modelo de imagem'), 'value' => 'FLUX.1 Schnell'],
        ['icon' => 'mic', 'label' => __('Modelo de voz'), 'value' => 'Kokoro TTS'],
        ['icon' => 'music', 'label' => __('Modelo de música'), 'value' => 'ACE-Step 1.5'],
        ['icon' => 'subtitles', 'label' => __('Modelo de legendas'), 'value' => 'Whisper'],
        ['icon' => 'brain', 'label' => __('Modelo de linguagem'), 'value' => 'Gemma via Ollama'],
    ];
    $hardware = [
        ['icon' => 'cpu', 'label' => __('GPU detectada'), 'value' => 'NVIDIA RTX 4060 8GB'],
        ['icon' => 'hard-drive', 'label' => __('VRAM disponível'), 'value' => '7.4 GB / 8 GB'],
        ['icon' => 'activity', 'label' => __('Modo de execução'), 'value' => __('Sequencial')],
    ];
?>

<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl"><?php echo e(__('Configurações')); ?></h1>
        <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Gerencie modelos, preferências e hardware.')); ?></p>
    </div>

    <div class="mt-10 max-w-3xl space-y-8">
        <section class="card">
            <h2 class="section-title"><?php echo e(__('Modelos locais')); ?></h2>
            <div class="mt-4 space-y-4">
                <?php $__currentLoopData = $models; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between border-b border-border pb-3 last:border-0">
                        <div class="flex items-center gap-2">
                            <i data-lucide="<?php echo e($model['icon']); ?>" class="h-3.5 w-3.5 text-muted-foreground"></i>
                            <span class="text-sm text-foreground"><?php echo e($model['label']); ?></span>
                        </div>
                        <span class="rounded-sm bg-accent px-3 py-1 font-display text-xs text-muted-foreground"><?php echo e($model['value']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <section class="card">
            <h2 class="section-title"><?php echo e(__('Preferências')); ?></h2>
            <div class="mt-4 space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground"><?php echo e(__('Idioma da interface')); ?></span>
                    <form id="localeForm" action="<?php echo e(route('locale.switch')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <select name="locale" onchange="document.getElementById('localeForm').submit()" class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                            <option value="pt-BR" <?php if(app()->getLocale() === 'pt-BR'): echo 'selected'; endif; ?>><?php echo e(__('Português')); ?></option>
                            <option value="en" <?php if(app()->getLocale() === 'en'): echo 'selected'; endif; ?>><?php echo e(__('Inglês')); ?></option>
                        </select>
                    </form>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground"><?php echo e(__('Duração padrão')); ?></span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option>15 <?php echo e(__('segundos')); ?></option>
                        <option selected>30 <?php echo e(__('segundos')); ?></option>
                        <option>45 <?php echo e(__('segundos')); ?></option>
                        <option>60 <?php echo e(__('segundos')); ?></option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground"><?php echo e(__('Qualidade das imagens')); ?></span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option><?php echo e(__('Alta (1024px)')); ?></option>
                        <option><?php echo e(__('Média (768px)')); ?></option>
                        <option><?php echo e(__('Baixa (512px)')); ?></option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground"><?php echo e(__('Pasta de saída')); ?></span>
                    <input type="text" value="<?php echo e(__('Padrão do sistema')); ?>" class="w-48 rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-foreground"><?php echo e(__('Usar legendas automaticamente')); ?></span>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" checked class="peer sr-only">
                        <div class="h-5 w-9 rounded-full bg-border transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:bg-card after:transition-transform peer-checked:bg-primary peer-checked:after:translate-x-4"></div>
                    </label>
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="section-title"><?php echo e(__('Hardware')); ?></h2>
            <div class="mt-4 space-y-4">
                <?php $__currentLoopData = $hardware; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between border-b border-border pb-3 last:border-0">
                        <div class="flex items-center gap-2">
                            <i data-lucide="<?php echo e($item['icon']); ?>" class="h-3.5 w-3.5 text-muted-foreground"></i>
                            <span class="text-sm text-foreground"><?php echo e($item['label']); ?></span>
                        </div>
                        <span class="rounded-sm bg-accent px-3 py-1 font-display text-xs text-muted-foreground"><?php echo e($item['value']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="mt-2 flex items-center gap-2">
                    <div class="h-2 w-2 animate-pulse rounded-full bg-chart-2"></div>
                    <span class="text-xs text-muted-foreground"><?php echo e(__('Todos os modelos disponíveis')); ?></span>
                </div>
            </div>
        </section>

        <button id="saveBtn" class="btn-primary"><?php echo e(__('Salvar configurações')); ?></button>
        <span id="saveMsg" class="hidden text-xs text-muted-foreground"><?php echo e(__('Configurações salvas.')); ?></span>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.getElementById('saveBtn').addEventListener('click', function () {
        const msg = document.getElementById('saveMsg');
        msg.classList.remove('hidden');
        setTimeout(() => msg.classList.add('hidden'), 2500);
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/pages/config.blade.php ENDPATH**/ ?>