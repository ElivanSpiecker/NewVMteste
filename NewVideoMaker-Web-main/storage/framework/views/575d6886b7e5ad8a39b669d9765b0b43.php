<?php $__env->startSection('title', 'Pipeline — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Fluxo completo de geração automatizada de vídeos.')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $steps = [
        ['icon' => 'message-square', 'title' => __('Descrição textual'), 'tech' => __('Usuário'), 'desc' => __('O usuário envia uma descrição do vídeo desejado.')],
        ['icon' => 'file-text', 'title' => __('Roteiro e direção criativa'), 'tech' => __('Gemma via Ollama'), 'desc' => __('LLM local gera roteiro, prompts de imagem e direção artística.')],
        ['icon' => 'image', 'title' => __('Geração de imagens'), 'tech' => 'FLUX.1 Schnell', 'desc' => __('Modelo de difusão gera imagens de alta qualidade para cada cena.')],
        ['icon' => 'mic', 'title' => __('Narração em áudio'), 'tech' => __('Kokoro TTS'), 'desc' => __('Sintetizador de voz gera narração natural a partir do roteiro.')],
        ['icon' => 'music', 'title' => __('Trilha sonora'), 'tech' => 'ACE-Step 1.5', 'desc' => __('Modelo generativo cria música original para o vídeo.')],
        ['icon' => 'subtitles', 'title' => __('Legendas sincronizadas'), 'tech' => __('Whisper'), 'desc' => __('Modelo de transcrição gera legendas automaticamente.')],
        ['icon' => 'film', 'title' => __('Montagem final'), 'tech' => 'FFmpeg / MoviePy', 'desc' => __('Composição de vídeo, áudio, música e legendas.')],
        ['icon' => 'server', 'title' => __('Orquestração'), 'tech' => __('Laravel'), 'desc' => __('A plataforma coordena e sequencia todas as etapas do pipeline automaticamente.')],
    ];
    $features = [
        ['icon' => 'cpu', 'title' => __('Execução local'), 'desc' => __('Todos os modelos rodam na sua máquina.')],
        ['icon' => 'shield', 'title' => __('Privacidade total'), 'desc' => __('Nenhum dado sai do ambiente local.')],
        ['icon' => 'dollar-sign', 'title' => __('Custo zero'), 'desc' => __('Sem APIs pagas, sem cobranças por geração.')],
        ['icon' => 'server', 'title' => __('Processamento sequencial'), 'desc' => __('Otimizado para VRAM limitada.')],
    ];
?>

<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Pipeline</h1>
        <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Fluxo completo de geração automatizada de vídeos.')); ?></p>
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="group flex items-start gap-4 rounded-sm border border-border bg-card p-5 transition-all duration-300 hover:translate-x-1 hover:shadow-sm">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-sm bg-primary text-primary-foreground">
                        <i data-lucide="<?php echo e($step['icon']); ?>" class="h-4.5 w-4.5"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-display text-sm font-semibold text-foreground"><?php echo e($step['title']); ?></h3>
                            <span class="rounded-sm bg-accent px-2 py-0.5 font-display text-[10px] tracking-wider text-muted-foreground"><?php echo e($step['tech']); ?></span>
                        </div>
                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground"><?php echo e($step['desc']); ?></p>
                    </div>
                    <?php if($index < count($steps) - 1): ?>
                        <i data-lucide="arrow-right" class="mt-3 h-3.5 w-3.5 shrink-0 text-border"></i>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <aside class="space-y-4">
            <h2 class="section-title"><?php echo e(__('Diferenciais')); ?></h2>
            <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-sm border border-border bg-card p-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="<?php echo e($feature['icon']); ?>" class="h-3.5 w-3.5 text-foreground"></i>
                        <h3 class="font-display text-xs font-semibold text-foreground"><?php echo e($feature['title']); ?></h3>
                    </div>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-muted-foreground"><?php echo e($feature['desc']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/pages/pipeline.blade.php ENDPATH**/ ?>