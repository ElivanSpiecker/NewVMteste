@extends('layouts.app')

@section('title', 'Publicar no YouTube — NEW VideoMaker')
@section('description', 'Envie ou agende um vídeo curto para o YouTube Shorts.')

@section('content')
@php
    $minDate = now()->addMinutes(10)->format('Y-m-d\TH:i');
@endphp

<div class="min-h-screen p-8 lg:p-12">
    <div class="animate-[fadeIn_.45s_ease-out]">
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Publicar no YouTube</h1>
        <p class="mt-2 text-sm text-muted-foreground">Use um vídeo já gerado pelo pipeline ou faça upload manual.</p>
    </div>

    @if (session('error'))
        <div class="mt-6 rounded-sm border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm text-destructive">
            {{ session('error') }}
        </div>
    @endif

    @if ($accounts->isEmpty())
        <div class="mt-10 rounded-sm border border-dashed border-border p-12 text-center">
            <i data-lucide="youtube" class="mx-auto h-12 w-12 text-muted-foreground"></i>
            <p class="mt-4 text-sm text-foreground">Nenhum canal conectado.</p>
            <p class="mt-1 text-xs text-muted-foreground">Conecte um canal do YouTube antes de publicar.</p>
            <a href="{{ route('shorts.connect') }}" class="btn-primary mt-6">
                <i data-lucide="link" class="h-4 w-4"></i>
                Conectar canal
            </a>
        </div>
    @else
    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_360px]">
        <form action="{{ route('shorts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="card">
                <h2 class="section-title">Canal de destino</h2>
                <div class="mt-3">
                    <label class="form-label" for="youtube_account_id">Canal</label>
                    <select id="youtube_account_id" name="youtube_account_id" class="form-control">
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('youtube_account_id') == $account->id)>
                                {{ $account->display_name }} {{ $account->channel_id ? '(' . $account->channel_id . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('youtube_account_id') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Origem do vídeo</h2>

                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-border bg-background p-4 transition-colors hover:bg-accent">
                        <input type="radio" name="origem" value="existente" class="mt-1" @checked(old('origem', 'existente') === 'existente')>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Usar vídeo gerado</p>
                            <p class="mt-1 text-xs text-muted-foreground">Selecione um vídeo já produzido pelo pipeline.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-border bg-background p-4 transition-colors hover:bg-accent">
                        <input type="radio" name="origem" value="upload" class="mt-1" @checked(old('origem') === 'upload')>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Enviar arquivo</p>
                            <p class="mt-1 text-xs text-muted-foreground">MP4, MOV, AVI, WMV, MPEG, WEBM, MKV, FLV ou 3GP. Até 256 MB.</p>
                        </div>
                    </label>
                </div>

                <div id="bloco_existente" class="mt-5">
                    <label class="form-label" for="video_id">Vídeo gerado</label>
                    @if ($videosProntos->isEmpty())
                        <p class="rounded-sm border border-dashed border-border px-3 py-4 text-xs text-muted-foreground">
                            Nenhum vídeo gerado ainda. Crie um pelo menu CRIAR primeiro.
                        </p>
                    @else
                        <select id="video_id" name="video_id" class="form-control">
                            <option value="">— selecione —</option>
                            @foreach ($videosProntos as $video)
                                <option value="{{ $video->id }}" @selected(old('video_id') == $video->id)>
                                    #{{ $video->id }} · {{ $video->tema }} · {{ $video->duracao }}s
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('video_id') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div id="bloco_upload" class="mt-5 hidden">
                    <label class="form-label" for="video">Arquivo de vídeo</label>
                    <input id="video" type="file" name="video" accept=".mp4,.mov,.avi,.wmv,.mpeg,.mpg,.webm,.mkv,.flv,.3gp,video/*" class="form-control">
                    <p class="mt-1 text-xs text-muted-foreground">YouTube recomenda formato vertical 9:16 e duração ≤ 60 s para Shorts.</p>
                    @error('video') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Metadados</h2>

                <div class="mt-4 space-y-5">
                    <div>
                        <label class="form-label" for="title">Título *</label>
                        <input id="title" type="text" name="title" maxlength="100" required value="{{ old('title') }}" class="form-control" placeholder="Ex: Café artesanal em 60 segundos #shorts">
                        <p class="mt-1 text-[11px] text-muted-foreground"><span id="titleCount">0</span> / 100</p>
                        @error('title') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="description">Descrição</label>
                        <textarea id="description" name="description" maxlength="5000" rows="5" class="form-control resize-none" placeholder="Conte sobre o vídeo, links e hashtags...">{{ old('description') }}</textarea>
                        <p class="mt-1 text-[11px] text-muted-foreground"><span id="descCount">0</span> / 5000</p>
                        @error('description') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label" for="tags">Tags</label>
                        <input id="tags" type="text" name="tags" maxlength="500" value="{{ old('tags') }}" class="form-control" placeholder="cafe, especial, brasil">
                        <p class="mt-1 text-[11px] text-muted-foreground">Separe por vírgula. Limite total: 500 caracteres.</p>
                        @error('tags') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label" for="privacy_status">Privacidade</label>
                            <select id="privacy_status" name="privacy_status" class="form-control">
                                <option value="public"   @selected(old('privacy_status', 'public') === 'public')>Público</option>
                                <option value="unlisted" @selected(old('privacy_status') === 'unlisted')>Não listado</option>
                                <option value="private"  @selected(old('privacy_status') === 'private')>Privado</option>
                            </select>
                            @error('privacy_status') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label" for="category_id">Categoria</label>
                            <select id="category_id" name="category_id" class="form-control">
                                @php
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
                                @endphp
                                @foreach ($cats as $id => $label)
                                    <option value="{{ $id }}" @selected(old('category_id', '22') === $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="made_for_kids" value="1" @checked(old('made_for_kids')) class="mt-0.5">
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
                        <input type="radio" name="modo" value="agora" class="mt-1" @checked(old('modo', 'agora') === 'agora')>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Publicar agora</p>
                            <p class="mt-1 text-xs text-muted-foreground">O upload começa imediatamente.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-border bg-background p-4 transition-colors hover:bg-accent">
                        <input type="radio" name="modo" value="agendar" class="mt-1" @checked(old('modo') === 'agendar')>
                        <div>
                            <p class="font-display text-sm font-semibold text-foreground">Agendar</p>
                            <p class="mt-1 text-xs text-muted-foreground">O upload sobe agora, e o YouTube publica sozinho na hora marcada.</p>
                        </div>
                    </label>
                </div>

                <div id="bloco_agendar" class="mt-4 hidden">
                    <label class="form-label" for="scheduled_at">Data e hora</label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at" min="{{ $minDate }}" value="{{ old('scheduled_at') }}" class="form-control">
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Mínimo de 5 minutos no futuro. O vídeo é enviado imediatamente como <span class="font-medium text-foreground">privado</span> e o próprio YouTube o torna público no horário escolhido — não depende do servidor estar ativo na hora marcada.
                    </p>
                    @error('scheduled_at') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
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
    @endif
</div>
@endsection

@push('scripts')
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
@endpush
