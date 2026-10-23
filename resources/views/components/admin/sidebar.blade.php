<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand" title="Kupkop Admin Dashboard">
            <img src="{{ asset('images/logo.png') }}" alt="Kupkop Logo" class="h-10 w-auto">
            <span class="badge-admin">ADMIN</span>
        </a>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section-label">General</div>
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <x-lucide-layout-grid style="width: 17px; height: 17px; stroke-width: 2.2;" />
            <span>Dashboard</span>
        </a>

        <div class="menu-section-label">Management</div>
        <a href="#" class="sidebar-link {{ request()->routeIs('admin.posts') ? 'active' : '' }}">
            <x-lucide-paw-print style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>Posts</span>
        </a>
        <a href="#" class="sidebar-link {{ request()->routeIs('admin.accounts') ? 'active' : '' }}">
            <x-lucide-users style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>Accounts</span>
        </a>

        <div class="menu-section-label">Quick links</div>
        <a href="{{ route('home') }}" class="sidebar-link" target="_blank">
            <x-lucide-external-link style="width: 17px; height: 17px; stroke-width: 2;" />
            <span>View public site</span>
        </a>
    </nav>

    {{-- Bottom Sidebar Card (Brgy. 171) --}}
    <div class="sidebar-footer-container">
        <div class="sidebar-barangay-card">
            <h4 class="barangay-title">Brgy. 176-F, Caloocan City</h4>
            <p class="barangay-desc">Pets listed for adoption in your barangay.</p>
        </div>
    </div>
</aside>
