@extends('layouts.app')

@section('title', __('Serviços') . ' — NEW VideoMaker')
@section('description', __('Status em tempo real dos serviços necessários para geração de vídeos.'))

@section('content')
<div class="min-h-screen p-8 lg:p-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-bold tracking-tight text-foreground lg:text-5xl">{{ __('Serviços') }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">{{ __('Status em tempo real dos serviços necessários para geração de vídeos.') }}</p>
        </div>
        <span id="allStatus" class="rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase {{ collect($services)->every(fn($s) => $s['up']) ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground' }}">
            {{ collect($services)->every(fn($s) => $s['up']) ? __('Tudo rodando') : __('Serviço offline') }}
        </span>
    </div>

    <div id="servicesList" class="mt-10 grid gap-5 lg:grid-cols-2">
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
                    {{ $service['up'] ? __('Online') : __('Offline') }}
                </p>
                <p class="service-detail mt-2 text-[11px] text-muted-foreground">{{ $service['detail'] ?? '' }}</p>
                @if (!$service['up'] && !empty($service['install']))
                    <a href="{{ $service['install'] }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 text-[11px] text-muted-foreground underline hover:text-foreground">
                        Como instalar ↗
                    </a>
                @endif
            </article>
        @endforeach
    </div>

    @isset($pipeline)
        <section class="card mt-8">
            <h2 class="section-title">Pipeline Python</h2>
            <p class="mt-1 text-xs text-muted-foreground">Caminhos configurados em <a href="{{ route('config') }}" class="underline">CONFIG → Pipeline</a>.</p>
            <div class="mt-4 space-y-2 text-xs">
                @foreach (['python' => 'Python', 'pipeline' => 'pipeline.py', 'output_dir' => 'Pasta de saída'] as $k => $label)
                    @php $info = $pipeline[$k]; @endphp
                    <div class="flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full {{ $info['ok'] ? 'bg-chart-2' : 'bg-destructive' }}"></span>
                        <span class="w-32 text-foreground">{{ $label }}:</span>
                        <span class="flex-1 break-all text-muted-foreground">{{ $info['path'] ?: '—' }}</span>
                        @if (!$info['ok'])<span class="text-destructive">{{ $info['reason'] }}</span>@endif
                    </div>
                @endforeach
            </div>
        </section>
    @endisset

    <section class="card mt-8">
        <h2 class="section-title">{{ __('Inicialização dos serviços') }}</h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <p class="text-sm font-medium text-foreground">Ollama</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">ollama serve</code>
            </div>
            <div>
                <p class="text-sm font-medium text-foreground">ComfyUI</p>
                <code class="mt-2 block rounded-sm bg-accent p-3 text-xs text-muted-foreground">python main.py --lowvram</code>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const T_ALL_RUNNING = @json(__('Tudo rodando'));
    const T_OFFLINE     = @json(__('Serviço offline'));
    const T_ONLINE      = @json(__('Online'));
    const T_OFF         = @json(__('Offline'));

    async function refreshServices() {
        try {
            const response = await fetch('{{ route('health.api') }}');
            const data = await response.json();
            const allStatus = document.getElementById('allStatus');
            allStatus.textContent = data.all_up ? T_ALL_RUNNING : T_OFFLINE;
            allStatus.className = `rounded-sm px-3 py-2 font-display text-[10px] font-medium tracking-wider uppercase ${data.all_up ? 'bg-primary text-primary-foreground' : 'bg-destructive text-destructive-foreground'}`;

            data.services.forEach((service) => {
                const card = document.querySelector(`.service-card[data-port="${service.port}"]`);
                if (!card) return;
                const dot = card.querySelector('.service-dot');
                const label = card.querySelector('.service-label');
                const detail = card.querySelector('.service-detail');
                dot.className = `service-dot h-3 w-3 rounded-full ${service.up ? 'bg-primary' : 'bg-destructive animate-pulse'}`;
                label.textContent = service.up ? T_ONLINE : T_OFF;
                label.className = `service-label mt-6 font-display text-sm font-semibold ${service.up ? 'text-foreground' : 'text-destructive'}`;
                if (detail) detail.textContent = service.detail || '';
            });
        } catch (error) {
            console.error(error);
        }
    }

    setInterval(refreshServices, 4000);
</script>
@endpush
