<aside class="admin-sidebar">
    {{-- Mobile close button --}}
    <button type="button" class="admin-sidebar-close-btn" onclick="closeAdminSidebar()" aria-label="Close navigation">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand" title="Kupkop Admin Dashboard">
            <img src="{{ asset('images/logo.png') }}" alt="Kupkop Logo" class="h-10 w-auto">
        </a>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section-label">General</div>
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <x-lucide-layout-grid style="width: 17px; height: 17px; stroke-width: 2.2;" />
            <span>Dashboard</span>
        </a>

        <div class="menu-section-label">Management</div>
        <a href="{{ route('admin.posts') }}" class="sidebar-link {{ request()->routeIs('admin.posts*') ? 'active' : '' }}">
            <x-lucide-paw-print style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>Posts</span>
        </a>
        <a href="{{ route('admin.accounts') }}" class="sidebar-link {{ request()->routeIs('admin.accounts*') ? 'active' : '' }}">
            <x-lucide-users style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>Accounts</span>
        </a>

        <div class="menu-section-label">Quick links</div>
        <a href="{{ route('home') }}" class="sidebar-link" target="_blank">
            <x-lucide-external-link style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>View public site</span>
        </a>

        <div class="menu-section-label">Account</div>
        <div class="sidebar-user-info" style="display: flex; align-items: center; gap: 0.6rem; padding: 0.5rem 0.75rem; background: #FAF5EE; border-radius: 0.5rem; margin-bottom: 0.4rem;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: #F3E8D8; display: flex; align-items: center; justify-content: center; color: #4A2E2B; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 0.85rem; font-weight: 700; color: #2D1810; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div style="font-size: 0.72rem; color: #8C756B;">Administrator</div>
            </div>
        </div>
        <button type="button" class="sidebar-link sidebar-btn" onclick="openProfileModal()" title="Account settings">
            <x-lucide-settings style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>Settings</span>
        </button>
        <button type="button" class="sidebar-link sidebar-btn sidebar-btn-logout" onclick="showAdminLogoutConfirm()" title="Log out">
            <x-lucide-log-out style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>Log out</span>
        </button>
    </nav>

    {{-- Bottom Sidebar Card (Brgy. 176-F) --}}
    <div class="sidebar-footer-container">
        <div class="sidebar-barangay-card">
            <h4 class="barangay-title">Brgy. 176-F, Caloocan City</h4>
            <p class="barangay-desc">Pets listed for adoption in your barangay.</p>
        </div>
    </div>
</aside>
