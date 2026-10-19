<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Kupkop') . ' - Brgy. San Antonio Pet Adoption')</title>

    <!-- Google Fonts for clean typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Base Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="site-body">
    {{-- Reusable Navigation Bar Component --}}
    <x-navbar />

    {{-- Main Page Content --}}
    <main class="main-content">
        {{-- Global Flash Messages Container --}}
        @if(session('success') || session('error'))
            <div class="site-container alert-container">
                @if(session('success'))
                    <x-alert type="success" :message="session('success')" />
                @endif

                @if(session('error'))
                    <x-alert type="error" :message="session('error')" />
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Common Footer --}}
    <footer class="footer">
        <div class="site-container">
            <p>🐾 {{ config('app.name', 'Kupkop') }} Barangay Pet Adoption</p>
        </div>
    </footer>

    <!-- Base Script -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
