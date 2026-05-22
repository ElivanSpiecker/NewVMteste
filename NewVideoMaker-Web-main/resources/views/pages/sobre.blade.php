@extends('layouts.app')

@section('title', __('SOBRE') . ' — NEW VideoMaker')
@section('description', __('Geração automatizada de vídeos com IA 100% local.'))

@section('content')
@php
    $cards = [
        [
            'icon' => 'target',
            'title' => __('O produto'),
            'content' => __('NEW VideoMaker transforma um texto simples em vídeo completo — com imagens, narração, trilha sonora e legenda — de forma totalmente automatizada. Tudo roda na sua máquina, sem APIs externas, sem custo por geração.'),
        ],
        [
            'icon' => 'layers',
            'title' => __('Stack de IA'),
            'content' => __('FLUX.1 Schnell para imagens cinematográficas, Kokoro TTS para narração natural, ACE-Step 1.5 para música original, Whisper para legendas sincronizadas, Gemma como diretor criativo via Ollama, MoviePy + FFmpeg para montagem final.'),
        ],
        [
            'icon' => 'sparkles',
            'title' => __('Diferenciais'),
            'items' => [
                __('Geração completa de vídeo a partir de um único texto'),
                __('Execução 100% local — sem nuvem, sem internet obrigatória'),
                __('Privacidade total: seus dados nunca saem da máquina'),
                __('Sem assinatura e sem cobrança por vídeo gerado'),
                __('LLM como agente de direção criativa autônoma'),
                __('Pipeline otimizado para GPUs de 8 GB de VRAM'),
            ],
        ],
        [
            'icon' => 'arrow-up-right',
            'title' => __('Roadmap'),
            'items' => [
                __('Suporte a múltiplos estilos visuais personalizados'),
                __('Integração com modelos de vídeo generativo'),
                __('Interface de edição de timeline'),
                __('Exportação em múltiplos formatos e resoluções'),
                __('Versão instalável para Windows com um clique'),
            ],
        ],
    ];
@endphp

<div class="min-h-screen p-8 lg:p-12">
    <div class="max-w-3xl">
        <h1 class="font-display text-5xl font-bold tracking-tight text-foreground lg:text-6xl">NEW VideoMaker</h1>
        <p class="mt-3 font-display text-lg text-muted-foreground">{{ __('Geração automatizada de vídeos com IA 100% local.') }}</p>
        <div class="mt-6 h-px w-24 bg-foreground"></div>
        <p class="mt-6 max-w-xl text-sm leading-relaxed text-muted-foreground">
            {{ __('Da ideia ao vídeo pronto em minutos. NEW VideoMaker usa uma cadeia de modelos de inteligência artificial rodando inteiramente na sua máquina para gerar roteiro, imagens, narração, música e legenda — tudo de forma autônoma, a partir de uma descrição em texto.') }}
        </p>
    </div>

    <div class="mt-12 grid gap-6 sm:grid-cols-2">
        @foreach ($cards as $card)
            <article class="rounded-sm border border-border bg-card p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4 text-foreground"></i>
                    <h2 class="font-display text-sm font-semibold tracking-wider text-foreground uppercase">{{ $card['title'] }}</h2>
                </div>

                @isset($card['content'])
                    <p class="mt-3 text-xs leading-relaxed text-muted-foreground">{{ $card['content'] }}</p>
                @endisset

                @isset($card['items'])
                    <ul class="mt-3 space-y-1.5">
                        @foreach ($card['items'] as $item)
                            <li class="flex items-start gap-2 text-xs text-muted-foreground">
                                <span class="mt-1.5 block h-1 w-1 shrink-0 rounded-full bg-foreground"></span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                @endisset
            </article>
        @endforeach
    </div>

    <div class="mt-12 flex flex-wrap items-center gap-4">
        <a href="{{ route('videos.create') }}" class="btn-primary">{{ __('Criar vídeo agora') }}</a>
        <a href="{{ route('pipeline') }}" class="btn-outline">{{ __('Ver pipeline técnico') }}</a>
    </div>
</div>
@endsection
