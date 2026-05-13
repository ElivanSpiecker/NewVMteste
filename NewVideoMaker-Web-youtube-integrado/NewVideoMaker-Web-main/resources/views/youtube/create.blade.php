@extends('layouts.app')

@section('title', 'Agendar no YouTube — NEW VideoMaker')
@section('description', 'Agende ou publique um vídeo no YouTube.')

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div>
        <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Agendar no YouTube</h1>
        <p class="mt-2 text-sm text-muted-foreground">Vídeo: <span class="font-medium text-foreground">{{ $video->tema }}</span></p>
    </div>

    @if (!$connected)
        <div class="mt-6 rounded-sm border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive">
            Antes de agendar, conecte sua conta do YouTube.
            <a href="{{ route('youtube.connect') }}" class="font-semibold underline">Conectar agora</a>
        </div>
    @endif

    <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_360px]">
        <form action="{{ route('youtube.store', $video) }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="form-label" for="title">Título</label>
                <input id="title" type="text" name="title" value="{{ old('title', $video->tema) }}" maxlength="100" required class="form-control">
                @error('title') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label" for="description">Descrição</label>
                <textarea id="description" name="description" rows="5" maxlength="5000" class="form-control" placeholder="Descrição do vídeo...">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label" for="tags">Tags</label>
                <input id="tags" type="text" name="tags" value="{{ old('tags') }}" maxlength="500" class="form-control" placeholder="ia, video, shorts">
                <p class="mt-1 text-xs text-muted-foreground">Separe as tags por vírgula.</p>
                @error('tags') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="form-label" for="category_id">Categoria</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="22" @selected(old('category_id', '22') === '22')>Pessoas e blogs</option>
                        <option value="24" @selected(old('category_id') === '24')>Entretenimento</option>
                        <option value="28" @selected(old('category_id') === '28')>Ciência e tecnologia</option>
                        <option value="10" @selected(old('category_id') === '10')>Música</option>
                        <option value="17" @selected(old('category_id') === '17')>Esportes</option>
                    </select>
                    @error('category_id') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="privacy_status">Privacidade</label>
                    <select id="privacy_status" name="privacy_status" class="form-control">
                        <option value="private" @selected(old('privacy_status', 'private') === 'private')>Privado</option>
                        <option value="unlisted" @selected(old('privacy_status') === 'unlisted')>Não listado</option>
                        <option value="public" @selected(old('privacy_status') === 'public')>Público</option>
                    </select>
                    @error('privacy_status') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label" for="scheduled_at">Data e horário</label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="form-control">
                    @error('scheduled_at') <p class="mt-1 text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit" class="btn-primary w-full" @disabled(!$connected)>
                <i data-lucide="calendar-clock" class="h-4 w-4"></i>
                Salvar agendamento
            </button>
        </form>

        <aside class="space-y-6">
            <div class="card">
                <h2 class="section-title">Preview</h2>
                <div class="mt-4 aspect-video overflow-hidden rounded-sm bg-accent">
                    @if ($video->video_path && file_exists($video->video_path))
                        <video controls class="h-full w-full bg-black">
                            <source src="{{ route('videos.download', $video) }}" type="video/mp4">
                        </video>
                    @else
                        <div class="flex h-full items-center justify-center text-sm text-muted-foreground">Arquivo não encontrado.</div>
                    @endif
                </div>
            </div>

            <div class="card bg-accent/60">
                <h2 class="section-title">Observação</h2>
                <p class="mt-3 text-sm leading-relaxed text-muted-foreground">Se o horário ficar vazio, o vídeo será publicado na próxima execução do comando. Para Shorts, use vídeo vertical e curto; o YouTube classifica automaticamente após o processamento.</p>
            </div>
        </aside>
    </div>
</div>
@endsection
