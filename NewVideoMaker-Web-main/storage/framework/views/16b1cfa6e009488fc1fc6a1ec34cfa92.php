<?php $__env->startSection('title', __('Vídeo pronto') . ' — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Vídeo pronto')); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="mx-auto max-w-3xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
            <i data-lucide="check" class="h-8 w-8"></i>
        </div>
        <h1 class="mt-6 font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl"><?php echo e(__('Vídeo pronto')); ?></h1>
        <p class="mt-3 text-sm text-muted-foreground"><?php echo e(__('Tema:')); ?> <span class="font-medium text-foreground"><?php echo e($video->tema); ?></span> · <?php echo e($video->duracao); ?>s · <?php echo e($video->idioma ?? 'PT-BR'); ?></p>

        <div class="mt-10 rounded-sm border border-border bg-card p-8">
            <div class="aspect-video overflow-hidden rounded-sm bg-accent">
                <?php if($video->video_path && file_exists($video->video_path)): ?>
                    <video controls class="h-full w-full bg-black">
                        <source src="<?php echo e(route('videos.download', $video)); ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <div class="flex h-full items-center justify-center text-sm text-muted-foreground"><?php echo e(__('Arquivo de vídeo não encontrado no caminho configurado.')); ?></div>
                <?php endif; ?>
            </div>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="<?php echo e(route('videos.download', $video)); ?>" class="btn-primary">
                    <i data-lucide="download" class="h-4 w-4"></i>
                    <?php echo e(__('Baixar MP4')); ?>

                </a>
                <a href="<?php echo e(route('videos.download-srt', $video)); ?>" class="btn-outline">
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    <?php echo e(__('Baixar legenda')); ?>

                </a>
                <button type="button" onclick="document.getElementById('regen-panel').classList.toggle('hidden')" class="btn-outline">
                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    <?php echo e(__('Regenerar')); ?>

                </button>
                <a href="<?php echo e(route('videos.index')); ?>" class="btn-outline"><?php echo e(__('Voltar')); ?></a>
            </div>
        </div>

        <?php
            $hasImagens  = !empty($video->imagens_paths);
            $hasNarracao = $video->narracao_path && file_exists($video->narracao_path);
            $hasMusica   = $video->musica_path   && file_exists($video->musica_path);
            $voicesByLang = \App\Http\Controllers\VoiceController::VOICES;
            $idiomaAtual = $video->idioma ?? 'PT-BR';
        ?>

        <div id="regen-panel" class="hidden mt-6 rounded-sm border border-border bg-card p-8 text-left">
            <h2 class="font-display text-2xl font-semibold text-foreground"><?php echo e(__('Regenerar vídeo')); ?></h2>
            <p class="mt-1 text-sm text-muted-foreground"><?php echo e(__('Reaproveite o que já foi gerado e regere apenas o que quiser mudar.')); ?></p>

            <form method="POST" action="<?php echo e(route('videos.regenerate', $video)); ?>" class="mt-6 space-y-6">
                <?php echo csrf_field(); ?>

                <div>
                    <h3 class="text-sm font-medium text-foreground"><?php echo e(__('Etapas a manter')); ?></h3>
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('O que ficar desmarcado será regerado.')); ?></p>

                    <div class="mt-3 space-y-2">
                        <?php if($hasImagens): ?>
                            <label class="flex items-center gap-2 text-sm text-foreground">
                                <input type="checkbox" name="keep[]" value="imagens" checked class="h-4 w-4 rounded border-border">
                                <?php echo e(__('Manter imagens')); ?> (<?php echo e(count($video->imagens_paths)); ?>)
                            </label>
                        <?php endif; ?>
                        <?php if($hasNarracao): ?>
                            <label class="flex items-center gap-2 text-sm text-foreground">
                                <input type="checkbox" name="keep[]" value="narracao" class="h-4 w-4 rounded border-border">
                                <?php echo e(__('Manter narração')); ?>

                            </label>
                        <?php endif; ?>
                        <?php if($hasMusica): ?>
                            <label class="flex items-center gap-2 text-sm text-foreground">
                                <input type="checkbox" name="keep[]" value="musica" checked class="h-4 w-4 rounded border-border">
                                <?php echo e(__('Manter música')); ?>

                            </label>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-foreground"><?php echo e(__('Idioma')); ?></label>
                        <select name="idioma" class="mt-2 w-full rounded-sm border border-border bg-background px-3 py-2 text-sm">
                            <option value="PT-BR" <?php if($idiomaAtual === 'PT-BR'): echo 'selected'; endif; ?>><?php echo e(__('Português')); ?></option>
                            <option value="EN-US" <?php if($idiomaAtual === 'EN-US'): echo 'selected'; endif; ?>><?php echo e(__('Inglês')); ?></option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-foreground"><?php echo e(__('Voz da narração')); ?></label>
                        <select name="voz" class="mt-2 w-full rounded-sm border border-border bg-background px-3 py-2 text-sm">
                            <option value=""><?php echo e(__('Padrão do idioma')); ?></option>
                            <?php $__currentLoopData = $voicesByLang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang => $voices): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <optgroup label="<?php echo e($lang); ?>">
                                    <?php $__currentLoopData = $voices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($v['id']); ?>" <?php if($video->voz === $v['id']): echo 'selected'; endif; ?>>
                                            <?php echo e($v['name']); ?> (<?php echo e(__($v['gender'])); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </optgroup>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="rounded-sm border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                    <?php echo e(__('Ao trocar a voz ou o idioma, a narração e as legendas serão regeradas mesmo se estiverem marcadas para manter.')); ?>

                </div>

                <button type="submit" class="btn-primary">
                    <i data-lucide="play" class="h-4 w-4"></i>
                    <?php echo e(__('Gerar novo vídeo')); ?>

                </button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/videos/show.blade.php ENDPATH**/ ?>