<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Application')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @php($compiledThemePath = public_path('css/app.css'))
    @if (file_exists($compiledThemePath))
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
    @endif
    @stack('head')
</head>
<body class="theme-shell auth-shell @yield('body_class')">
<div class="app-frame app-frame--no-right">
    <aside class="app-sidebar">
        @yield('left_sidebar')
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            @yield('topbar')
        </header>

        <main class="app-content">
            <div class="content-grid">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
