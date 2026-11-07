<header class="site-header">
    <div class="site-container nav-wrapper">
        {{-- Brand / Logo + Mobile Hamburger (Left Side) --}}
        <div class="brand-group" style="display: flex; align-items: center; gap: 0.65rem;">
            <button
                type="button"
                class="nav-mobile-toggle nav-mobile-toggle-left"
                id="navMobileToggle"
                onclick="toggleNavDropdown()"
                aria-label="Open navigation menu"
                aria-expanded="false"
            >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <a href="{{ route('home') }}" class="brand-link" title="Kupkop Home" style="display: flex; align-items: center; text-decoration: none; transition: opacity 0.3s ease;">
                <img 
                    src="{{ asset('images/logo.png') }}" 
                    alt="Kupkop Logo" 
                    class="nav-logo-img"
                    style="height: 32px; width: auto; object-fit: contain; display: block;"
                >
            </a>
        </div>

        {{-- Center: Page Switcher Icons — only shown for regular logged in users (admins manage via Admin Panel) --}}
        @auth
            @php
                $isAdmin = (bool) (Auth::user()->is_admin ?? false);
                $isHome     = request()->routeIs('home');
                $isMyPosts  = request()->routeIs('my-posts');
                $isMessages = request()->routeIs('messages');
                $navUnreadCount = \App\Models\Message::where('receiver_id', Auth::id())
                    ->where('is_read', false)
                    ->count();
                $navPendingRequests = \App\Models\AdoptionRequest::whereHas('pet', function ($q) {
                        $q->where('user_id', Auth::id());
                    })
                    ->where('status', 'Pending')
                    ->when(Auth::user()->requests_last_viewed_at, function ($q) {
                        $q->where('created_at', '>', Auth::user()->requests_last_viewed_at);
                    })
                    ->count();
            @endphp

            @if(!$isAdmin)
            <nav class="nav-page-switcher" aria-label="Page navigation">
                <a href="{{ route('home') }}"
                   class="nav-item-link {{ $isHome ? 'nav-pill active' : 'nav-link-item' }}"
                   title="Home">
                    <x-lucide-paw-print class="nav-icon" />
                    <span class="nav-label">Home</span>
                </a>

                <a href="{{ route('my-posts') }}"
                   class="nav-item-link {{ $isMyPosts ? 'nav-pill active' : 'nav-link-item' }}"
                   title="My Posts">
                    <span class="nav-icon-wrap">
                        <x-lucide-heart-handshake class="nav-icon" />
                        @if($navPendingRequests > 0)
                            <span class="nav-unread-badge" data-count="{{ $navPendingRequests }}">{{ $navPendingRequests > 99 ? '99+' : $navPendingRequests }}</span>
                        @endif
                    </span>
                    <span class="nav-label">My Posts</span>
                </a>

                <a href="{{ route('messages') }}"
                   class="nav-item-link nav-msg-link {{ $isMessages ? 'nav-pill active' : 'nav-link-item' }}"
                   title="Messages">
                    <span class="nav-icon-wrap">
                        <x-lucide-messages-square class="nav-icon" />
                        <span id="navMsgBadge" class="nav-unread-badge" style="{{ $navUnreadCount > 0 ? '' : 'display: none;' }}" data-count="{{ $navUnreadCount }}">{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
                    </span>
                    <span class="nav-label">Messages</span>
                </a>
            </nav>
            @endif
        @endauth

        {{-- Right: Auth / Action Area (always at the very end of the navbar) --}}
        <div class="nav-actions" style="grid-column: 3; justify-self: end; display: flex; align-items: center;">
            @auth
                @if(Auth::user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="nav-admin-badge" title="Go to Admin Dashboard" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.38rem 0.8rem; background: #FEF3C7; color: #4A2E2B; border: 1px solid #FCD34D; border-radius: 9999px; font-size: 0.82rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease;">
                        <x-lucide-shield-check style="width: 14px; height: 14px; stroke-width: 2.5; color: #D97706;" />
                        <span>Admin Panel</span>
                    </a>

                    {{-- Admin Profile Display (Plain label without edit trigger / pencil icon) --}}
                    <div class="nav-user-display" title="Logged in as Administrator">
                        <x-lucide-user style="width: 14px; height: 14px; stroke-width: 2.2;" />
                        <span class="nav-user-name">{{ Auth::user()->name }}</span>
                    </div>
                @else
                    {{-- Regular User Profile Button (Clickable with pencil icon) --}}
                    <button 
                        type="button" 
                        class="nav-user-btn" 
                        onclick="openProfileModal()" 
                        title="Click to edit account details"
                        aria-haspopup="dialog"
                        aria-controls="profile-modal"
                    >
                        <x-lucide-user style="width: 14px; height: 14px; stroke-width: 2.2;" />
                        <span class="nav-user-name">{{ Auth::user()->name }}</span>
                        <x-lucide-pencil style="width: 11px; height: 11px; stroke-width: 2; opacity: 0.75;" />
                    </button>
                @endif

                {{-- Logout form (submitted by the confirmation modal) --}}
                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                    @csrf
                </form>

                <button
                    type="button"
                    class="nav-btn-logout desktop-only"
                    title="Log Out"
                    onclick="showLogoutConfirm()"
                >
                    <x-lucide-log-out class="nav-icon" />
                </button>
            @else
                <button
                    type="button"
                    class="nav-btn-white"
                    onclick="openLoginModal()"
                    aria-haspopup="dialog"
                    aria-controls="login-modal"
                >
                    Log In
                </button>
            @endauth

            {{-- Mobile Hamburger Toggle moved to left side of navbar --}}
        </div>
    </div>

    {{-- Mobile Slide-in Drawer (Left Side) --}}
</header>

<div id="nav-mobile-drawer" class="nav-mobile-drawer" role="dialog" aria-modal="true" aria-label="Navigation Menu">
    {{-- Drawer Header: Logo + Close Button --}}
    <div class="nav-drawer-header">
        <a href="{{ route('home') }}" class="nav-drawer-logo-link" onclick="closeNavDropdown()">
            <img src="{{ asset('images/logo.png') }}" alt="Kupkop Logo" class="nav-drawer-logo-img">
        </a>
        <button type="button" class="nav-drawer-close" onclick="closeNavDropdown()" aria-label="Close menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    {{-- Drawer Nav Links --}}
    <nav class="nav-drawer-nav">
        @auth
            @if(Auth::user()->is_admin)
                {{-- Admin Drawer Links --}}
                <div class="nav-drawer-section-label">General</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-drawer-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" onclick="closeNavDropdown()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>
                <div class="nav-drawer-section-label">Management</div>
                <a href="{{ route('admin.posts') }}" class="nav-drawer-link {{ request()->routeIs('admin.posts') ? 'active' : '' }}" onclick="closeNavDropdown()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Posts</span>
                </a>
                <a href="{{ route('admin.accounts') }}" class="nav-drawer-link {{ request()->routeIs('admin.accounts') ? 'active' : '' }}" onclick="closeNavDropdown()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Accounts</span>
                </a>
                <div class="nav-drawer-section-label">Quick links</div>
                <a href="{{ route('home') }}" class="nav-drawer-link" onclick="closeNavDropdown()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>View public site</span>
                </a>
            @else
                {{-- Regular User Drawer Links --}}
                <a href="{{ route('home') }}" class="nav-drawer-link {{ request()->routeIs('home') ? 'active' : '' }}" onclick="closeNavDropdown()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Home</span>
                </a>
                <a href="{{ route('my-posts') }}" class="nav-drawer-link {{ request()->routeIs('my-posts') ? 'active' : '' }}" onclick="closeNavDropdown()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span>My Posts</span>
                    @if(isset($navPendingRequests) && $navPendingRequests > 0)
                        <span class="nav-drawer-badge">{{ $navPendingRequests > 99 ? '99+' : $navPendingRequests }}</span>
                    @endif
                </a>
                <a href="{{ route('messages') }}" class="nav-drawer-link {{ request()->routeIs('messages') ? 'active' : '' }}" onclick="closeNavDropdown()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Messages</span>
                    @if(isset($navUnreadCount) && $navUnreadCount > 0)
                        <span class="nav-drawer-badge">{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
                    @endif
                </a>
            @endif
        @else
            {{-- Guest Links --}}
            <a href="{{ route('home') }}" class="nav-drawer-link {{ request()->routeIs('home') ? 'active' : '' }}" onclick="closeNavDropdown()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Home</span>
            </a>
            <button type="button" class="nav-drawer-link" onclick="closeNavDropdown(); openLoginModal();">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <span>Log In</span>
            </button>
            <button type="button" class="nav-drawer-link" onclick="closeNavDropdown(); openRegisterModal();">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                <span>Create Account</span>
            </button>
        @endauth
    </nav>

    {{-- Drawer Footer: User Info + Logout --}}
    @auth
        <div class="nav-drawer-footer">
            <div class="nav-drawer-divider"></div>
            @if(Auth::user()->is_admin)
                <div class="nav-drawer-user-row">
                    <div class="nav-drawer-user-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <span class="nav-drawer-user-name">{{ Auth::user()->name }}</span>
                </div>
                <div class="nav-drawer-barangay-card">
                    <div class="nav-drawer-barangay-title">Brgy. 176-F, Caloocan City</div>
                    <div class="nav-drawer-barangay-sub">Pets listed for adoption in your barangay.</div>
                </div>
            @else
                <button type="button" class="nav-drawer-user-row nav-drawer-profile-btn" onclick="closeNavDropdown(); openProfileModal();">
                    <div class="nav-drawer-user-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <span class="nav-drawer-user-name">{{ Auth::user()->name }}</span>
                </button>
            @endif
            <button type="button" class="nav-drawer-logout-btn" onclick="closeNavDropdown(); showLogoutConfirm();">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Log out</span>
            </button>
        </div>
    @endauth
</div>

{{-- Backdrop for the drawer --}}
<div id="nav-dropdown-backdrop" class="nav-dropdown-backdrop" onclick="closeNavDropdown()"></div>

<script>
    function toggleNavDropdown() {
        const drawer = document.getElementById('nav-mobile-drawer');
        const backdrop = document.getElementById('nav-dropdown-backdrop');
        const btn = document.getElementById('navMobileToggle');
        if (!drawer) return;
        const isOpen = drawer.classList.contains('open');
        if (isOpen) {
            closeNavDropdown();
        } else {
            drawer.classList.add('open');
            if (backdrop) backdrop.classList.add('active');
            if (btn) btn.setAttribute('aria-expanded', 'true');
            document.body.classList.add('menu-open');
        }
    }

    function closeNavDropdown() {
        const drawer = document.getElementById('nav-mobile-drawer');
        const backdrop = document.getElementById('nav-dropdown-backdrop');
        const btn = document.getElementById('navMobileToggle');
        if (drawer) drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
    }

    // Backwards compatibility
    window.toggleNavMobileDrawer = toggleNavDropdown;
    window.closeNavMobileDrawer = closeNavDropdown;

    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            closeNavDropdown();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeNavDropdown();
        }
    });
</script>

@auth
{{-- =====================================================================
     LOGOUT CONFIRMATION MODAL
     ===================================================================== --}}
<div
    id="confirm-logout-modal"
    class="modal-backdrop"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-logout-title"
    style="display: none; z-index: 1080;"
    onclick="if(event.target.id === 'confirm-logout-modal') hideLogoutConfirm()"
>
    <div class="modal-dialog" onclick="event.stopPropagation()" style="max-width: 400px; background: #ffffff; border-radius: 1.25rem; padding: 2rem 1.75rem; box-shadow: 0 25px 60px rgba(74, 46, 43, 0.22); text-align: center; border: 1px solid #F3E8D8;">
        {{-- Icon --}}
        <div style="width: 56px; height: 56px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem auto;">
            <x-lucide-log-out style="width: 26px; height: 26px; stroke-width: 2; color: #4A2E2B;" />
        </div>

        {{-- Title --}}
        <h3 id="confirm-logout-title" style="font-size: 1.2rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.45rem 0;">
            Log Out?
        </h3>

        {{-- Description --}}
        <p style="font-size: 0.92rem; color: #52473D; margin: 0 0 1.5rem 0; line-height: 1.5;">
            Are you sure you want to log out of your Kupkop account?
        </p>

        {{-- Actions --}}
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button
                type="button"
                onclick="hideLogoutConfirm()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.25rem; border: 1px solid #D5C2B1; background: #FAF5EE; color: #4A2E2B; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#F3E8D8'"
                onmouseout="this.style.background='#FAF5EE'"
            >
                <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Cancel</span>
            </button>
            <button
                type="button"
                onclick="if (window.showKupLoader) window.showKupLoader('Logging out...'); document.getElementById('logout-form').submit()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 1.25rem; border: none; background: #4A2E2B; color: #ffffff; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;"
                onmouseover="this.style.background='#382220'"
                onmouseout="this.style.background='#4A2E2B'"
            >
                <x-lucide-log-out style="width: 15px; height: 15px; stroke-width: 2;" />
                <span>Yes, Log Out</span>
            </button>
        </div>
    </div>
</div>

<script>
    function showLogoutConfirm() {
        const modal = document.getElementById('confirm-logout-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }
    }

    function hideLogoutConfirm() {
        const modal = document.getElementById('confirm-logout-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const logoutModal = document.getElementById('confirm-logout-modal');
            if (logoutModal && logoutModal.style.display === 'flex') {
                hideLogoutConfirm();
            }
        }
    });
</script>
@endauth
