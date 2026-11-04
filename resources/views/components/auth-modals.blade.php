{{-- ==========================================================================
     AUTHENTICATION MODALS (LOGIN, SIGN UP & ACCOUNT RESTRICTION)
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
                <x-lucide-x class="modal-close-icon" />
            </button>

            <div class="auth-card-modal">
                <div class="auth-header">
                    <h2 id="login-modal-title">Welcome Back</h2>
                    <p class="text-muted">Sign in to your {{ config('app.name', 'Kupkop') }} account.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="auth-form" id="login-modal-form">
                    @csrf
                    <input type="hidden" name="auth_action" value="login">

                    {{-- Email Address --}}
                    <div class="form-group">
                        <label for="modal_login_email">Email Address</label>
                        <input
                            id="modal_login_email"
                            type="email"
                            name="email"
                            autocomplete="username"
                            value="{{ old('email') }}"
                            required
                            placeholder="you@example.com"
                            class="form-control @if($errors->has('email') && !$errors->has('account_restricted')) is-invalid @endif"
                        >
                        @if($errors->has('email') && !$errors->has('account_restricted'))
                            <span class="form-error">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label for="modal_login_password">Password</label>
                        <div class="password-input-wrapper">
                            <input
                                id="modal_login_password"
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
                                onclick="togglePasswordVisibility('modal_login_password', this)"
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
                <x-lucide-x class="modal-close-icon" />
            </button>

            <div class="auth-card-modal">
                <div class="auth-header">
                    <h2 id="register-modal-title">Create an Account</h2>
                    <p class="text-muted">Join {{ config('app.name', 'Kupkop') }} to adopt pets and post rescues.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="auth-form" id="register-modal-form">
                    @csrf
                    <input type="hidden" name="auth_action" value="register">

                    {{-- Full Name --}}
                    <div class="form-group">
                        <label for="modal_register_name">Full Name</label>
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
                        <label for="modal_register_email">Email Address</label>
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
                        <label for="modal_register_password">Password (Min. 6 chars)</label>
                        <div class="password-input-wrapper">
                            <input
                                id="modal_register_password"
                                type="password"
                                name="password"
                                required
                                placeholder="Create a strong password"
                                class="form-control @error('password') is-invalid @enderror"
                            >
                            <button 
                                type="button" 
                                class="password-toggle-btn" 
                                onclick="togglePasswordVisibility('modal_register_password', this)"
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

                    {{-- Confirm Password --}}
                    <div class="form-group">
                        <label for="modal_register_password_confirmation">Confirm Password</label>
                        <div class="password-input-wrapper">
                            <input
                                id="modal_register_password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                placeholder="Re-type your password"
                                class="form-control"
                            >
                            <button 
                                type="button" 
                                class="password-toggle-btn" 
                                onclick="togglePasswordVisibility('modal_register_password_confirmation', this)"
                                aria-label="Show password"
                                title="Show password"
                            >
                                <x-lucide-eye class="icon-eye" style="width: 18px; height: 18px; stroke-width: 2;" />
                                <x-lucide-eye-off class="icon-eye-off" style="width: 18px; height: 18px; stroke-width: 2; display: none;" />
                            </button>
                        </div>
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

    {{-- 3. DEDICATED STANDALONE ACCOUNT ACCESS RESTRICTED MODAL --}}
    @php
        $isRestricted = $errors->has('account_restricted') || session('is_account_restricted') || session('account_blocked');
        $restTitle   = session('restriction_title') ?? ($errors->first('account_restricted') && str_contains(strtolower($errors->first('account_restricted')), 'suspend') ? 'Account Suspended' : 'Account Access Restricted');
        $restMessage = session('restriction_message') ?? ($errors->first('account_restricted') ?: 'Your account has been blocked by the Barangay Kupkop Administrator.');
        $restReason  = session('block_reason') ?? 'Violation of community adoption guidelines';
    @endphp
    <div 
        id="account-restricted-modal" 
        class="modal-backdrop auth-modal-backdrop" 
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="restricted-modal-title"
        style="{{ $isRestricted ? 'display: flex;' : 'display: none;' }} z-index: 1080;"
        onclick="handleAuthBackdropClick(event, 'account-restricted-modal')"
    >
        <div class="modal-dialog auth-modal-dialog" onclick="event.stopPropagation()" style="max-width: 460px;">
            {{-- Close Button --}}
            <button 
                type="button" 
                class="modal-close-btn" 
                onclick="closeRestrictedModal()" 
                aria-label="Close modal"
            >
                <x-lucide-x class="modal-close-icon" />
            </button>

            <div class="auth-card-modal" style="padding: 2.25rem 2rem; border-top: 5px solid #DC2626; border-radius: 1.25rem;">
                {{-- Icon & Header --}}
                <div style="text-align: center; margin-bottom: 1.25rem;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.85rem; box-shadow: 0 0 0 6px #FEF2F2;">
                        <x-lucide-shield-alert style="width: 30px; height: 30px; stroke-width: 2.2;" />
                    </div>
                    <h2 id="restricted-modal-title" style="font-size: 1.35rem; font-weight: 800; color: #991B1B; margin: 0 0 0.35rem 0; letter-spacing: -0.3px;">
                        {{ $restTitle }}
                    </h2>
                    <span style="display: inline-block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: #FEE2E2; color: #991B1B; padding: 2px 10px; border-radius: 999px;">
                        Barangay Kupkop Security Notice
                    </span>
                </div>

                {{-- Message --}}
                <p style="font-size: 0.94rem; color: #4A2E2B; margin: 0 0 1.15rem 0; line-height: 1.55; text-align: center; font-weight: 500;">
                    {{ $restMessage }}
                </p>

                {{-- Reason Box --}}
                <div style="background: #FEF2F2; border-left: 4px solid #DC2626; border-radius: 0 0.65rem 0.65rem 0; padding: 0.85rem 1.1rem; margin-bottom: 1.25rem;">
                    <div style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: #7F1D1D; letter-spacing: 0.4px; margin-bottom: 0.25rem;">
                        Reason Provided by Admin
                    </div>
                    <div style="font-size: 0.92rem; color: #991B1B; font-weight: 600; line-height: 1.45;">
                        {{ $restReason }}
                    </div>
                </div>

                {{-- Support Box --}}
                <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 0.75rem; padding: 0.85rem 1rem; margin-bottom: 1.65rem; font-size: 0.84rem; color: #78350F; line-height: 1.5;">
                    <strong style="color: #92400E; display: block; margin-bottom: 0.2rem;">Barangay 176-F Support</strong>
                    For appeals, account review, or inquiries, please contact the Barangay 176-F administration at <strong>support@kupkop.local</strong> or visit the Barangay 176-F Animal Welfare Desk.
                </div>

                {{-- Action Buttons: Back to Login & Close --}}
                <div style="display: flex; gap: 0.75rem; justify-content: stretch;">
                    <button
                        type="button"
                        onclick="backToLoginFromRestricted()"
                        style="flex: 1; padding: 0.75rem 1rem; border: none; background: #4A2E2B; color: #ffffff; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: background 0.15s ease;"
                        onmouseover="this.style.background='#361F1D'"
                        onmouseout="this.style.background='#4A2E2B'"
                    >
                        <x-lucide-arrow-left style="width: 17px; height: 17px; stroke-width: 2.2;" />
                        <span>Back to Login</span>
                    </button>
                    <button
                        type="button"
                        onclick="closeRestrictedModal()"
                        style="padding: 0.75rem 1.25rem; border: 1.5px solid #E5E7EB; background: #FAF9F6; color: #71655B; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: background 0.15s ease;"
                        onmouseover="this.style.background='#F3F4F6'"
                        onmouseout="this.style.background='#FAF9F6'"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Auth Modals JavaScript Controller --}}
    <script>
        function openLoginModal() {
            closeRegisterModal();
            closeRestrictedModal();
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
                updateBodyModalState();
            }
        }

        function openRegisterModal() {
            closeLoginModal();
            closeRestrictedModal();
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
                updateBodyModalState();
            }
        }

        function openRestrictedModal() {
            closeLoginModal();
            closeRegisterModal();
            const modal = document.getElementById('account-restricted-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.classList.add('modal-open');
            }
        }

        function closeRestrictedModal() {
            const modal = document.getElementById('account-restricted-modal');
            if (modal) {
                modal.style.display = 'none';
                updateBodyModalState();
            }
        }

        function backToLoginFromRestricted() {
            closeRestrictedModal();
            const pageEmail = document.getElementById('email');
            if (pageEmail) {
                pageEmail.focus();
            } else {
                openLoginModal();
            }
        }

        function updateBodyModalState() {
            const login = document.getElementById('login-modal');
            const reg = document.getElementById('register-modal');
            const rest = document.getElementById('account-restricted-modal');
            const isAnyOpen = (login && login.style.display === 'flex') ||
                              (reg && reg.style.display === 'flex') ||
                              (rest && rest.style.display === 'flex');
            if (!isAnyOpen) {
                document.body.classList.remove('modal-open');
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
                if (modalId === 'account-restricted-modal') closeRestrictedModal();
            }
        }

        // Global keydown handler for Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeLoginModal();
                closeRegisterModal();
                closeRestrictedModal();
            }
        });

        // Auto-reopen appropriate modal on validation error / restriction
        @if($errors->has('account_restricted') || session('is_account_restricted') || session('account_blocked'))
            document.addEventListener('DOMContentLoaded', function() {
                closeLoginModal();
                closeRegisterModal();
                openRestrictedModal();
            });
        @elseif(old('auth_action') === 'register' || $errors->has('name') || $errors->has('password_confirmation'))
            document.addEventListener('DOMContentLoaded', function() {
                openRegisterModal();
            });
        @elseif(old('auth_action') === 'login' || ($errors->any() && old('auth_action') !== 'register'))
            document.addEventListener('DOMContentLoaded', function() {
                openLoginModal();
            });
        @endif
    </script>
@endguest
