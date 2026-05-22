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
    <div class="flex min-h-screen">
        @include('components.sidebar')
        <main class="ml-[100px] flex-1">
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
