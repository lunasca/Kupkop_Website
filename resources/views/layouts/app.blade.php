<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo_1.png') }}">

    <title>@yield('title', config('app.name', 'Kupkop') . ' - Brgy. 176-F Pet Adoption')</title>

    <!-- Google Fonts for clean typography (Outfit - modern geometric font close to Spotify Mix) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Base Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('styles')
</head>
<body class="site-body {{ request()->routeIs('home') ? 'page-home' : '' }} {{ request()->routeIs('messages') ? 'page-messages' : '' }} {{ !auth()->check() ? 'is-guest' : 'is-auth' }}">
    {{-- Global top progress bar & centered loading indicator --}}
    <div id="kup-progress-bar"></div>
    <div id="kup-global-loader" class="kup-global-loader" aria-hidden="true">
        <div class="kup-loader-card">
            <div class="kup-loader-spinner-wrapper">
                <div class="kup-loader-ring"></div>
                <span class="kup-loader-icon">🐾</span>
            </div>
            <span class="kup-loader-message" id="kup-loader-message">Loading...</span>
        </div>
    </div>

    {{-- Reusable Navigation Bar Component --}}
    <x-navbar />

    {{-- Main Page Content --}}
    <main class="main-content">
        {{-- Flash Banners: only shown after login or sign-up --}}
        @if(session('auth_success') || session('auth_error'))
            <div class="site-container alert-container">
                @if(session('auth_success'))
                    <x-alert type="success" :message="session('auth_success')" />
                @endif

                @if(session('auth_error'))
                    <x-alert type="error" :message="session('auth_error')" />
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Common Footer (Only shown when not signed in) --}}
    @guest
        <footer class="footer">
            <div class="site-container">
                <p>🐾 {{ config('app.name', 'Kupkop') }} Barangay Pet Adoption</p>
            </div>
        </footer>
    @endguest

    {{-- Global Authentication Modals (Login & Register) for guests --}}
    <x-auth-modals />

    {{-- Global Profile Edit Modal for authenticated users --}}
    <x-profile-modal />

    <!-- Base Script -->
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    @stack('scripts')
</body>
</html>
