@extends('layouts.app')

@section('title', 'Sobre o Projeto — NEW VideoMaker')
@section('description', 'Plataforma de geração autônoma de vídeos com IA local — TCC em Engenharia de Software.')

@section('content')
@php
    $cards = [
        [
            'icon' => 'target',
            'title' => 'Objetivo',
            'content' => 'Desenvolver uma plataforma capaz de gerar vídeos curtos de forma completamente automatizada, utilizando exclusivamente modelos de inteligência artificial executados localmente, sem dependência de serviços em nuvem ou APIs externas.',
        ],
        [
            'icon' => 'layers',
            'title' => 'Tecnologias',
            'content' => 'FLUX.1 Schnell para imagens, Kokoro TTS para narração, ACE-Step 1.5 para música, Whisper para legendas, Gemma/Ollama como LLM, FFmpeg/MoviePy para montagem, Laravel para orquestração do pipeline.',
        ],
        [
            'icon' => 'sparkles',
            'title' => 'Diferenciais',
            'items' => ['Geração automatizada de vídeos completos', 'Execução totalmente local e offline', 'Privacidade total dos dados', 'Sem custos por geração', 'IA multimodal integrada', 'LLM como agente de direção criativa'],
        ],
        [
            'icon' => 'arrow-up-right',
            'title' => 'Próximas etapas',
            'items' => ['Suporte a múltiplos estilos visuais personalizados', 'Integração com modelos de vídeo generativo', 'Interface de edição de timeline', 'Exportação em múltiplos formatos e resoluções', 'Benchmark comparativo com plataformas comerciais'],
        ],
    ];
@endphp

<div class="min-h-screen p-8 lg:p-12">
    <div class="max-w-3xl">
        <h1 class="font-display text-5xl font-bold tracking-tight text-foreground lg:text-6xl">NEW VideoMaker</h1>
        <p class="mt-3 font-display text-lg text-muted-foreground">Plataforma de geração autônoma de vídeos com modelos de inteligência artificial locais.</p>
        <div class="mt-6 h-px w-24 bg-foreground"></div>
        <p class="mt-6 max-w-xl text-sm leading-relaxed text-muted-foreground">
            Este projeto é desenvolvido como Trabalho de Conclusão de Curso (TCC) no curso de Engenharia de Software. A plataforma propõe uma abordagem inovadora para a criação de conteúdo audiovisual, onde todas as etapas — do roteiro ao vídeo final — são executadas por modelos de IA rodando localmente, garantindo privacidade, autonomia e custo zero.
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
</div>
@endsection
