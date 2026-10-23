@extends('layouts.app')

@section('title', 'Log In - ' . config('app.name', 'KUPKOP'))

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p class="text-muted">Sign in to your {{ config('app.name', 'KUPKOP') }} account.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            {{-- Email Address --}}
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="username"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="you@example.com"
                    class="form-control @error('email') is-invalid @enderror"
                >
                @error('email')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-wrapper">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        placeholder="Enter your password"
                        class="form-control @error('password') is-invalid @enderror"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="togglePasswordVisibility('password', this)"
                        aria-label="Show password"
                        title="Show password"
                    >
                        <x-lucide-eye class="icon-eye" style="width: 18px; height: 18px; stroke-width: 2;" />
                        <x-lucide-eye-off class="icon-eye-off" style="width: 18px; height: 18px; stroke-width: 2; display: none;" />
                    </button>
                </div>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="form-group-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me on this device</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account yet? <a href="{{ route('register') }}">Sign Up</a></p>
        </div>
    </div>
</div>
@endsection
