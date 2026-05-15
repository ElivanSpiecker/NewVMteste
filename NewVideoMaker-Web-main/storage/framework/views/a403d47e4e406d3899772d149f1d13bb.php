<?php $__env->startSection('title', 'Sobre — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'NEW VideoMaker — geração automatizada de vídeos com IA 100% local, sem assinatura, sem custo por geração.'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $cards = [
        [
            'icon' => 'target',
            'title' => 'O produto',
            'content' => 'NEW VideoMaker transforma um texto simples em vídeo completo — com imagens, narração, trilha sonora e legenda — de forma totalmente automatizada. Tudo roda na sua máquina, sem APIs externas, sem custo por geração.',
        ],
        [
            'icon' => 'layers',
            'title' => 'Stack de IA',
            'content' => 'FLUX.1 Schnell para imagens cinematográficas, Kokoro TTS para narração natural, ACE-Step 1.5 para música original, Whisper para legendas sincronizadas, Gemma como diretor criativo via Ollama, MoviePy + FFmpeg para montagem final.',
        ],
        [
            'icon' => 'sparkles',
            'title' => 'Diferenciais',
            'items' => [
                'Geração completa de vídeo a partir de um único texto',
                'Execução 100% local — sem nuvem, sem internet obrigatória',
                'Privacidade total: seus dados nunca saem da máquina',
                'Sem assinatura e sem cobrança por vídeo gerado',
                'LLM como agente de direção criativa autônoma',
                'Pipeline otimizado para GPUs de 8 GB de VRAM',
            ],
        ],
        [
            'icon' => 'arrow-up-right',
            'title' => 'Roadmap',
            'items' => [
                'Suporte a múltiplos estilos visuais personalizados',
                'Integração com modelos de vídeo generativo',
                'Interface de edição de timeline',
                'Exportação em múltiplos formatos e resoluções',
                'Versão instalável para Windows com um clique',
            ],
        ],
    ];
?>

<div class="min-h-screen p-8 lg:p-12">
    <div class="max-w-3xl">
        <h1 class="font-display text-5xl font-bold tracking-tight text-foreground lg:text-6xl">NEW VideoMaker</h1>
        <p class="mt-3 font-display text-lg text-muted-foreground">Geração automatizada de vídeos com IA 100% local.</p>
        <div class="mt-6 h-px w-24 bg-foreground"></div>
        <p class="mt-6 max-w-xl text-sm leading-relaxed text-muted-foreground">
            Da ideia ao vídeo pronto em minutos. NEW VideoMaker usa uma cadeia de modelos de inteligência artificial rodando inteiramente na sua máquina para gerar roteiro, imagens, narração, música e legenda — tudo de forma autônoma, a partir de uma descrição em texto.
        </p>
    </div>

    <div class="mt-12 grid gap-6 sm:grid-cols-2">
        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="rounded-sm border border-border bg-card p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="<?php echo e($card['icon']); ?>" class="h-4 w-4 text-foreground"></i>
                    <h2 class="font-display text-sm font-semibold tracking-wider text-foreground uppercase"><?php echo e($card['title']); ?></h2>
                </div>

                <?php if(isset($card['content'])): ?>
                    <p class="mt-3 text-xs leading-relaxed text-muted-foreground"><?php echo e($card['content']); ?></p>
                <?php endif; ?>

                <?php if(isset($card['items'])): ?>
                    <ul class="mt-3 space-y-1.5">
                        <?php $__currentLoopData = $card['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start gap-2 text-xs text-muted-foreground">
                                <span class="mt-1.5 block h-1 w-1 shrink-0 rounded-full bg-foreground"></span>
                                <?php echo e($item); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-12 flex flex-wrap items-center gap-4">
        <a href="<?php echo e(route('videos.create')); ?>" class="btn-primary">Criar vídeo agora</a>
        <a href="<?php echo e(route('pipeline')); ?>" class="btn-outline">Ver pipeline técnico</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/pages/sobre.blade.php ENDPATH**/ ?>