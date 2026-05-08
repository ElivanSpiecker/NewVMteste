<?php $__env->startSection('title', 'Criar Vídeo — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Crie vídeos automaticamente com IA local.'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Criar Vídeo</h1>
        <p class="mt-2 text-sm text-muted-foreground">Descreva o tema e deixe o pipeline local gerar o vídeo.</p>
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_380px]">
        <form action="<?php echo e(route('videos.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div>
                <label class="form-label" for="tema">Tema do vídeo</label>
                <input id="tema" type="text" name="tema" value="<?php echo e(old('tema')); ?>" maxlength="200" required placeholder="ex: café artesanal, futebol amador, inteligência artificial..." class="form-control">
                <?php $__errorArgs = ['tema'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="form-label mb-0" for="duracao">Duração</label>
                    <span id="durationValue" class="font-display text-xs font-semibold text-foreground"><?php echo e(old('duracao', 30)); ?>s</span>
                </div>
                <input id="duracao" type="range" name="duracao" min="15" max="120" step="5" value="<?php echo e(old('duracao', 30)); ?>" class="w-full accent-black">
                <div class="mt-1 flex justify-between text-[11px] text-muted-foreground">
                    <span>15s</span><span>120s</span>
                </div>
                <?php $__errorArgs = ['duracao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Idioma</label>
                    <select class="form-control" disabled>
                        <option>Português PT-BR</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Formato</label>
                    <select class="form-control" disabled>
                        <option>Vídeo curto com legenda</option>
                    </select>
                </div>
            </div>

            <div class="card bg-accent/60">
                <h2 class="section-title">Observações</h2>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-muted-foreground">
                    <li>• O pipeline roda localmente via fila Laravel.</li>
                    <li>• Verifique Ollama, ComfyUI e ACE-Step antes de gerar.</li>
                    <li>• Após enviar, você será direcionado para o status do processamento.</li>
                </ul>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i data-lucide="rocket" class="h-4 w-4"></i>
                Gerar vídeo
            </button>
        </form>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title">Pipeline</h2>
                <div class="mt-5 space-y-4">
                    <?php $__currentLoopData = [['Roteiro', 'Gemma via Ollama'], ['Imagens', 'FLUX via ComfyUI'], ['Narração', 'Kokoro TTS'], ['Música', 'ACE-Step'], ['Montagem', 'MoviePy + FFmpeg']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-foreground"></span>
                            <div>
                                <p class="text-sm font-medium text-foreground"><?php echo e($step[0]); ?></p>
                                <p class="text-xs text-muted-foreground"><?php echo e($step[1]); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="card overflow-hidden p-0">
                <img src="<?php echo e(asset('assets/frame-1.jpg')); ?>" alt="Preview visual" class="h-56 w-full object-cover grayscale">
                <div class="p-5">
                    <h2 class="font-display text-sm font-semibold text-foreground">NEW VideoMaker</h2>
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">Interface integrada ao backend Laravel existente, mantendo geração, status e downloads.</p>
                </div>
            </div>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const duracao = document.getElementById('duracao');
    const durationValue = document.getElementById('durationValue');
    if (duracao && durationValue) {
        duracao.addEventListener('input', () => durationValue.textContent = `${duracao.value}s`);
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrador\Downloads\NewVideoMaker-Web-integrado-tailwind-blade\NewVideoMaker-Web-main\resources\views/videos/create.blade.php ENDPATH**/ ?>