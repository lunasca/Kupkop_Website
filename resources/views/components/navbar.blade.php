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

        {{-- Center: Page Switcher Icons — only shown when logged in --}}
        @auth
        <nav class="nav-page-switcher" aria-label="Page navigation">
            @php
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
        @endauth

        {{-- Right: Auth / Action Area (always at the very end of the navbar) --}}
        <div class="nav-actions" style="grid-column: 3; justify-self: end; display: flex; align-items: center; gap: 0.85rem;">
            @auth
                @if(Auth::user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="nav-admin-badge" title="Go to Admin Dashboard" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.38rem 0.8rem; background: #FEF3C7; color: #4A2E2B; border: 1px solid #FCD34D; border-radius: 9999px; font-size: 0.82rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease;">
                        <x-lucide-shield-check style="width: 14px; height: 14px; stroke-width: 2.5; color: #D97706;" />
                        <span>Admin Panel</span>
                    </a>
                @endif

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

                {{-- Logout form (submitted by the confirmation modal) --}}
                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                    @csrf
                </form>

                <button
                    type="button"
                    class="nav-btn-logout"
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
        </div>
    </div>
</header>

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
