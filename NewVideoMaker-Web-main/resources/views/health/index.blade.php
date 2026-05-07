@extends('layouts.app')

@section('title', 'Serviços — NEW VideoMaker')
@section('description', 'Status dos serviços locais do pipeline.')

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">Serviços</h1>
            <p class="mt-2 text-sm text-muted-foreground">Verificação dos serviços locais usados pelo pipeline.</p>
        </div>
        <span id="allStatus" class="rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase {{ collect($services)->every(fn($s) => $s['up']) ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground' }}">
            {{ collect($services)->every(fn($s) => $s['up']) ? 'Tudo rodando' : 'Serviço offline' }}
        </span>
    </div>

    <div id="servicesList" class="mt-10 grid gap-5 lg:grid-cols-3">
        @foreach ($services as $service)
            <article class="service-card card" data-port="{{ $service['port'] }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-foreground">{{ $service['name'] }}</h2>
                        <p class="mt-1 text-xs text-muted-foreground">{{ $service['host'] }}:{{ $service['port'] }}</p>
                    </div>
                    <span class="service-dot h-3 w-3 rounded-full {{ $service['up'] ? 'bg-primary' : 'bg-destructive animate-pulse' }}"></span>
                </div>
                <p class="service-label mt-6 font-display text-sm font-semibold {{ $service['up'] ? 'text-foreground' : 'text-destructive' }}">
                    {{ $service['up'] ? 'Online' : 'Offline' }}
                </p>
            </article>
        @endforeach
    </div>

    <section class="card mt-8">
        <h2 class="section-title">Comandos de apoio</h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-foreground">Ollama</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">ollama serve</code>
            </div>
            <div>
                <p class="text-sm font-medium text-foreground">ComfyUI</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">python main.py --lowvram</code>
            </div>
            <div>
                <p class="text-sm font-medium text-foreground">ACE-Step</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">uv run python gradio_app.py</code>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    async function refreshServices() {
        try {
            const response = await fetch('{{ route('health.api') }}');
            const data = await response.json();
            const allStatus = document.getElementById('allStatus');
            allStatus.textContent = data.all_up ? 'Tudo rodando' : 'Serviço offline';
            allStatus.className = `rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase ${data.all_up ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'}`;

            data.services.forEach((service) => {
                const card = document.querySelector(`.service-card[data-port="${service.port}"]`);
                if (!card) return;
                const dot = card.querySelector('.service-dot');
                const label = card.querySelector('.service-label');
                dot.className = `service-dot h-3 w-3 rounded-full ${service.up ? 'bg-primary' : 'bg-destructive animate-pulse'}`;
                label.textContent = service.up ? 'Online' : 'Offline';
                label.className = `service-label mt-6 font-display text-sm font-semibold ${service.up ? 'text-foreground' : 'text-destructive'}`;
            });
        } catch (error) {
            console.error(error);
        }
    }

    setInterval(refreshServices, 4000);
</script>
@endpush
