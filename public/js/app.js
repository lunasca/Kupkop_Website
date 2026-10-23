// KUPKOP - Base Application Scripts
console.log('KUPKOP application initialized.');

// Global Password Visibility Toggle
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    const eyeIcon = btn.querySelector('.icon-eye');
    const eyeOffIcon = btn.querySelector('.icon-eye-off');

    if (eyeIcon && eyeOffIcon) {
        eyeIcon.style.display = isPassword ? 'none' : 'inline-block';
        eyeOffIcon.style.display = isPassword ? 'inline-block' : 'none';
    }

    const label = isPassword ? 'Hide password' : 'Show password';
    btn.setAttribute('aria-label', label);
    btn.setAttribute('title', label);
}

// =============================================================================
// LOADING INDICATOR — Global Overlay + Top Progress Bar + Button Spinner
// =============================================================================

(function () {
    'use strict';

    // ── Safe Progress Bar Element Access ─────────────────────────────────────
    let barTimer = null;
    let barValue = 0;
    let loaderHideTimer = null;

    function getProgressBar() {
        let el = document.getElementById('kup-progress-bar');
        if (!el) {
            el = document.createElement('div');
            el.id = 'kup-progress-bar';
            document.body.prepend(el);
        }
        return el;
    }

    function startBar() {
        const bar = getProgressBar();
        clearTimeout(barTimer);
        barValue = 0;
        bar.style.width = '0%';
        bar.style.opacity = '1';
        bar.classList.add('kup-bar-active');

        _trickleTo(20, 150);
        _trickleTo(45, 500);
        _trickleTo(70, 1200);
        _trickleTo(85, 2200);
    }

    function _trickleTo(target, delay) {
        barTimer = setTimeout(function () {
            const bar = getProgressBar();
            if (barValue < target) {
                barValue = target;
                bar.style.width = barValue + '%';
            }
        }, delay);
    }

    function finishBar() {
        const bar = getProgressBar();
        clearTimeout(barTimer);
        bar.style.width = '100%';
        setTimeout(function () {
            bar.style.opacity = '0';
            setTimeout(function () {
                bar.style.width = '0%';
                bar.classList.remove('kup-bar-active');
            }, 300);
        }, 200);
    }

    // ── Global Loader Functions ──────────────────────────────────────────────
    window.showKupLoader = function (msg) {
        clearTimeout(loaderHideTimer);
        const loader = document.getElementById('kup-global-loader');
        const msgEl = document.getElementById('kup-loader-message');

        if (msgEl) {
            msgEl.textContent = msg || 'Loading...';
        }
        if (loader) {
            loader.classList.add('is-active');
            loader.setAttribute('aria-hidden', 'false');
        }

        startBar();

        // Safety fallback: auto-hide after 12s in case navigation is cancelled or fast cached
        loaderHideTimer = setTimeout(function () {
            window.hideKupLoader();
        }, 12000);
    };

    window.hideKupLoader = function () {
        clearTimeout(loaderHideTimer);
        const loader = document.getElementById('kup-global-loader');
        if (loader) {
            loader.classList.remove('is-active');
            loader.setAttribute('aria-hidden', 'true');
        }
        finishBar();
    };

    // ── Trigger: internal <a> links ──────────────────────────────────────────
    function isInternalLink(a) {
        return (
            a &&
            a.href &&
            !a.href.startsWith('javascript') &&
            !a.target &&
            !a.download &&
            a.origin === location.origin &&
            !(a.pathname === location.pathname && a.search === location.search && a.hash)
        );
    }

    document.addEventListener('click', function (e) {
        const a = e.target.closest('a');
        if (a && isInternalLink(a) && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.defaultPrevented) {
            let label = 'Loading page...';
            if (a.title) {
                label = 'Loading ' + a.title + '...';
            } else if (a.textContent && a.textContent.trim().length > 0 && a.textContent.trim().length < 24) {
                label = 'Loading ' + a.textContent.trim() + '...';
            }
            window.showKupLoader(label);
        }
    }, true);

    // ── Trigger: any <form> submission ───────────────────────────────────────
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form) return;

        // If client-side HTML5 validation fails, don't show loader
        if (form.checkValidity && !form.checkValidity()) {
            return;
        }

        let msg = 'Loading...';
        if (form.id === 'filter-form') {
            msg = 'Filtering pets...';
        } else if (form.id === 'delete-post-form') {
            msg = 'Deleting post...';
        } else if (form.id === 'logout-form') {
            msg = 'Logging out...';
        } else if (form.classList && form.classList.contains('create-post-form')) {
            msg = 'Publishing post...';
        } else if (form.id === 'edit-post-form') {
            msg = 'Updating post...';
        } else if (form.id === 'profile-form') {
            msg = 'Updating profile...';
        } else if (form.action && form.action.includes('adoption-requests')) {
            msg = 'Submitting adoption request...';
        } else if (form.action && form.action.includes('login')) {
            msg = 'Signing in...';
        } else if (form.action && form.action.includes('register')) {
            msg = 'Creating account...';
        }

        window.showKupLoader(msg);

        // Spinner on the submit button
        const submitBtn = form.querySelector('[type="submit"]') ||
                          (document.activeElement && document.activeElement.type === 'submit'
                              ? document.activeElement : null);

        if (submitBtn && !submitBtn.dataset.kupSpinner) {
            submitBtn.dataset.kupSpinner = '1';
            const originalHTML = submitBtn.innerHTML;
            const originalDisabled = submitBtn.disabled;

            const firstSpan = submitBtn.querySelector('span');
            const btnText = firstSpan ? firstSpan.textContent.trim() : submitBtn.textContent.trim();

            submitBtn.innerHTML =
                '<span class="kup-btn-spinner"></span>' +
                '<span style="margin-left:0.35rem;">' + btnText + '</span>';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.75';
            submitBtn.style.cursor = 'not-allowed';

            setTimeout(function () {
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = originalDisabled;
                submitBtn.style.opacity = '';
                submitBtn.style.cursor = '';
                delete submitBtn.dataset.kupSpinner;
                window.hideKupLoader();
            }, 10000);
        }
    }, true);

    // ── Intercept programmatic form.submit() calls ───────────────────────────
    const origSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        if (window.showKupLoader) {
            let msg = 'Loading...';
            if (this.id === 'filter-form') {
                msg = 'Filtering pets...';
            } else if (this.id === 'delete-post-form') {
                msg = 'Deleting post...';
            } else if (this.id === 'logout-form') {
                msg = 'Logging out...';
            } else if (this.classList && this.classList.contains('create-post-form')) {
                msg = 'Saving post...';
            } else if (this.id === 'edit-post-form') {
                msg = 'Updating post...';
            }
            window.showKupLoader(msg);
        }
        return origSubmit.apply(this, arguments);
    };

    // ── Finish & hide loader on pageshow / bfcache ───────────────────────────
    window.addEventListener('pageshow', function () {
        window.hideKupLoader();
    });

    document.addEventListener('DOMContentLoaded', function () {
        window.hideKupLoader();
    });
})();

