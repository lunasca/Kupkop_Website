<header class="site-header">
    <div class="site-container nav-wrapper">
        {{-- Brand / Logo --}}
        <div class="brand-group">
            <a href="{{ route('home') }}" class="brand-link" title="Kupkop Home" style="display: flex; align-items: center; text-decoration: none; transition: opacity 0.3s ease;">
                <img 
                    src="{{ asset('images/logo.png') }}" 
                    alt="Kupkop Logo" 
                    class="nav-logo-img"
                    style="height: 40px; width: auto; object-fit: contain; display: block;"
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

            {{-- Mobile Drawer Hamburger Button --}}
            <button
                type="button"
                class="nav-mobile-toggle"
                id="navMobileToggle"
                onclick="toggleNavMobileDrawer()"
                aria-label="Open navigation menu"
                aria-expanded="false"
            >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
</header>

{{-- Mobile Navigation Drawer Backdrop & Drawer --}}
<div id="nav-mobile-backdrop" class="nav-mobile-backdrop" onclick="closeNavMobileDrawer()"></div>
<div id="nav-mobile-drawer" class="nav-mobile-drawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation Menu">
    <div class="nav-drawer-header">
        <a href="{{ route('home') }}" class="nav-drawer-brand" onclick="closeNavMobileDrawer()">
            <img src="{{ asset('images/logo.png') }}" alt="Kupkop Logo" style="height: 34px; width: auto; object-fit: contain;">
        </a>
        <button type="button" class="nav-drawer-close" onclick="closeNavMobileDrawer()" aria-label="Close menu">
            <x-lucide-x style="width: 20px; height: 20px; stroke-width: 2.2;" />
        </button>
    </div>

    @auth
        <div class="nav-drawer-user-card">
            <div class="nav-drawer-avatar">
                <x-lucide-user style="width: 20px; height: 20px; stroke-width: 2.2; color: #4A2E2B;" />
            </div>
            <div class="nav-drawer-user-info">
                <strong class="nav-drawer-user-name">{{ Auth::user()->name }}</strong>
                <span class="nav-drawer-user-role">{{ Auth::user()->is_admin ? 'Administrator' : 'Resident / Rescuer' }}</span>
            </div>
            @if(!Auth::user()->is_admin)
                <button type="button" class="nav-drawer-edit-btn" onclick="closeNavMobileDrawer(); openProfileModal();" title="Edit Profile">
                    <x-lucide-pencil style="width: 13px; height: 13px; stroke-width: 2;" />
                </button>
            @endif
        </div>

        <nav class="nav-drawer-menu">
            <a href="{{ route('home') }}" class="nav-drawer-link {{ request()->routeIs('home') ? 'active' : '' }}" onclick="closeNavMobileDrawer()">
                <x-lucide-paw-print class="nav-drawer-icon" />
                <span>Home Feed</span>
            </a>

            @if(!Auth::user()->is_admin)
                <a href="{{ route('my-posts') }}" class="nav-drawer-link {{ request()->routeIs('my-posts') ? 'active' : '' }}" onclick="closeNavMobileDrawer()">
                    <x-lucide-heart-handshake class="nav-drawer-icon" />
                    <span>My Listed Pets</span>
                    @if(isset($navPendingRequests) && $navPendingRequests > 0)
                        <span class="nav-drawer-badge">{{ $navPendingRequests > 99 ? '99+' : $navPendingRequests }}</span>
                    @endif
                </a>

                <a href="{{ route('messages') }}" class="nav-drawer-link {{ request()->routeIs('messages') ? 'active' : '' }}" onclick="closeNavMobileDrawer()">
                    <x-lucide-messages-square class="nav-drawer-icon" />
                    <span>Messages</span>
                    <span id="navMsgBadgeMobile" class="nav-drawer-badge" style="{{ (isset($navUnreadCount) && $navUnreadCount > 0) ? '' : 'display: none;' }}">{{ (isset($navUnreadCount) && $navUnreadCount > 99) ? '99+' : ($navUnreadCount ?? 0) }}</span>
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="nav-drawer-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" onclick="closeNavMobileDrawer()">
                    <x-lucide-shield-check class="nav-drawer-icon" />
                    <span>Admin Dashboard</span>
                </a>
                <a href="{{ route('admin.posts') }}" class="nav-drawer-link {{ request()->routeIs('admin.posts*') ? 'active' : '' }}" onclick="closeNavMobileDrawer()">
                    <x-lucide-paw-print class="nav-drawer-icon" />
                    <span>Manage Posts</span>
                </a>
                <a href="{{ route('admin.accounts') }}" class="nav-drawer-link {{ request()->routeIs('admin.accounts*') ? 'active' : '' }}" onclick="closeNavMobileDrawer()">
                    <x-lucide-users class="nav-drawer-icon" />
                    <span>Manage Accounts</span>
                </a>
            @endif

            <button type="button" class="nav-drawer-link nav-drawer-logout" onclick="closeNavMobileDrawer(); showLogoutConfirm();">
                <x-lucide-log-out class="nav-drawer-icon" />
                <span>Log Out</span>
            </button>
        </nav>
    @else
        <div class="nav-drawer-guest-box">
            <p class="nav-drawer-guest-text">Welcome to Kupkop! Adopt rescued animals and connect with pet rescuers in Brgy. 176-F.</p>
            <div style="display: flex; flex-direction: column; gap: 0.65rem; width: 100%;">
                <button type="button" class="btn-gate-login" onclick="closeNavMobileDrawer(); openLoginModal();" style="width: 100%; text-align: center;">Log In</button>
                <button type="button" class="btn-gate-signup" onclick="closeNavMobileDrawer(); openRegisterModal();" style="width: 100%; text-align: center;">Create Account</button>
            </div>
        </div>
    @endauth
</div>

<script>
    function toggleNavMobileDrawer() {
        const drawer = document.getElementById('nav-mobile-drawer');
        const backdrop = document.getElementById('nav-mobile-backdrop');
        const btn = document.getElementById('navMobileToggle');
        if (!drawer || !backdrop) return;
        const isOpen = drawer.classList.contains('open');
        if (isOpen) {
            closeNavMobileDrawer();
        } else {
            drawer.classList.add('open');
            backdrop.classList.add('active');
            document.body.classList.add('modal-open');
            if (btn) btn.setAttribute('aria-expanded', 'true');
        }
    }

    function closeNavMobileDrawer() {
        const drawer = document.getElementById('nav-mobile-drawer');
        const backdrop = document.getElementById('nav-mobile-backdrop');
        const btn = document.getElementById('navMobileToggle');
        if (drawer) drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.classList.remove('modal-open');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeNavMobileDrawer();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const drawer = document.getElementById('nav-mobile-drawer');
            if (drawer && drawer.classList.contains('open')) {
                closeNavMobileDrawer();
            }
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
