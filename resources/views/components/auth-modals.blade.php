{{-- ==========================================================================
     AUTHENTICATION MODALS (LOGIN & SIGN UP)
     Rendered globally in layouts/app.blade.php
   ========================================================================== --}}

@guest
    {{-- 1. LOGIN MODAL --}}
    <div 
        id="login-modal" 
        class="modal-backdrop auth-modal-backdrop" 
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="login-modal-title"
        style="display: none;"
        onclick="handleAuthBackdropClick(event, 'login-modal')"
    >
        <div class="modal-dialog auth-modal-dialog" onclick="event.stopPropagation()">
            {{-- Close Button --}}
            <button 
                type="button" 
                class="modal-close-btn" 
                onclick="closeLoginModal()" 
                aria-label="Close modal"
            >
                &times;
            </button>

            <div class="auth-card-modal">
                <div class="auth-header">
                    <span class="auth-modal-badge">🐾 Member Access</span>
                    <h2 id="login-modal-title">Welcome Back</h2>
                    <p class="text-muted">Sign in to your {{ config('app.name', 'Kupkop') }} account.</p>
                </div>

                {{-- Quick Demo Credentials Helper --}}
                <div class="demo-account-hint">
                    <p class="demo-hint-title">💡 Quick Test Credentials:</p>
                    <p>User: <code>user@kupkop.test</code> | Password: <code>password123</code></p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="auth-form" id="login-modal-form">
                    @csrf

                    {{-- Email Address --}}
                    <div class="form-group">
                        <label for="modal_login_email">Email Address</label>
                        <input
                            id="modal_login_email"
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
                        <label for="modal_login_password">Password</label>
                        <input
                            id="modal_login_password"
                            type="password"
                            name="password"
                            required
                            placeholder="Enter your password"
                            class="form-control @error('password') is-invalid @enderror"
                        >
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="form-group-checkbox">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="modal_remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me on this device</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit-auth">Log In</button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account yet? 
                        <a href="javascript:void(0)" onclick="switchToRegisterModal()">Sign Up</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SIGN UP (REGISTER) MODAL --}}
    <div 
        id="register-modal" 
        class="modal-backdrop auth-modal-backdrop" 
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="register-modal-title"
        style="display: none;"
        onclick="handleAuthBackdropClick(event, 'register-modal')"
    >
        <div class="modal-dialog auth-modal-dialog" onclick="event.stopPropagation()">
            {{-- Close Button --}}
            <button 
                type="button" 
                class="modal-close-btn" 
                onclick="closeRegisterModal()" 
                aria-label="Close modal"
            >
                &times;
            </button>

            <div class="auth-card-modal">
                <div class="auth-header">
                    <span class="auth-modal-badge">🐾 Join Our Community</span>
                    <h2 id="register-modal-title">Create an Account</h2>
                    <p class="text-muted">Join {{ config('app.name', 'Kupkop') }} to adopt pets and post rescues.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="auth-form" id="register-modal-form">
                    @csrf

                    {{-- Full Name --}}
                    <div class="form-group">
                        <label for="modal_register_name">Full Name *</label>
                        <input
                            id="modal_register_name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="e.g. Maria Santos"
                            class="form-control @error('name') is-invalid @enderror"
                        >
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Email Address --}}
                    <div class="form-group">
                        <label for="modal_register_email">Email Address *</label>
                        <input
                            id="modal_register_email"
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
                        <label for="modal_register_password">Password (Min. 6 chars) *</label>
                        <input
                            id="modal_register_password"
                            type="password"
                            name="password"
                            required
                            placeholder="Create a strong password"
                            class="form-control @error('password') is-invalid @enderror"
                        >
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="form-group">
                        <label for="modal_register_password_confirmation">Confirm Password *</label>
                        <input
                            id="modal_register_password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            placeholder="Re-type your password"
                            class="form-control"
                        >
                    </div>

                    <button type="submit" class="btn-submit-auth">Create Account</button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? 
                        <a href="javascript:void(0)" onclick="switchToLoginModal()">Log In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Auth Modals JavaScript Controller --}}
    <script>
        function openLoginModal() {
            closeRegisterModal();
            const modal = document.getElementById('login-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.classList.add('modal-open');
                setTimeout(() => {
                    const emailInput = document.getElementById('modal_login_email');
                    if (emailInput) emailInput.focus();
                }, 100);
            }
        }

        function closeLoginModal() {
            const modal = document.getElementById('login-modal');
            if (modal) {
                modal.style.display = 'none';
                if (!document.getElementById('register-modal') || document.getElementById('register-modal').style.display === 'none') {
                    document.body.classList.remove('modal-open');
                }
            }
        }

        function openRegisterModal() {
            closeLoginModal();
            const modal = document.getElementById('register-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.classList.add('modal-open');
                setTimeout(() => {
                    const nameInput = document.getElementById('modal_register_name');
                    if (nameInput) nameInput.focus();
                }, 100);
            }
        }

        function closeRegisterModal() {
            const modal = document.getElementById('register-modal');
            if (modal) {
                modal.style.display = 'none';
                if (!document.getElementById('login-modal') || document.getElementById('login-modal').style.display === 'none') {
                    document.body.classList.remove('modal-open');
                }
            }
        }

        function switchToRegisterModal() {
            closeLoginModal();
            openRegisterModal();
        }

        function switchToLoginModal() {
            closeRegisterModal();
            openLoginModal();
        }

        function handleAuthBackdropClick(event, modalId) {
            if (event.target.id === modalId) {
                if (modalId === 'login-modal') closeLoginModal();
                if (modalId === 'register-modal') closeRegisterModal();
            }
        }

        // Global keydown handler for Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLoginModal();
                closeRegisterModal();
            }
        });

        // Auto-reopen appropriate modal on validation error
        @if($errors->has('name') || ($errors->any() && old('name')))
            document.addEventListener('DOMContentLoaded', function() {
                openRegisterModal();
            });
        @elseif($errors->any() && !old('name'))
            document.addEventListener('DOMContentLoaded', function() {
                openLoginModal();
            });
        @endif
    </script>
@endguest
