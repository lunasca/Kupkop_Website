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
<body class="admin-body {{ request()->routeIs('admin.posts') ? 'page-admin-posts' : '' }} {{ request()->routeIs('admin.accounts') ? 'page-admin-accounts' : '' }}">
    <div class="admin-wrapper" id="adminWrapper">
        {{-- Mobile sidebar overlay backdrop --}}
        <div class="admin-sidebar-overlay" id="adminSidebarOverlay" onclick="closeAdminSidebar()"></div>

        {{-- Admin Sidebar Component --}}
        <x-admin.sidebar />

        <div class="admin-main">
            {{-- Mobile Header Bar (Phone & Tablet) --}}
            <div class="admin-mobile-header" id="adminMobileHeader">
                <div class="brand-group" style="display: flex; align-items: center; gap: 0.65rem;">
                    <button type="button" class="admin-mobile-toggle" id="adminMenuToggle" onclick="toggleAdminSidebar()" aria-label="Open navigation menu" style="background: transparent !important; background-color: transparent !important; border: none !important; box-shadow: none !important; outline: none !important; border-radius: 0 !important; padding: 4px 6px !important; display: inline-flex; align-items: center; justify-content: center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="brand-link" title="Kupkop Admin Dashboard" style="display: flex; align-items: center; text-decoration: none;">
                        <img src="{{ asset('images/logo.png') }}" alt="Kupkop Logo" class="nav-logo-img" style="height: 26px; width: auto; object-fit: contain; display: block;">
                    </a>
                </div>
            </div>

            {{-- Main Content Area --}}
            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Base Script -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

    <script>
    function toggleAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('adminSidebarOverlay');
        const isOpen = sidebar.classList.contains('open');
        if (isOpen) {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    function closeAdminSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.getElementById('adminSidebarOverlay');
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    // Close sidebar on window resize if above tablet breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            closeAdminSidebar();
        }
    });
    </script>

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

    {{-- Global Profile Edit Modal for admin account details --}}
    <x-profile-modal />

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

        // Admin Custom Dropdowns Controller
        document.addEventListener('DOMContentLoaded', function () {
            const groups = document.querySelectorAll('.admin-custom-select-group');

            function closeAllSelects() {
                groups.forEach(g => {
                    g.classList.remove('open');
                    const tr = g.querySelector('.admin-custom-select-trigger');
                    if (tr) tr.setAttribute('aria-expanded', 'false');
                });
            }

            groups.forEach(group => {
                const trigger = group.querySelector('.admin-custom-select-trigger');
                const hiddenInput = group.querySelector('input[type="hidden"]');
                const options = group.querySelectorAll('.admin-custom-select-option');
                const labelSpan = group.querySelector('.admin-custom-select-label');
                const prefix = group.getAttribute('data-prefix') || '';

                if (!trigger) return;

                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const isOpen = group.classList.contains('open');
                    closeAllSelects();
                    if (!isOpen) {
                        group.classList.add('open');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });

                options.forEach(opt => {
                    opt.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const val = this.getAttribute('data-value');
                        const label = this.getAttribute('data-label') || this.querySelector('span')?.textContent.trim() || '';

                        options.forEach(o => o.classList.remove('selected'));
                        this.classList.add('selected');

                        if (labelSpan) labelSpan.textContent = prefix + label;
                        if (hiddenInput) hiddenInput.value = val;

                        closeAllSelects();

                        const form = group.closest('form');
                        if (form) form.submit();
                    });
                });
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('.admin-custom-select-group')) {
                    closeAllSelects();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeAllSelects();
                }
            });
        });
    </script>
</body>
</html>
