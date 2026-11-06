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
    {{-- Global top progress bar --}}
    <div id="kup-progress-bar"></div>

    {{-- Reusable Navigation Bar Component --}}
    <x-navbar />

    {{-- Main Page Content --}}
    <main class="main-content">
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

    {{-- Global background polling: keeps the unread-message badge live on every page --}}
    @auth
        @if(!Auth::user()->is_admin)
        <script>
        (function () {
            'use strict';
            const POLL_INTERVAL = 30000; // 30 s — optimized for serverless / pooled database
            let globalPollTimer = null;

            function updateNavBadge(count) {
                const badge = document.getElementById('navMsgBadge');
                const badgeMobile = document.getElementById('navMsgBadgeMobile');
                [badge, badgeMobile].forEach(el => {
                    if (!el) return;
                    if (count > 0) {
                        el.textContent = count > 99 ? '99+' : count;
                        el.style.display = 'flex';
                        el.setAttribute('data-count', count);
                    } else {
                        el.style.display = 'none';
                        el.setAttribute('data-count', '0');
                    }
                });
            }

            async function globalPollUnread() {
                try {
                    const res = await fetch('/messages/unread', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (typeof data.unread_total !== 'undefined') {
                        updateNavBadge(data.unread_total);
                        // Allow the messages page to hook into global poll updates
                        if (typeof window.kupGlobalUnreadUpdate === 'function') {
                            window.kupGlobalUnreadUpdate(data.unread_total, data.unreads_by_sender || {});
                        }
                    }
                } catch (e) {
                    // Silent — network errors are expected during navigation / offline states
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                globalPollTimer = setInterval(globalPollUnread, POLL_INTERVAL);
            });

            window.addEventListener('beforeunload', function () {
                if (globalPollTimer) clearInterval(globalPollTimer);
            });
        })();
        </script>
        @endif
    @endauth

    @stack('scripts')
</body>
</html>
