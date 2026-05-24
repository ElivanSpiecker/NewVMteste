<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NEW VideoMaker')</title>
    <meta name="description" content="@yield('description', 'Plataforma de geração automatizada de vídeos curtos com inteligência artificial local.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    @inject('appConfig', 'App\Services\AppConfig')
    @php $pendencies = $appConfig->pendencies(); @endphp

    <div class="flex min-h-screen">
        @include('components.sidebar')
        <main class="ml-[100px] flex-1">
            @if (!empty($pendencies) && !request()->routeIs('config') && !request()->routeIs('setup.*'))
                <div class="border-b border-destructive/30 bg-destructive/5 px-6 py-2.5 text-xs">
                    <div class="flex flex-wrap items-center gap-2 text-destructive">
                        <i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i>
                        <span>Configuração incompleta — falta: <strong>{{ implode(', ', $pendencies) }}</strong>.</span>
                        <a href="{{ route('setup.index') }}" class="ml-auto underline hover:no-underline">Abrir wizard →</a>
                        <a href="{{ route('config') }}" class="underline hover:no-underline">ou /config</a>
                    </div>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
    @stack('scripts')
</body>
</html>
