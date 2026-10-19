<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            🐾 {{ config('app.name', 'KUPKOP') }} <span class="badge-admin">Admin</span>
        </a>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section-label">General</div>
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            📊 Dashboard
        </a>

        <div class="menu-section-label">Management</div>
        {{-- Future admin feature links --}}
        <a href="#" class="sidebar-link">
            🐶 Manage Animals
        </a>
        <a href="#" class="sidebar-link">
            📋 Adoption Requests
        </a>
        <a href="#" class="sidebar-link">
            💬 Messages
        </a>

        <div class="menu-section-label">Quick Links</div>
        <a href="{{ route('home') }}" class="sidebar-link" target="_blank">
            🌐 View Public Site ↗
        </a>
    </nav>
</aside>
