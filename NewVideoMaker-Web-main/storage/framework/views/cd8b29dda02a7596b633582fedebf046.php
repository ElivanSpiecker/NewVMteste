<?php $__env->startSection('title', 'Configurações — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Configurações da plataforma NEW VideoMaker.'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $models = [
        ['icon' => 'image', 'label' => 'Modelo de imagem', 'value' => 'FLUX.1 Schnell'],
        ['icon' => 'mic', 'label' => 'Modelo de voz', 'value' => 'Kokoro TTS'],
        ['icon' => 'music', 'label' => 'Modelo de música', 'value' => 'ACE-Step 1.5'],
        ['icon' => 'subtitles', 'label' => 'Modelo de legendas', 'value' => 'Whisper'],
        ['icon' => 'brain', 'label' => 'Modelo de linguagem', 'value' => 'Gemma via Ollama'],
    ];
    $hardware = [
        ['icon' => 'cpu', 'label' => 'GPU detectada', 'value' => 'NVIDIA RTX 4060 8GB'],
        ['icon' => 'hard-drive', 'label' => 'VRAM disponível', 'value' => '7.4 GB / 8 GB'],
        ['icon' => 'activity', 'label' => 'Modo de execução', 'value' => 'Sequencial'],
    ];
?>

<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Configurações</h1>
        <p class="mt-2 text-sm text-muted-foreground">Gerencie modelos, preferências e hardware.</p>
    </div>

    <div class="mt-10 max-w-3xl space-y-8">
        <section class="card">
            <h2 class="section-title">Modelos locais</h2>
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
            <h2 class="section-title">Preferências</h2>
            <div class="mt-4 space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Idioma padrão</span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option>Português PT-BR</option>
                        <option>Inglês EN-US</option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Duração padrão</span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option>15 segundos</option>
                        <option selected>30 segundos</option>
                        <option>45 segundos</option>
                        <option>60 segundos</option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Qualidade das imagens</span>
                    <select class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                        <option>Alta (1024px)</option>
                        <option>Média (768px)</option>
                        <option>Baixa (512px)</option>
                    </select>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">Pasta de saída</span>
                    <input type="text" value="Padrão do sistema" class="w-48 rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-foreground">Usar legendas automaticamente</span>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" checked class="peer sr-only">
                        <div class="h-5 w-9 rounded-full bg-border transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:bg-card after:transition-transform peer-checked:bg-primary peer-checked:after:translate-x-4"></div>
                    </label>
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="section-title">Hardware</h2>
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
                    <span class="text-xs text-muted-foreground">Todos os modelos disponíveis</span>
                </div>
            </div>
        </section>

        <button id="saveBtn" class="btn-primary">Salvar configurações</button>
        <span id="saveMsg" class="hidden text-xs text-muted-foreground">Configurações salvas.</span>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/pages/config.blade.php ENDPATH**/ ?>