<nav class="navbar">
    <div class="nav-container">
        <a href="{{ route('home') }}" class="brand-logo">
            🐾 {{ config('app.name', 'KUPKOP') }}
        </a>

        <div class="nav-links">
            <a href="{{ route('home') }}" class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            {{-- Feature links (e.g. Animals, Adoption, Messages) will go here as they are developed --}}
        </div>
    </div>
</nav>
