<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard - ' . config('app.name', 'KUPKOP'))</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo_1.png') }}">

    <!-- Google Fonts for clean typography (Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Base Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        {{-- Admin Sidebar Component --}}
        <x-admin.sidebar />

        <div class="admin-main">
            {{-- Top Admin Header Bar --}}
            <header class="admin-topbar">
                <div class="topbar-left">
                    {{-- Intentionally minimal, matching screenshot --}}
                </div>
                <div class="topbar-actions">
                    <a href="{{ route('home') }}" class="btn-topbar-public">
                        <x-lucide-globe style="width: 15px; height: 15px; stroke-width: 2.2;" />
                        <span>Public feed</span>
                    </a>
                    <div class="admin-user-pill">
                        <span class="avatar-circle">{{ strtoupper(substr(Auth::user()->name ?? 'K', 0, 1)) }}</span>
                        <span class="user-name">{{ Auth::user()->name ?? 'Kupkop Administrator' }}</span>
                    </div>
                    <button type="button" class="btn-topbar-logout" onclick="showAdminLogoutConfirm()">
                        <x-lucide-log-out style="width: 14px; height: 14px; stroke-width: 2.2;" />
                        <span>Log out</span>
                    </button>
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

    {{-- =====================================================================
         Admin Logout Confirmation Modal
         ===================================================================== --}}
    <div
        id="confirm-admin-logout-modal"
        class="modal-backdrop"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-admin-logout-title"
        style="display: none; z-index: 1080;"
        onclick="if(event.target.id === 'confirm-admin-logout-modal') hideAdminLogoutConfirm()"
    >
        <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 400px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22); text-align: center; border: 1px solid #F3E8D8;">
            {{-- Icon --}}
            <div style="width: 56px; height: 56px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
                <x-lucide-log-out style="width: 26px; height: 26px; stroke-width: 2; color: #4A2E2B;" />
            </div>

            {{-- Title --}}
            <h3 id="confirm-admin-logout-title" style="font-size: 1.2rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.45rem 0;">
                Log Out of Admin?
            </h3>

            {{-- Description --}}
            <p style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
                Are you sure you want to log out of the Admin Portal?
            </p>

            {{-- Actions --}}
            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <button
                    type="button"
                    onclick="hideAdminLogoutConfirm()"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.25rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                    onmouseover="this.style.background='#F3E8D8'"
                    onmouseout="this.style.background='#FAF5EE'"
                >
                    <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                    <span>Cancel</span>
                </button>
                <form id="admin-logout-form" method="POST" action="{{ route('logout') }}" style="display: inline; margin: 0;">
                    @csrf
                    <button
                        type="submit"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.25rem; border: none; background: #4A2E2B; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                        onmouseover="this.style.background='#382220'"
                        onmouseout="this.style.background='#4A2E2B'"
                    >
                        <x-lucide-log-out style="width: 15px; height: 15px; stroke-width: 2;" />
                        <span>Yes, Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAdminLogoutConfirm() {
            const modal = document.getElementById('confirm-admin-logout-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.classList.add('modal-open');
            }
        }

        function hideAdminLogoutConfirm() {
            const modal = document.getElementById('confirm-admin-logout-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const logoutModal = document.getElementById('confirm-admin-logout-modal');
                if (logoutModal && logoutModal.style.display === 'flex') {
                    hideAdminLogoutConfirm();
                }
            }
        });
    </script>
</body>
</html>
