@extends('layouts.app')

@section('title', __('Configurações') . ' — NEW VideoMaker')
@section('description', __('Configure credenciais, pipeline e veja status dos serviços.'))

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">{{ __('Configurações') }}</h1>
        <p class="mt-2 text-sm text-muted-foreground">{{ __('Tudo que o aplicativo precisa para funcionar — sem editar arquivos no disco.') }}</p>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-sm border border-border bg-card px-4 py-3 text-sm text-foreground">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="h-4 w-4 text-chart-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="mt-10 max-w-4xl space-y-10">

        {{-- ===== Status geral ===== --}}
        @php
            $servicosOk = collect($services)->every(fn ($s) => $s['up']);
            $pipelineOk = $pipeline_status['ok'];
            $youtubeOk  = !empty($youtube['client_id']) && $youtube['client_secret_set'];
        @endphp

        <section class="card">
            <h2 class="section-title">{{ __('Estado geral') }}</h2>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach ([
                    ['label' => __('Serviços Python'),      'ok' => $servicosOk, 'href' => '#servicos'],
                    ['label' => __('Pipeline configurado'), 'ok' => $pipelineOk, 'href' => '#pipeline'],
                    ['label' => __('YouTube conectado'),    'ok' => $youtubeOk,  'href' => '#youtube'],
                ] as $item)
                    <a href="{{ $item['href'] }}" class="flex items-center gap-3 rounded-sm border border-border bg-background p-3 transition-colors hover:bg-accent">
                        <span class="h-2.5 w-2.5 rounded-full {{ $item['ok'] ? 'bg-chart-2' : 'bg-destructive' }}"></span>
                        <span class="text-sm text-foreground">{{ $item['label'] }}</span>
                        <span class="ml-auto text-[10px] uppercase tracking-wider text-muted-foreground">{{ $item['ok'] ? 'OK' : __('Pendente') }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ===== Pipeline Python ===== --}}
        <section id="pipeline" class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title">{{ __('Pipeline Python') }}</h2>
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('Caminhos para o Python do venv, o pipeline.py e a pasta de saída.') }}</p>
                </div>
                <span class="rounded-sm px-2 py-1 text-[10px] uppercase tracking-wider {{ $pipeline_status['ok'] ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground' }}">
                    {{ $pipeline_status['ok'] ? __('Configurado') : __('Pendente') }}
                </span>
            </div>

            <form method="POST" action="{{ route('config.pipeline') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="form-label" for="python_path">{{ __('Python (executável do venv)') }}</label>
                    <input id="python_path" type="text" name="python_path"
                           value="{{ old('python_path', $videogen['python_path']) }}"
                           placeholder="C:\caminho\NewVideoMaker\.venv\Scripts\python.exe"
                           class="form-control font-mono text-xs">
                    @if ($pipeline_status['python']['reason'])
                        <p class="mt-1 text-xs text-destructive">{{ $pipeline_status['python']['reason'] }}</p>
                    @endif
                    @error('python_path') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="pipeline_path">pipeline.py</label>
                    <input id="pipeline_path" type="text" name="pipeline_path"
                           value="{{ old('pipeline_path', $videogen['pipeline_path']) }}"
                           placeholder="C:\caminho\NewVideoMaker\pipeline.py"
                           class="form-control font-mono text-xs">
                    @if ($pipeline_status['pipeline']['reason'])
                        <p class="mt-1 text-xs text-destructive">{{ $pipeline_status['pipeline']['reason'] }}</p>
                    @endif
                    @error('pipeline_path') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="output_dir">{{ __('Pasta de saída') }}</label>
                    <input id="output_dir" type="text" name="output_dir"
                           value="{{ old('output_dir', $videogen['output_dir']) }}"
                           placeholder="C:\caminho\NewVideoMaker\output"
                           class="form-control font-mono text-xs">
                    @if ($pipeline_status['output_dir']['reason'])
                        <p class="mt-1 text-xs text-destructive">{{ $pipeline_status['output_dir']['reason'] }}</p>
                    @endif
                    @error('output_dir') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    {{ __('Salvar pipeline') }}
                </button>
            </form>
        </section>

        {{-- ===== Serviços ===== --}}
        <section id="servicos" class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title">{{ __('Serviços locais') }}</h2>
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('ComfyUI e Ollama precisam estar rodando para gerar vídeos.') }}</p>
                </div>
                <a href="{{ route('health.index') }}" class="btn-outline">{{ __('Detalhes') }}</a>
            </div>

            <div class="mt-5 space-y-3">
                @foreach ($services as $svc)
                    <div class="flex items-start justify-between gap-3 border-b border-border pb-3 last:border-0">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $svc['up'] ? 'bg-chart-2' : 'bg-destructive animate-pulse' }}"></span>
                            <div>
                                <p class="text-sm text-foreground">{{ $svc['name'] }}</p>
                                <p class="text-[11px] text-muted-foreground">{{ $svc['host'] }}:{{ $svc['port'] }}</p>
                                @if (!empty($svc['detail']))
                                    <p class="mt-1 text-[11px] text-destructive">{{ $svc['detail'] }}</p>
                                @endif
                            </div>
                        </div>
                        @if (!$svc['up'])
                            <a href="{{ $svc['install'] }}" target="_blank" rel="noopener" class="text-[11px] text-muted-foreground underline hover:text-foreground">{{ __('Como instalar') }} ↗</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== YouTube ===== --}}
        <section id="youtube" class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title">{{ __('Credenciais do YouTube (OAuth 2.0)') }}</h2>
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('Obrigatório para publicar e agendar Shorts.') }}</p>
                </div>
                <span class="rounded-sm px-2 py-1 text-[10px] uppercase tracking-wider {{ $youtubeOk ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground' }}">
                    {{ $youtubeOk ? __('Configurado') : __('Pendente') }}
                </span>
            </div>

            <details class="mt-4 rounded-sm border border-border bg-background p-4 text-xs">
                <summary class="cursor-pointer font-display text-xs uppercase tracking-wider text-foreground">{{ __('Como obter Client ID e Client Secret') }}</summary>
                <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-muted-foreground">
                    <li>{{ __('Acesse') }} <a href="https://console.cloud.google.com/" target="_blank" rel="noopener" class="underline hover:text-foreground">console.cloud.google.com</a> {{ __('e crie (ou abra) um projeto.') }}</li>
                    <li>{{ __('Em') }} <strong>APIs e serviços → Biblioteca</strong>, {{ __('busque e habilite') }} "<strong>YouTube Data API v3</strong>".</li>
                    <li>{{ __('Vá em') }} <strong>APIs e serviços → Tela de consentimento OAuth</strong> {{ __('e configure como') }} <em>External</em>.</li>
                    <li>{{ __('Em') }} <strong>Credenciais → Criar credenciais → ID do cliente OAuth</strong>, {{ __('escolha tipo') }} <em>Aplicativo da Web</em>.</li>
                    <li>{{ __('Em') }} <em>URIs de redirecionamento autorizados</em>, {{ __('cole exatamente:') }}
                        <code class="mt-1 block break-all rounded-sm bg-accent px-2 py-1 text-foreground">{{ $youtube['redirect_uri'] }}</code>
                    </li>
                    <li>{{ __('Copie o') }} <strong>Client ID</strong> {{ __('e') }} <strong>Client Secret</strong> {{ __('e cole abaixo.') }}</li>
                </ol>
            </details>

            <form method="POST" action="{{ route('config.youtube') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="form-label" for="client_id">Client ID</label>
                    <input id="client_id" type="text" name="client_id"
                           value="{{ old('client_id', $youtube['client_id']) }}"
                           placeholder="123456789-abc...apps.googleusercontent.com"
                           class="form-control font-mono text-xs">
                    @error('client_id') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="client_secret">
                        Client Secret
                        @if ($youtube['client_secret_set'])
                            <span class="ml-1 normal-case tracking-normal text-chart-2">· {{ __('salvo') }}</span>
                        @endif
                    </label>
                    <input id="client_secret" type="password" name="client_secret"
                           placeholder="{{ $youtube['client_secret_set'] ? '•••••••••••••• (deixe em branco para manter)' : 'GOCSPX-...' }}"
                           class="form-control font-mono text-xs"
                           autocomplete="new-password">
                    @error('client_secret') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="redirect_uri">Redirect URI</label>
                    <input id="redirect_uri" type="url" name="redirect_uri"
                           value="{{ old('redirect_uri', $youtube['redirect_uri']) }}"
                           class="form-control font-mono text-xs">
                    <p class="mt-1 text-[11px] text-muted-foreground">{{ __('Esta URL precisa estar cadastrada no Google Cloud Console como redirect autorizada.') }}</p>
                    @error('redirect_uri') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        {{ __('Salvar credenciais') }}
                    </button>
                    @if ($youtubeOk)
                        <a href="{{ route('shorts.connect') }}" class="btn-outline">
                            <i data-lucide="link" class="h-4 w-4"></i>
                            {{ __('Conectar canal') }}
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- ===== Preferências ===== --}}
        <section class="card">
            <h2 class="section-title">{{ __('Preferências') }}</h2>
            <div class="mt-4 space-y-4">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <span class="text-sm text-foreground">{{ __('Idioma da interface') }}</span>
                    <form id="localeForm" action="{{ route('locale.switch') }}" method="POST" class="inline">
                        @csrf
                        <select name="locale" onchange="document.getElementById('localeForm').submit()" class="rounded-sm border border-border bg-background px-3 py-1.5 text-xs text-foreground outline-none focus:border-foreground">
                            <option value="pt-BR" @selected(app()->getLocale() === 'pt-BR')>{{ __('Português') }}</option>
                            <option value="en" @selected(app()->getLocale() === 'en')>{{ __('Inglês') }}</option>
                        </select>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
