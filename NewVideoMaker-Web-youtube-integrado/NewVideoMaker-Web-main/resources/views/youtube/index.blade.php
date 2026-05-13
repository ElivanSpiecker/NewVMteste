@extends('layouts.app')

@section('title', 'YouTube — NEW VideoMaker')
@section('description', 'Postagens automáticas e agendadas para o YouTube.')

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">YouTube</h1>
            <p class="mt-2 text-sm text-muted-foreground">Integração para publicar vídeos gerados automaticamente.</p>
        </div>

        @if ($connected)
            <span class="rounded-sm bg-primary px-3 py-2 font-display text-[10px] font-medium tracking-wider text-primary-foreground uppercase">Conta conectada</span>
        @else
            <a href="{{ route('youtube.connect') }}" class="btn-primary">
                <i data-lucide="youtube" class="h-4 w-4"></i>
                Conectar YouTube
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-sm border border-primary/30 bg-primary/10 p-4 text-sm text-foreground">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="mt-6 rounded-sm border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive">{{ session('error') }}</div>
    @endif

    <section class="card mt-8">
        <h2 class="section-title">Postagens agendadas</h2>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="border-b border-border text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="py-3 pr-4">Vídeo</th>
                        <th class="py-3 pr-4">Título</th>
                        <th class="py-3 pr-4">Agendamento</th>
                        <th class="py-3 pr-4">Privacidade</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($posts as $post)
                        <tr>
                            <td class="py-4 pr-4 text-muted-foreground">#{{ $post->video_id }}</td>
                            <td class="py-4 pr-4">
                                <div class="font-medium text-foreground">{{ $post->title }}</div>
                                @if ($post->youtube_video_id)
                                    <a href="https://www.youtube.com/watch?v={{ $post->youtube_video_id }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block text-xs text-muted-foreground underline hover:text-foreground">Abrir no YouTube</a>
                                @endif
                                @if ($post->error)
                                    <p class="mt-1 max-w-md text-xs text-destructive">{{ $post->error }}</p>
                                @endif
                            </td>
                            <td class="py-4 pr-4 text-muted-foreground">{{ $post->scheduled_at?->format('d/m/Y H:i') ?? 'Publicar quando o comando rodar' }}</td>
                            <td class="py-4 pr-4 text-muted-foreground">{{ $post->privacy_status }}</td>
                            <td class="py-4 pr-4">
                                <span class="rounded-sm bg-accent px-2 py-1 font-display text-[10px] tracking-wider text-muted-foreground uppercase">{{ $post->statusLabel() }}</span>
                            </td>
                            <td class="py-4 pr-4">
                                @if (in_array($post->status, ['scheduled', 'failed'], true))
                                    <form action="{{ route('youtube.publish-now', $post) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-outline text-xs">Publicar agora</button>
                                    </form>
                                @else
                                    <span class="text-xs text-muted-foreground">Sem ação</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-muted-foreground">Nenhuma postagem agendada ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
