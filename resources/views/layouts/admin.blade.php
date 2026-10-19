<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard - ' . config('app.name', 'KUPKOP'))</title>

    <!-- Base Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        {{-- Admin Sidebar Component --}}
        <x-admin.sidebar />

        <div class="admin-main">
            {{-- Top Admin Header Bar --}}
            <header class="admin-topbar">
                <div class="topbar-title">
                    <h2>@yield('header', 'Admin Portal')</h2>
                </div>
                <div class="topbar-actions">
                    <span class="user-greeting">👤 Admin</span>
                </div>
            </header>

            {{-- Main Content Area --}}
            <main class="admin-content">
                {{-- Global Flash Messages --}}
                @if(session('success'))
                    <x-alert type="success" :message="session('success')" />
                @endif

                @if(session('error'))
                    <x-alert type="error" :message="session('error')" />
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Base Script -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
