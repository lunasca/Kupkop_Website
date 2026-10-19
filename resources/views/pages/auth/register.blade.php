@extends('layouts.app')

@section('title', 'Sign Up - ' . config('app.name', 'KUPKOP'))

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Create an Account</h2>
            <p class="text-muted">Join {{ config('app.name', 'KUPKOP') }} to adopt pets and send messages.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            {{-- Full Name --}}
            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    placeholder="e.g. Maria Santos"
                    class="form-control @error('name') is-invalid @enderror"
                >
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Email Address --}}
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    placeholder="Minimum 6 characters"
                    class="form-control @error('password') is-invalid @enderror"
                >
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    placeholder="Re-type your password"
                    class="form-control"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="{{ route('login') }}">Log In</a></p>
        </div>
    </div>
</div>
@endsection
