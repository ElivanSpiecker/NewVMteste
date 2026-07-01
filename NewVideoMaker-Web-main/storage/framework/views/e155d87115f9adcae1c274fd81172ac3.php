<?php $__env->startSection('title', __('Configurações') . ' — NEW VideoMaker'); ?>
<?php $__env->startSection('description', __('Configure credenciais, pipeline e veja status dos serviços.')); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl"><?php echo e(__('Configurações')); ?></h1>
        <p class="mt-2 text-sm text-muted-foreground"><?php echo e(__('Tudo que o aplicativo precisa para funcionar — sem editar arquivos no disco.')); ?></p>
    </div>

    <?php if(session('success')): ?>
        <div class="mt-6 rounded-sm border border-border bg-card px-4 py-3 text-sm text-foreground">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="h-4 w-4 text-chart-2"></i>
                <?php echo e(session('success')); ?>

            </div>
        </div>
    <?php endif; ?>

    <div class="mt-10 max-w-4xl space-y-10">

        
        <?php
            $servicosOk = collect($services)->every(fn ($s) => $s['up']);
            $pipelineOk = $pipeline_status['ok'];
            $youtubeOk  = !empty($youtube['client_id']) && $youtube['client_secret_set'];
        ?>

        <section class="card">
            <h2 class="section-title"><?php echo e(__('Estado geral')); ?></h2>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <?php $__currentLoopData = [
                    ['label' => __('Serviços Python'),      'ok' => $servicosOk, 'href' => '#servicos'],
                    ['label' => __('Pipeline configurado'), 'ok' => $pipelineOk, 'href' => '#pipeline'],
                    ['label' => __('YouTube conectado'),    'ok' => $youtubeOk,  'href' => '#youtube'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($item['href']); ?>" class="flex items-center gap-3 rounded-sm border border-border bg-background p-3 transition-colors hover:bg-accent">
                        <span class="h-2.5 w-2.5 rounded-full <?php echo e($item['ok'] ? 'bg-chart-2' : 'bg-destructive'); ?>"></span>
                        <span class="text-sm text-foreground"><?php echo e($item['label']); ?></span>
                        <span class="ml-auto text-[10px] uppercase tracking-wider text-muted-foreground"><?php echo e($item['ok'] ? 'OK' : __('Pendente')); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section id="pipeline" class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title"><?php echo e(__('Pipeline Python')); ?></h2>
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('Caminhos para o Python do venv, o pipeline.py e a pasta de saída.')); ?></p>
                </div>
                <span class="rounded-sm px-2 py-1 text-[10px] uppercase tracking-wider <?php echo e($pipeline_status['ok'] ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'); ?>">
                    <?php echo e($pipeline_status['ok'] ? __('Configurado') : __('Pendente')); ?>

                </span>
            </div>

            <form method="POST" action="<?php echo e(route('config.pipeline')); ?>" class="mt-5 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="form-label" for="python_path"><?php echo e(__('Python (executável do venv)')); ?></label>
                    <input id="python_path" type="text" name="python_path"
                           value="<?php echo e(old('python_path', $videogen['python_path'])); ?>"
                           placeholder="C:\caminho\NewVideoMaker\.venv\Scripts\python.exe"
                           class="form-control font-mono text-xs">
                    <?php if($pipeline_status['python']['reason']): ?>
                        <p class="mt-1 text-xs text-destructive"><?php echo e($pipeline_status['python']['reason']); ?></p>
                    <?php endif; ?>
                    <?php $__errorArgs = ['python_path'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="form-label" for="pipeline_path">pipeline.py</label>
                    <input id="pipeline_path" type="text" name="pipeline_path"
                           value="<?php echo e(old('pipeline_path', $videogen['pipeline_path'])); ?>"
                           placeholder="C:\caminho\NewVideoMaker\pipeline.py"
                           class="form-control font-mono text-xs">
                    <?php if($pipeline_status['pipeline']['reason']): ?>
                        <p class="mt-1 text-xs text-destructive"><?php echo e($pipeline_status['pipeline']['reason']); ?></p>
                    <?php endif; ?>
                    <?php $__errorArgs = ['pipeline_path'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="form-label" for="output_dir"><?php echo e(__('Pasta de saída')); ?></label>
                    <input id="output_dir" type="text" name="output_dir"
                           value="<?php echo e(old('output_dir', $videogen['output_dir'])); ?>"
                           placeholder="C:\caminho\NewVideoMaker\output"
                           class="form-control font-mono text-xs">
                    <?php if($pipeline_status['output_dir']['reason']): ?>
                        <p class="mt-1 text-xs text-destructive"><?php echo e($pipeline_status['output_dir']['reason']); ?></p>
                    <?php endif; ?>
                    <?php $__errorArgs = ['output_dir'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    <?php echo e(__('Salvar pipeline')); ?>

                </button>
            </form>
        </section>

        
        <section id="servicos" class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title"><?php echo e(__('Serviços locais')); ?></h2>
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('ComfyUI e Ollama precisam estar rodando para gerar vídeos.')); ?></p>
                </div>
                <a href="<?php echo e(route('health.index')); ?>" class="btn-outline"><?php echo e(__('Detalhes')); ?></a>
            </div>

            <div class="mt-5 space-y-3">
                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-start justify-between gap-3 border-b border-border pb-3 last:border-0">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full <?php echo e($svc['up'] ? 'bg-chart-2' : 'bg-destructive animate-pulse'); ?>"></span>
                            <div>
                                <p class="text-sm text-foreground"><?php echo e($svc['name']); ?></p>
                                <p class="text-[11px] text-muted-foreground"><?php echo e($svc['host']); ?>:<?php echo e($svc['port']); ?></p>
                                <?php if(!empty($svc['detail'])): ?>
                                    <p class="mt-1 text-[11px] text-destructive"><?php echo e($svc['detail']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if(!$svc['up']): ?>
                            <a href="<?php echo e($svc['install']); ?>" target="_blank" rel="noopener" class="text-[11px] text-muted-foreground underline hover:text-foreground"><?php echo e(__('Como instalar')); ?> ↗</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        
        <section id="youtube" class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title"><?php echo e(__('Credenciais do YouTube (OAuth 2.0)')); ?></h2>
                    <p class="mt-1 text-xs text-muted-foreground"><?php echo e(__('Obrigatório para publicar e agendar Shorts.')); ?></p>
                </div>
                <span class="rounded-sm px-2 py-1 text-[10px] uppercase tracking-wider <?php echo e($youtubeOk ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'); ?>">
                    <?php echo e($youtubeOk ? __('Configurado') : __('Pendente')); ?>

                </span>
            </div>

            <details class="mt-4 rounded-sm border border-border bg-background p-4 text-xs">
                <summary class="cursor-pointer font-display text-xs uppercase tracking-wider text-foreground"><?php echo e(__('Como obter Client ID e Client Secret')); ?></summary>
                <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-muted-foreground">
                    <li><?php echo e(__('Acesse')); ?> <a href="https://console.cloud.google.com/" target="_blank" rel="noopener" class="underline hover:text-foreground">console.cloud.google.com</a> <?php echo e(__('e crie (ou abra) um projeto.')); ?></li>
                    <li><?php echo e(__('Em')); ?> <strong>APIs e serviços → Biblioteca</strong>, <?php echo e(__('busque e habilite')); ?> "<strong>YouTube Data API v3</strong>".</li>
                    <li><?php echo e(__('Vá em')); ?> <strong>APIs e serviços → Tela de consentimento OAuth</strong> <?php echo e(__('e configure como')); ?> <em>External</em>.</li>
                    <li><?php echo e(__('Em')); ?> <strong>Credenciais → Criar credenciais → ID do cliente OAuth</strong>, <?php echo e(__('escolha tipo')); ?> <em>Aplicativo da Web</em>.</li>
                    <li><?php echo e(__('Em')); ?> <em>URIs de redirecionamento autorizados</em>, <?php echo e(__('cole exatamente:')); ?>

                        <code class="mt-1 block break-all rounded-sm bg-accent px-2 py-1 text-foreground"><?php echo e($youtube['redirect_uri']); ?></code>
                    </li>
                    <li><?php echo e(__('Copie o')); ?> <strong>Client ID</strong> <?php echo e(__('e')); ?> <strong>Client Secret</strong> <?php echo e(__('e cole abaixo.')); ?></li>
                </ol>
            </details>

            <form method="POST" action="<?php echo e(route('config.youtube')); ?>" class="mt-5 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="form-label" for="client_id">Client ID</label>
                    <input id="client_id" type="text" name="client_id"
                           value="<?php echo e(old('client_id', $youtube['client_id'])); ?>"
                           placeholder="123456789-abc...apps.googleusercontent.com"
                           class="form-control font-mono text-xs">
                    <?php $__errorArgs = ['client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="form-label" for="client_secret">
                        Client Secret
                        <?php if($youtube['client_secret_set']): ?>
                            <span class="ml-1 normal-case tracking-normal text-chart-2">· <?php echo e(__('salvo')); ?></span>
                        <?php endif; ?>
                    </label>
                    <input id="client_secret" type="password" name="client_secret"
                           placeholder="<?php echo e($youtube['client_secret_set'] ? '•••••••••••••• (deixe em branco para manter)' : 'GOCSPX-...'); ?>"
                           class="form-control font-mono text-xs"
                           autocomplete="new-password">
                    <?php $__errorArgs = ['client_secret'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="form-label" for="redirect_uri">Redirect URI</label>
                    <input id="redirect_uri" type="url" name="redirect_uri"
                           value="<?php echo e(old('redirect_uri', $youtube['redirect_uri'])); ?>"
                           class="form-control font-mono text-xs">
                    <p class="mt-1 text-[11px] text-muted-foreground"><?php echo e(__('Esta URL precisa estar cadastrada no Google Cloud Console como redirect autorizada.')); ?></p>
                    <?php $__errorArgs = ['redirect_uri'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-destructive"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        <?php echo e(__('Salvar credenciais')); ?>

                    </button>
                    <?php if($youtubeOk): ?>
                        <a href="<?php echo e(route('shorts.connect')); ?>" class="btn-outline">
                            <i data-lucide="link" class="h-4 w-4"></i>
                            <?php echo e(__('Conectar canal')); ?>

                        </a>
                    <?php endif; ?>
                </div>
            </form>
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
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nicol\PycharmProjects\NewVMteste\NewVideoMaker-Web-main\resources\views/pages/config.blade.php ENDPATH**/ ?>