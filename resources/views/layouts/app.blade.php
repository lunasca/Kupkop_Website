<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'KUPKOP'))</title>

    <!-- Base Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    {{-- Reusable Navigation Bar Component --}}
    <x-navbar />

    {{-- Main Page Content --}}
    <main class="main-content">
        <div class="container">
            {{-- Global Flash Messages --}}
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Common Footer --}}
    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'KUPKOP') }}. All rights reserved.</p>
        </div>
    </footer>

    <!-- Base Script -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
