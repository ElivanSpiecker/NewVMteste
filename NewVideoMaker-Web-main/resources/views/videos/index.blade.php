@extends('layouts.app')

@section('title', __('Meus Vídeos') . ' — NEW VideoMaker')
@section('description', __('Todos os vídeos gerados na plataforma.'))

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">{{ __('Meus Vídeos') }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">{{ __('Todos os vídeos gerados na plataforma.') }}</p>
        </div>
        <a href="{{ route('videos.create') }}" class="btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            {{ __('Novo vídeo') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-sm border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-foreground">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-sm border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-8 flex flex-wrap items-center gap-3">
        @foreach ([__('Todos'), __('Concluídos'), __('Em processamento'), __('Com erro')] as $index => $filter)
            <button type="button" data-filter="{{ $filter }}" class="video-filter rounded-sm px-3 py-1.5 font-display text-[10px] font-medium tracking-wider uppercase transition-colors {{ $index === 0 ? 'bg-primary text-primary-foreground' : 'bg-accent text-accent-foreground hover:bg-primary hover:text-primary-foreground' }}">
                {{ $filter }}
            </button>
        @endforeach

        <div class="relative ml-auto">
            <i data-lucide="search" class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"></i>
            <input id="videoSearch" type="text" placeholder="{{ __('Buscar por tema...') }}" class="w-56 rounded-sm border border-border bg-card py-2 pl-9 pr-3 text-xs text-foreground outline-none transition-colors focus:border-foreground">
        </div>
    </div>

    <div id="videosGrid" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($videos as $video)
            @php
                $statusKey = $video->isDone() ? 'done' : ($video->hasFailed() ? 'failed' : 'processing');
                $statusClass = match ($statusKey) {
                    'done' => 'bg-primary text-primary-foreground',
                    'processing' => 'bg-accent text-accent-foreground',
                    default => 'bg-destructive text-destructive-foreground',
                };
                $filterKey = match ($statusKey) {
                    'done' => __('Concluídos'),
                    'processing' => __('Em processamento'),
                    default => __('Com erro'),
                };
                $thumbCandidate = $video->thumbnail_path
                    ?: (($video->imagens_paths[0] ?? null));
                $hasRealThumb = $thumbCandidate && file_exists($thumbCandidate);
                $thumb = $hasRealThumb
                    ? route('videos.thumbnail', $video)
                    : asset('assets/frame-' . (($loop->iteration - 1) % 6 + 1) . '.jpg');
            @endphp
            <article data-title="{{ strtolower($video->tema) }}" data-status="{{ $filterKey }}" class="video-card group overflow-hidden rounded-sm border border-border bg-card transition-transform duration-300 hover:-translate-y-1">
                <div class="relative aspect-[4/3] overflow-hidden bg-accent">
                    <img src="{{ $thumb }}" alt="{{ $video->tema }}" loading="lazy" class="h-full w-full object-cover transition-all duration-300 group-hover:scale-105 {{ $hasRealThumb ? '' : 'grayscale group-hover:grayscale-0' }}">
                    <div class="absolute right-2 top-2 rounded-sm px-2 py-0.5 font-display text-[10px] tracking-wider uppercase {{ $statusClass }}">
                        {{ $video->statusLabel() }}
                    </div>
                    @if (!$video->isDone() && !$video->hasFailed())
                        <div class="absolute bottom-0 left-0 h-1 bg-primary transition-all" style="width: {{ $video->progresso }}%"></div>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="truncate font-display text-sm font-semibold text-foreground">{{ $video->tema }}</h3>
                    <div class="mt-1 flex items-center gap-3 text-[11px] text-muted-foreground">
                        <span>{{ $video->created_at?->format('d/m/Y H:i') }}</span>
                        <span>•</span>
                        <span>{{ $video->duracao }}s</span>
                        <span>•</span>
                        <span>{{ $video->idioma ?? 'PT-BR' }}</span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <a href="{{ $video->isDone() ? route('videos.show', $video) : route('videos.status', $video) }}" class="flex items-center gap-1 rounded-sm border border-border px-2.5 py-1.5 text-[10px] font-medium text-foreground transition-colors hover:bg-accent"><i data-lucide="eye" class="h-3 w-3"></i> {{ __('Ver') }}</a>
                        @if ($video->isDone())
                            <a href="{{ route('videos.download', $video) }}" class="flex items-center gap-1 rounded-sm border border-border px-2.5 py-1.5 text-[10px] font-medium text-foreground transition-colors hover:bg-accent"><i data-lucide="download" class="h-3 w-3"></i> {{ __('Baixar') }}</a>
                            <a href="{{ route('videos.download-srt', $video) }}" class="flex items-center gap-1 rounded-sm border border-border px-2.5 py-1.5 text-[10px] font-medium text-foreground transition-colors hover:bg-accent"><i data-lucide="file-text" class="h-3 w-3"></i> SRT</a>
                        @endif
                        @unless ($video->isProcessing())
                            <form action="{{ route('videos.destroy', $video) }}" method="POST" class="ml-auto js-delete-video" data-tema="{{ $video->tema }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('Excluir vídeo') }}" class="flex items-center gap-1 rounded-sm border border-border px-2.5 py-1.5 text-[10px] font-medium text-muted-foreground transition-colors hover:border-destructive hover:bg-destructive hover:text-destructive-foreground">
                                    <i data-lucide="trash-2" class="h-3 w-3"></i>
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-sm border border-dashed border-border p-16 text-center">
                <i data-lucide="video" class="mx-auto h-10 w-10 text-muted-foreground"></i>
                <p class="mt-4 text-sm text-muted-foreground">{{ __('Nenhum vídeo gerado ainda.') }}</p>
                <a href="{{ route('videos.create') }}" class="btn-primary mt-6">{{ __('Gerar primeiro vídeo') }}</a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    const FILTER_ALL = @json(__('Todos'));
    const filterButtons = document.querySelectorAll('.video-filter');
    const searchInput = document.getElementById('videoSearch');
    const cards = document.querySelectorAll('.video-card');
    let activeFilter = FILTER_ALL;

    function applyFilters() {
        const query = (searchInput?.value || '').toLowerCase();
        cards.forEach((card) => {
            const matchTitle = card.dataset.title.includes(query);
            const matchStatus = activeFilter === FILTER_ALL || card.dataset.status === activeFilter;
            card.classList.toggle('hidden', !(matchTitle && matchStatus));
        });
    }

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.filter;
            filterButtons.forEach((item) => {
                item.className = 'video-filter rounded-sm bg-accent px-3 py-1.5 font-display text-[10px] font-medium tracking-wider text-accent-foreground uppercase transition-colors hover:bg-primary hover:text-primary-foreground';
            });
            button.className = 'video-filter rounded-sm bg-primary px-3 py-1.5 font-display text-[10px] font-medium tracking-wider text-primary-foreground uppercase transition-colors';
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    // Confirmação de exclusão
    const CONFIRM_DELETE = @json(__('Excluir o vídeo ":tema"? Esta ação não pode ser desfeita.'));
    document.querySelectorAll('form.js-delete-video').forEach((form) => {
        form.addEventListener('submit', (e) => {
            const tema = form.dataset.tema || '';
            const msg = CONFIRM_DELETE.replace(':tema', tema);
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
