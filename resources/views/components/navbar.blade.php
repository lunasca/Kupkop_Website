<header class="site-header">
    <div class="site-container nav-wrapper">
        {{-- Brand / Logo --}}
        <div class="brand-group">
            <a href="{{ route('home') }}" class="brand-link">
                <span class="brand-paw">🐾</span>
                <span class="brand-name">Kupkop</span>
            </a>
            <span class="brand-divider">|</span>
            <span class="brand-subtext">Brgy. San Antonio Pet Adoption</span>
        </div>

        {{-- Nav Links --}}
        <nav class="nav-links">
            <a href="{{ route('home') }}" class="nav-pill {{ request()->routeIs('home') ? 'active' : '' }}">
                Home
            </a>

            <a href="{{ route('home') }}#pets" class="nav-link-item">
                My Posts
            </a>

            <a href="{{ route('home') }}#pets" class="nav-link-item">
                Messages
            </a>

            @auth
                @if(Auth::user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="nav-admin-badge">
                        ⚙️ Admin
                    </a>
                @endif

                <span class="nav-user-label">
                    👋 {{ Auth::user()->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="inline-form">
                    @csrf
                    <button type="submit" class="nav-btn-logout">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-btn-white">
                    Login
                </a>
            @endauth
        </nav>
    </div>
</header>
