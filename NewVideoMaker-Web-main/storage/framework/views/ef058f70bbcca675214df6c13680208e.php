<?php $__env->startSection('title', 'Publicar no YouTube — NEW VideoMaker'); ?>
<?php $__env->startSection('description', 'Envie ou agende um vídeo curto para o YouTube Shorts.'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $minDate = now()->addMinutes(10)->format('Y-m-d\TH:i');
?>

<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Publicar no YouTube</h1>
        <p class="mt-2 text-sm text-muted-foreground">Use um vídeo já gerado pelo pipeline ou faça upload manual.</p>
    </div>

    <?php if(session('error')): ?>
        <div class="mt-6 rounded-sm border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm text-destructive">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($accounts->isEmpty()): ?>
        <div class="mt-10 rounded-sm border border-dashed border-border p-12 text-center">
            <i data-lucide="youtube" class="mx-auto h-12 w-12 text-muted-foreground"></i>
            <p class="mt-4 text-sm text-foreground">Nenhum canal conectado.</p>
            <p class="mt-1 text-xs text-muted-foreground">Conecte um canal do YouTube antes de publicar.</p>
            <a href="<?php echo e(route('shorts.connect')); ?>" class="btn-primary mt-6">
                <i data-lucide="link" class="h-4 w-4"></i>
                Conectar canal
            </a>
        </div>
    <?php else: ?>
    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_360px]">
        <form action="<?php echo e(route('shorts.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="card">
                <h2 class="section-title">Canal de destino</h2>
                <div class="mt-3">
                    <label class="form-label" for="youtube_account_id">Canal</label>
                    <select id="youtube_account_id" name="youtube_account_id" class="form-control">
                        <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($account->id); ?>" <?php if(old('youtube_account_id') == $account->id): echo 'selected'; endif; ?>>
                                <?php echo e($account->display_name); ?> <?php echo e($account->channel_id ? '(' . $account->channel_id . ')' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['youtube_account_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Origem do vídeo</h2>

                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-border bg-background p-4 transition-colors hover:bg-accent">
                        <input type="radio" name="origem" value="existente" class="mt-1" <?php if(old('origem', 'existente') === 'existente'): echo 'checked'; endif; ?>>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Usar vídeo gerado</p>
                            <p class="mt-1 text-xs text-muted-foreground">Selecione um vídeo já produzido pelo pipeline.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-border bg-background p-4 transition-colors hover:bg-accent">
                        <input type="radio" name="origem" value="upload" class="mt-1" <?php if(old('origem') === 'upload'): echo 'checked'; endif; ?>>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Enviar arquivo</p>
                            <p class="mt-1 text-xs text-muted-foreground">MP4, MOV, AVI, WMV, MPEG, WEBM, MKV, FLV ou 3GP. Até 256 MB.</p>
                        </div>
                    </label>
                </div>

                <div id="bloco_existente" class="mt-5">
                    <label class="form-label" for="video_id">Vídeo gerado</label>
                    <?php if($videosProntos->isEmpty()): ?>
                        <p class="rounded-sm border border-dashed border-border px-3 py-4 text-xs text-muted-foreground">
                            Nenhum vídeo gerado ainda. Crie um pelo menu CRIAR primeiro.
                        </p>
                    <?php else: ?>
                        <select id="video_id" name="video_id" class="form-control">
                            <option value="">— selecione —</option>
                            <?php $__currentLoopData = $videosProntos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($video->id); ?>" <?php if(old('video_id') == $video->id): echo 'selected'; endif; ?>>
                                    #<?php echo e($video->id); ?> · <?php echo e($video->tema); ?> · <?php echo e($video->duracao); ?>s
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>
                    <?php $__errorArgs = ['video_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div id="bloco_upload" class="mt-5 hidden">
                    <label class="form-label" for="video">Arquivo de vídeo</label>
                    <input id="video" type="file" name="video" accept=".mp4,.mov,.avi,.wmv,.mpeg,.mpg,.webm,.mkv,.flv,.3gp,video/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">YouTube recomenda formato vertical 9:16 e duração ≤ 60 s para Shorts.</p>
                    <?php $__errorArgs = ['video'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Metadados</h2>

                <div class="mt-4 space-y-5">
                    <div>
                        <label class="form-label" for="title">Título *</label>
                        <input id="title" type="text" name="title" maxlength="100" required value="<?php echo e(old('title')); ?>" class="form-control" placeholder="Ex: Café artesanal em 60 segundos #shorts">
                        <p class="mt-1 text-[11px] text-muted-foreground"><span id="titleCount">0</span> / 100</p>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label" for="description">Descrição</label>
                        <textarea id="description" name="description" maxlength="5000" rows="5" class="form-control resize-none" placeholder="Conte sobre o vídeo, links e hashtags..."><?php echo e(old('description')); ?></textarea>
                        <p class="mt-1 text-[11px] text-muted-foreground"><span id="descCount">0</span> / 5000</p>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="form-label" for="tags">Tags</label>
                        <input id="tags" type="text" name="tags" maxlength="500" value="<?php echo e(old('tags')); ?>" class="form-control" placeholder="cafe, especial, brasil">
                        <p class="mt-1 text-[11px] text-muted-foreground">Separe por vírgula. Limite total: 500 caracteres.</p>
                        <?php $__errorArgs = ['tags'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label" for="privacy_status">Privacidade</label>
                            <select id="privacy_status" name="privacy_status" class="form-control">
                                <option value="public"   <?php if(old('privacy_status', 'public') === 'public'): echo 'selected'; endif; ?>>Público</option>
                                <option value="unlisted" <?php if(old('privacy_status') === 'unlisted'): echo 'selected'; endif; ?>>Não listado</option>
                                <option value="private"  <?php if(old('privacy_status') === 'private'): echo 'selected'; endif; ?>>Privado</option>
                            </select>
                            <?php $__errorArgs = ['privacy_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="form-label" for="category_id">Categoria</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <?php
                                    $cats = [
                                        '22' => 'Pessoas e blogs',
                                        '24' => 'Entretenimento',
                                        '27' => 'Educação',
                                        '28' => 'Ciência e tecnologia',
                                        '10' => 'Música',
                                        '20' => 'Jogos',
                                        '23' => 'Comédia',
                                        '17' => 'Esportes',
                                        '26' => 'Estilo de vida',
                                        '25' => 'Notícias e política',
                                    ];
                                ?>
                                <?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>" <?php if(old('category_id', '22') === $id): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="made_for_kids" value="1" <?php if(old('made_for_kids')): echo 'checked'; endif; ?> class="mt-0.5">
                        <span class="text-xs text-foreground">
                            Este vídeo é feito para crianças (COPPA).
                            <span class="block text-[11px] text-muted-foreground">Marque apenas se o conteúdo é direcionado ao público infantil.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Quando publicar?</h2>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-border bg-background p-4 transition-colors hover:bg-accent">
                        <input type="radio" name="modo" value="agora" class="mt-1" <?php if(old('modo', 'agora') === 'agora'): echo 'checked'; endif; ?>>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Publicar agora</p>
                            <p class="mt-1 text-xs text-muted-foreground">O upload começa imediatamente.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-border bg-background p-4 transition-colors hover:bg-accent">
                        <input type="radio" name="modo" value="agendar" class="mt-1" <?php if(old('modo') === 'agendar'): echo 'checked'; endif; ?>>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Agendar</p>
                            <p class="mt-1 text-xs text-muted-foreground">O upload sobe agora, e o YouTube publica sozinho na hora marcada.</p>
                        </div>
                    </label>
                </div>

                <div id="bloco_agendar" class="mt-4 hidden">
                    <label class="form-label" for="scheduled_at">Data e hora</label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at" min="<?php echo e($minDate); ?>" value="<?php echo e(old('scheduled_at')); ?>" class="form-control">
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Mínimo de 5 minutos no futuro. O vídeo é enviado imediatamente como <span class="font-medium text-foreground">privado</span> e o próprio YouTube o torna público no horário escolhido — não depende do servidor estar ativo na hora marcada.
                    </p>
                    <?php $__errorArgs = ['scheduled_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i data-lucide="rocket" class="h-4 w-4"></i>
                Publicar
            </button>
        </form>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title">Boas práticas</h2>
                <ul class="mt-3 space-y-2 text-sm leading-relaxed text-muted-foreground">
                    <li>• Vídeos verticais 9:16 com no máximo 60 segundos viram Shorts automaticamente.</li>
                    <li>• Inclua <span class="font-medium text-foreground">#shorts</span> no título ou descrição.</li>
                    <li>• Title e description usam UTF-8; emojis são permitidos.</li>
                    <li>• Agendamentos exigem privacidade <span class="font-medium text-foreground">privada</span> até a hora marcada (a API trata isso automaticamente).</li>
                </ul>
            </div>

            <div class="card bg-accent/60">
                <h2 class="section-title">Limites da API</h2>
                <p class="mt-3 text-xs leading-relaxed text-muted-foreground">
                    A YouTube Data API tem cota diária de 10.000 unidades. Cada upload custa ~1.600 unidades —
                    ou seja, ~6 publicações por dia por projeto Google Cloud. O sistema também aplica limite
                    de 10 envios/hora por IP.
                </p>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const radiosOrigem = document.querySelectorAll('input[name="origem"]');
    const blocoExistente = document.getElementById('bloco_existente');
    const blocoUpload = document.getElementById('bloco_upload');

    function syncOrigem() {
        const valor = document.querySelector('input[name="origem"]:checked')?.value;
        blocoExistente?.classList.toggle('hidden', valor !== 'existente');
        blocoUpload?.classList.toggle('hidden', valor !== 'upload');
    }
    radiosOrigem.forEach(r => r.addEventListener('change', syncOrigem));
    syncOrigem();

    const radiosModo = document.querySelectorAll('input[name="modo"]');
    const blocoAgendar = document.getElementById('bloco_agendar');
    function syncModo() {
        const valor = document.querySelector('input[name="modo"]:checked')?.value;
        blocoAgendar?.classList.toggle('hidden', valor !== 'agendar');
    }
    radiosModo.forEach(r => r.addEventListener('change', syncModo));
    syncModo();

    const titleInput = document.getElementById('title');
    const titleCount = document.getElementById('titleCount');
    const descInput = document.getElementById('description');
    const descCount = document.getElementById('descCount');

    function refreshCounts() {
        if (titleInput && titleCount) titleCount.textContent = titleInput.value.length;
        if (descInput && descCount) descCount.textContent = descInput.value.length;
    }
    titleInput?.addEventListener('input', refreshCounts);
    descInput?.addEventListener('input', refreshCounts);
    refreshCounts();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/shorts/create.blade.php ENDPATH**/ ?>