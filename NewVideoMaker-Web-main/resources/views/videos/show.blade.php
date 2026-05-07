@extends('layouts.app')

@section('title', 'Vídeo Pronto — NEW VideoMaker')
@section('description', 'Download do vídeo gerado.')

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="mx-auto max-w-3xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
            <i data-lucide="check" class="h-8 w-8"></i>
        </div>
        <h1 class="mt-6 font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Vídeo pronto</h1>
        <p class="mt-3 text-sm text-muted-foreground">Tema: <span class="font-medium text-foreground">{{ $video->tema }}</span> · {{ $video->duracao }}s</p>

        <div class="mt-10 rounded-sm border border-border bg-card p-8">
            <div class="aspect-video overflow-hidden rounded-sm bg-accent">
                @if ($video->video_path && file_exists($video->video_path))
                    <video controls class="h-full w-full bg-black">
                        <source src="{{ route('videos.download', $video) }}" type="video/mp4">
                    </video>
                @else
                    <div class="flex h-full items-center justify-center text-sm text-muted-foreground">Arquivo de vídeo não encontrado no caminho configurado.</div>
                @endif
            </div>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('videos.download', $video) }}" class="btn-primary">
                    <i data-lucide="download" class="h-4 w-4"></i>
                    Baixar MP4
                </a>
                <a href="{{ route('videos.download-srt', $video) }}" class="btn-outline">
                    <i data-lucide="file-text" class="h-4 w-4"></i>
                    Baixar legenda
                </a>
                <a href="{{ route('videos.index') }}" class="btn-outline">Voltar</a>
            </div>
        </div>
    </div>
</div>
@endsection
