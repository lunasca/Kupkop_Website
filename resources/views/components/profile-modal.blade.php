@auth
<div 
    id="profile-modal" 
    class="modal-backdrop auth-modal-backdrop" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="profile-modal-title"
    style="display: none; z-index: 1050;"
    onclick="handleProfileBackdropClick(event)"
>
    <div class="modal-dialog auth-modal-dialog" onclick="event.stopPropagation()" style="max-width: 480px;">
        <div class="auth-card-modal" style="padding: 2.25rem 2rem; position: relative;">
        {{-- Close Button --}}
        <button 
            type="button" 
            class="modal-close-btn" 
            onclick="confirmCloseProfileModal()" 
            aria-label="Close modal"
        >
            <x-lucide-x class="modal-close-icon" />
        </button>
            {{-- Header --}}
            <div class="auth-header" style="text-align: center; margin-bottom: 1.5rem;">
                <div style="width: 52px; height: 52px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                    <x-lucide-user style="width: 26px; height: 26px; stroke-width: 2; color: #4A2E2B;" />
                </div>
                <h2 id="profile-modal-title" style="font-size: 1.35rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.35rem 0;">
                    Edit Profile Details
                </h2>
                <p class="text-muted" style="font-size: 0.88rem; margin: 0; color: #8C756B;">
                    Update the personal information you provided during sign-up.
                </p>
            </div>

            {{-- Error Summary Banner if submitted with errors --}}
            @if(old('form_action') === 'profile' && $errors->any())
                <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; border-radius: 0.6rem; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.84rem;">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Please correct the errors below:</div>
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="auth-form" id="profile-modal-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_action" value="profile">

                {{-- Full Name --}}
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="profile_name" style="display: flex; align-items: center; gap: 0.35rem; font-weight: 600; font-size: 0.88rem; color: #4A2E2B; margin-bottom: 0.35rem;">
                        <x-lucide-user style="width: 14px; height: 14px; stroke-width: 2;" />
                        <span>Full Name *</span>
                    </label>
                    <input
                        id="profile_name"
                        type="text"
                        name="name"
                        value="{{ old('form_action') === 'profile' ? old('name') : Auth::user()->name }}"
                        required
                        placeholder="e.g. Maria Santos"
                        class="form-control"
                    >
                </div>

                {{-- Email Address --}}
                <div class="form-group" style="margin-bottom: 1.35rem;">
                    <label for="profile_email" style="display: flex; align-items: center; gap: 0.35rem; font-weight: 600; font-size: 0.88rem; color: #4A2E2B; margin-bottom: 0.35rem;">
                        <x-lucide-mail style="width: 14px; height: 14px; stroke-width: 2;" />
                        <span>Email Address *</span>
                    </label>
                    <input
                        id="profile_email"
                        type="email"
                        name="email"
                        value="{{ old('form_action') === 'profile' ? old('email') : Auth::user()->email }}"
                        required
                        placeholder="you@example.com"
                        class="form-control"
                    >
                </div>

                {{-- Password Change Section (Optional) --}}
                <div style="border-top: 1px solid #EADDCF; padding-top: 1.15rem; margin-top: 1.15rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                        <x-lucide-lock style="width: 15px; height: 15px; stroke-width: 2; color: #78350F;" />
                        <span style="font-weight: 700; font-size: 0.9rem; color: #4A2E2B;">Change Password</span>
                    </div>
                    <p style="font-size: 0.78rem; color: #8C756B; margin: 0 0 1rem 0;">
                        Leave blank if you do not wish to change your password.
                    </p>

                    {{-- Current Password --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="profile_current_password" style="font-weight: 600; font-size: 0.84rem; color: #4A2E2B; margin-bottom: 0.35rem; display: block;">
                            Current Password
                        </label>
                        <div class="password-input-wrapper">
                            <input
                                id="profile_current_password"
                                type="password"
                                name="current_password"
                                placeholder="Enter current password to confirm"
                                class="form-control"
                                autocomplete="current-password"
                            >
                            <button 
                                type="button" 
                                class="password-toggle-btn" 
                                onclick="togglePasswordVisibility('profile_current_password', this)"
                                aria-label="Show password"
                                title="Show password"
                            >
                                <x-lucide-eye class="icon-eye" style="width: 18px; height: 18px; stroke-width: 2;" />
                                <x-lucide-eye-off class="icon-eye-off" style="width: 18px; height: 18px; stroke-width: 2; display: none;" />
                            </button>
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="profile_new_password" style="font-weight: 600; font-size: 0.84rem; color: #4A2E2B; margin-bottom: 0.35rem; display: block;">
                            New Password (Min. 6 characters)
                        </label>
                        <div class="password-input-wrapper">
                            <input
                                id="profile_new_password"
                                type="password"
                                name="password"
                                placeholder="Create a new password"
                                class="form-control"
                                autocomplete="new-password"
                            >
                            <button 
                                type="button" 
                                class="password-toggle-btn" 
                                onclick="togglePasswordVisibility('profile_new_password', this)"
                                aria-label="Show password"
                                title="Show password"
                            >
                                <x-lucide-eye class="icon-eye" style="width: 18px; height: 18px; stroke-width: 2;" />
                                <x-lucide-eye-off class="icon-eye-off" style="width: 18px; height: 18px; stroke-width: 2; display: none;" />
                            </button>
                        </div>
                    </div>

                    {{-- Confirm New Password --}}
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="profile_new_password_confirmation" style="font-weight: 600; font-size: 0.84rem; color: #4A2E2B; margin-bottom: 0.35rem; display: block;">
                            Confirm New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input
                                id="profile_new_password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter new password"
                                class="form-control"
                                autocomplete="new-password"
                            >
                            <button 
                                type="button" 
                                class="password-toggle-btn" 
                                onclick="togglePasswordVisibility('profile_new_password_confirmation', this)"
                                aria-label="Show password"
                                title="Show password"
                            >
                                <x-lucide-eye class="icon-eye" style="width: 18px; height: 18px; stroke-width: 2;" />
                                <x-lucide-eye-off class="icon-eye-off" style="width: 18px; height: 18px; stroke-width: 2; display: none;" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #EADDCF; padding-top: 1.25rem;">
                    <button 
                        type="button" 
                        class="btn-draft" 
                        onclick="confirmCloseProfileModal()"
                        style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.15rem;"
                    >
                        <x-lucide-x style="width: 15px; height: 15px; stroke-width: 2;" />
                        <span>Cancel</span>
                    </button>
                    <button 
                        type="submit" 
                        class="btn-submit-post"
                        style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.25rem;"
                    >
                        <x-lucide-check style="width: 15px; height: 15px; stroke-width: 2.2;" />
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirm Discard Profile Changes Dialog --}}
<div 
    id="confirm-close-profile-modal"
    style="display: none; position: fixed; inset: 0; z-index: 1200; background: rgba(74,46,43,0.45); align-items: center; justify-content: center;"
    onclick="hideConfirmCloseProfile()"
>
    <div style="background: #fff; border-radius: 1rem; padding: 1.75rem 2rem; max-width: 380px; width: 90%; box-shadow: 0 20px 50px rgba(74,46,43,0.2); border: 1px solid #F3E8D8; text-align: center;" onclick="event.stopPropagation()">
        <div style="width: 48px; height: 48px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
            <x-lucide-alert-triangle style="width: 22px; height: 22px; stroke-width: 2; color: #D97706;" />
        </div>
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #4A2E2B; margin: 0 0 0.5rem 0;">Discard changes?</h3>
        <p style="font-size: 0.88rem; color: #52473D; margin: 0 0 1.5rem 0;">You have unsaved changes. Are you sure you want to leave without saving?</p>
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button 
                type="button"
                onclick="hideConfirmCloseProfile()"
                style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.1rem; background: #FAF6EE; border: 1px solid #D1D5DB; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; color: #4A2E2B; cursor: pointer; font-family: inherit;"
            >
                <x-lucide-arrow-left style="width: 14px; height: 14px; stroke-width: 2;" />
                Keep Editing
            </button>
            <button 
                type="button"
                onclick="discardProfileChanges()"
                style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.1rem; background: #DC2626; border: none; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; color: #fff; cursor: pointer; font-family: inherit;"
            >
                <x-lucide-trash-2 style="width: 14px; height: 14px; stroke-width: 2;" />
                Discard
            </button>
        </div>
    </div>
</div>

<script>
    // Stores the field values captured when the modal was opened
    let _profileOriginalValues = {};

    function openProfileModal() {
        const modal = document.getElementById('profile-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
            // Snapshot current field values as the "clean" baseline
            _profileOriginalValues = {
                name:  (document.getElementById('profile_name')?.value  || '').trim(),
                email: (document.getElementById('profile_email')?.value || '').trim(),
            };
            // Always start with empty password fields
            ['profile_current_password', 'profile_new_password', 'profile_new_password_confirmation'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
        }
    }

    function closeProfileModal() {
        const modal = document.getElementById('profile-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    // Returns true if the user has made any changes since the modal opened
    function profileHasChanges() {
        const name  = (document.getElementById('profile_name')?.value  || '').trim();
        const email = (document.getElementById('profile_email')?.value || '').trim();
        const pwd1  = document.getElementById('profile_current_password')?.value || '';
        const pwd2  = document.getElementById('profile_new_password')?.value || '';
        const pwd3  = document.getElementById('profile_new_password_confirmation')?.value || '';
        return name  !== _profileOriginalValues.name
            || email !== _profileOriginalValues.email
            || pwd1  !== ''
            || pwd2  !== ''
            || pwd3  !== '';
    }

    // Entry point for all close triggers (X, Cancel, backdrop, Escape)
    function confirmCloseProfileModal() {
        if (profileHasChanges()) {
            showConfirmCloseProfile();
        } else {
            closeProfileModal();
        }
    }

    function showConfirmCloseProfile() {
        const dialog = document.getElementById('confirm-close-profile-modal');
        if (dialog) dialog.style.display = 'flex';
    }

    function hideConfirmCloseProfile() {
        const dialog = document.getElementById('confirm-close-profile-modal');
        if (dialog) dialog.style.display = 'none';
    }

    // Reset all fields back to the values captured on open, then close
    function discardProfileChanges() {
        hideConfirmCloseProfile();
        const nameEl  = document.getElementById('profile_name');
        const emailEl = document.getElementById('profile_email');
        if (nameEl)  nameEl.value  = _profileOriginalValues.name  || '';
        if (emailEl) emailEl.value = _profileOriginalValues.email || '';
        ['profile_current_password', 'profile_new_password', 'profile_new_password_confirmation'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        closeProfileModal();
    }

    function handleProfileBackdropClick(event) {
        if (event.target.id === 'profile-modal') {
            confirmCloseProfileModal();
        }
    }

    // Auto-reopen profile modal on validation error
    @if(old('form_action') === 'profile' && $errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openProfileModal();
        });
    @endif

    // Escape key handling
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const confirmDialog = document.getElementById('confirm-close-profile-modal');
            const profileModal  = document.getElementById('profile-modal');
            if (confirmDialog && confirmDialog.style.display === 'flex') {
                // Escape on the confirm dialog → keep editing (dismiss dialog only)
                hideConfirmCloseProfile();
            } else if (profileModal && profileModal.style.display === 'flex') {
                confirmCloseProfileModal();
            }
        }
    });
</script>
@endauth
