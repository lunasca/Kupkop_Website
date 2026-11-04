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
