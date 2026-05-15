@extends('layouts.app')

@section('title', 'YouTube Shorts — NEW VideoMaker')
@section('description', 'Agende e publique vídeos curtos diretamente no YouTube.')

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">YouTube Shorts</h1>
            <p class="mt-2 text-sm text-muted-foreground">Publique agora ou agende vídeos diretamente no seu canal.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($accounts->isNotEmpty())
                <a href="{{ route('shorts.create') }}" class="btn-primary">
                    <i data-lucide="upload" class="h-4 w-4"></i>
                    Novo Short
                </a>
            @endif
            <a href="{{ route('shorts.connect') }}" class="btn-outline">
                <i data-lucide="youtube" class="h-4 w-4"></i>
                Conectar canal
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-sm border border-border bg-card px-4 py-3 text-sm text-foreground">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="h-4 w-4 text-chart-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-sm border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm text-destructive">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-triangle" class="h-4 w-4"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <section class="mt-8">
        <h2 class="section-title">Canais conectados</h2>
        @if ($accounts->isEmpty())
            <div class="mt-3 rounded-sm border border-dashed border-border p-10 text-center">
                <i data-lucide="youtube" class="mx-auto h-10 w-10 text-muted-foreground"></i>
                <p class="mt-4 text-sm text-muted-foreground">Nenhum canal do YouTube conectado.</p>
                <a href="{{ route('shorts.connect') }}" class="btn-primary mt-6">
                    <i data-lucide="link" class="h-4 w-4"></i>
                    Conectar com OAuth do Google
                </a>
                <p class="mt-3 text-[11px] text-muted-foreground">Você será redirecionado para o consentimento da Google.</p>
            </div>
        @else
            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($accounts as $account)
                    <div class="card flex items-start justify-between gap-3">
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">{{ $account->display_name ?? 'Canal' }}</p>
                            <p class="mt-1 text-[11px] text-muted-foreground">ID: {{ $account->channel_id ?? '—' }}</p>
                            <p class="mt-2 text-[11px] text-muted-foreground">
                                Token expira:
                                {{ $account->expires_at ? $account->expires_at->format('d/m/Y H:i') : '—' }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('shorts.disconnect', $account) }}" onsubmit="return confirm('Desconectar este canal?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-sm border border-border px-2 py-1 text-[10px] uppercase tracking-wider text-muted-foreground transition-colors hover:bg-destructive hover:text-destructive-foreground hover:border-destructive">
                                Desconectar
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-10">
        <h2 class="section-title">Publicações</h2>
        @if ($uploads->isEmpty())
            <div class="mt-3 rounded-sm border border-dashed border-border p-10 text-center">
                <i data-lucide="video" class="mx-auto h-10 w-10 text-muted-foreground"></i>
                <p class="mt-4 text-sm text-muted-foreground">Nenhuma publicação registrada ainda.</p>
                @if ($accounts->isNotEmpty())
                    <a href="{{ route('shorts.create') }}" class="btn-primary mt-6">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        Publicar agora
                    </a>
                @endif
            </div>
        @else
            <div class="mt-3 overflow-hidden rounded-sm border border-border bg-card">
                <table class="w-full text-left text-xs">
                    <thead class="bg-accent text-[10px] uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">Título</th>
                            <th class="px-4 py-3">Canal</th>
                            <th class="px-4 py-3">Quando</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($uploads as $upload)
                            @php
                                $statusClass = match ($upload->status) {
                                    'published' => 'bg-primary text-primary-foreground',
                                    'scheduled' => 'bg-accent text-accent-foreground',
                                    'failed'    => 'bg-destructive text-destructive-foreground',
                                    default     => 'bg-accent text-accent-foreground',
                                };
                            @endphp
                            <tr class="border-t border-border">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-foreground">{{ $upload->title }}</p>
                                    @if ($upload->video)
                                        <p class="mt-0.5 text-[10px] text-muted-foreground">vídeo gerado: {{ $upload->video->tema }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $upload->account?->display_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    @if ($upload->scheduled_at)
                                        {{ $upload->scheduled_at->format('d/m/Y H:i') }}
                                    @else
                                        Imediato — {{ $upload->created_at?->format('d/m H:i') }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-sm px-2 py-0.5 font-display text-[10px] uppercase tracking-wider {{ $statusClass }}">{{ $upload->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('shorts.show', $upload) }}" class="rounded-sm border border-border px-2 py-1 text-[10px] uppercase tracking-wider transition-colors hover:bg-accent">Ver</a>
                                        @if ($upload->youtubeUrl())
                                            <a href="{{ $upload->youtubeUrl() }}" target="_blank" rel="noopener" class="rounded-sm border border-border px-2 py-1 text-[10px] uppercase tracking-wider transition-colors hover:bg-accent">YouTube</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
