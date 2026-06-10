@extends('layouts.app')

@section('title', __('Vídeo pronto') . ' — NEW VideoMaker')
@section('description', __('Vídeo pronto'))

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="mx-auto max-w-3xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
            <i data-lucide="check" class="h-8 w-8"></i>
        </div>
        <h1 class="mt-6 font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">{{ __('Vídeo pronto') }}</h1>
        <p class="mt-3 text-sm text-muted-foreground">{{ __('Tema:') }} <span class="font-medium text-foreground">{{ $video->tema }}</span> · {{ $video->duracao }}s · {{ $video->idioma ?? 'PT-BR' }}</p>

        <div class="mt-10 rounded-sm border border-border bg-card p-8">
            <div class="aspect-video overflow-hidden rounded-sm bg-accent">
                @if ($video->video_path && file_exists($video->video_path))
                    <video controls class="h-full w-full bg-black">
                        <source src="{{ route('videos.download', $video) }}" type="video/mp4">
                    </video>
                @else
                    <div class="flex h-full items-center justify-center text-sm text-muted-foreground">{{ __('Arquivo de vídeo não encontrado no caminho configurado.') }}</div>
                @endif
            </div>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('videos.download', $video) }}" class="btn-primary">
                    <i data-lucide="download" class="h-4 w-4"></i>
                    {{ __('Baixar MP4') }}
                </a>
                <a href="{{ route('videos.download-srt', $video) }}" class="btn-outline">
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    {{ __('Baixar legenda') }}
                </a>
                <button type="button" onclick="document.getElementById('regen-panel').classList.toggle('hidden')" class="btn-outline">
                    <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                    {{ __('Regenerar') }}
                </button>
                <a href="{{ route('videos.index') }}" class="btn-outline">{{ __('Voltar') }}</a>
            </div>
        </div>

        @php
            $hasImagens  = !empty($video->imagens_paths);
            $hasNarracao = $video->narracao_path && file_exists($video->narracao_path);
            $hasMusica   = $video->musica_path   && file_exists($video->musica_path);
            $voicesByLang = \App\Http\Controllers\VoiceController::VOICES;
            $idiomaAtual = $video->idioma ?? 'PT-BR';
        @endphp

        <div id="regen-panel" class="hidden mt-6 rounded-sm border border-border bg-card p-8 text-left">
            <h2 class="font-display text-2xl font-semibold text-foreground">{{ __('Regenerar vídeo') }}</h2>
            <p class="mt-1 text-sm text-muted-foreground">{{ __('Reaproveite o que já foi gerado e regere apenas o que quiser mudar.') }}</p>

            <form method="POST" action="{{ route('videos.regenerate', $video) }}" class="mt-6 space-y-6">
                @csrf

                <div>
                    <h3 class="text-sm font-medium text-foreground">{{ __('Etapas a manter') }}</h3>
                    <p class="mt-1 text-xs text-muted-foreground">{{ __('O que ficar desmarcado será regerado.') }}</p>

                    <div class="mt-3 space-y-2">
                        @if ($hasImagens)
                            <label class="flex items-center gap-2 text-sm text-foreground">
                                <input type="checkbox" name="keep[]" value="imagens" checked class="h-4 w-4 rounded border-border">
                                {{ __('Manter imagens') }} ({{ count($video->imagens_paths) }})
                            </label>
                        @endif
                        @if ($hasNarracao)
                            <label class="flex items-center gap-2 text-sm text-foreground">
                                <input type="checkbox" name="keep[]" value="narracao" class="h-4 w-4 rounded border-border">
                                {{ __('Manter narração') }}
                            </label>
                        @endif
                        @if ($hasMusica)
                            <label class="flex items-center gap-2 text-sm text-foreground">
                                <input type="checkbox" name="keep[]" value="musica" checked class="h-4 w-4 rounded border-border">
                                {{ __('Manter música') }}
                            </label>
                        @endif
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-foreground">{{ __('Idioma') }}</label>
                        <select name="idioma" class="mt-2 w-full rounded-sm border border-border bg-background px-3 py-2 text-sm">
                            <option value="PT-BR" @selected($idiomaAtual === 'PT-BR')>{{ __('Português') }}</option>
                            <option value="EN-US" @selected($idiomaAtual === 'EN-US')>{{ __('Inglês') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-foreground">{{ __('Voz da narração') }}</label>
                        <select name="voz" class="mt-2 w-full rounded-sm border border-border bg-background px-3 py-2 text-sm">
                            <option value="">{{ __('Padrão do idioma') }}</option>
                            @foreach ($voicesByLang as $lang => $voices)
                                <optgroup label="{{ $lang }}">
                                    @foreach ($voices as $v)
                                        <option value="{{ $v['id'] }}" @selected($video->voz === $v['id'])>
                                            {{ $v['name'] }} ({{ __($v['gender']) }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-sm border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">
                    {{ __('Ao trocar a voz ou o idioma, a narração e as legendas serão regeradas mesmo se estiverem marcadas para manter.') }}
                </div>

                <button type="submit" class="btn-primary">
                    <i data-lucide="play" class="h-4 w-4"></i>
                    {{ __('Gerar novo vídeo') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
