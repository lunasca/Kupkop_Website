<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form (fallback if accessed directly).
     */
    public function showLoginForm(): View
    {
        return view('pages.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = strtolower(trim($credentials['email']));

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Prevent login for archived or removed accounts
            if ($user->archived_at !== null || $user->status === 'removed') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $reason = $user->block_reason ?: 'Violation of community adoption guidelines';

                if ($request->expectsJson()) {
                    return response()->json([
                        'message'             => 'Your account has been removed by the Barangay Kupkop Administrator.',
                        'restriction_title'   => 'Account Suspended',
                        'restriction_message' => 'Your account has been removed by the Barangay Kupkop Administrator.',
                        'block_reason'        => $reason,
                        'status'              => 'restricted',
                    ], 403);
                }

                return back()
                    ->withErrors([
                        'account_restricted' => 'Your account has been removed by the Barangay Kupkop Administrator.',
                        'email'              => 'Your account has been removed by the Barangay Kupkop Administrator.',
                    ])
                    ->with('is_account_restricted', true)
                    ->with('account_blocked', true)
                    ->with('restriction_status', 'removed')
                    ->with('restriction_title', 'Account Suspended')
                    ->with('restriction_message', 'Your account has been removed by the Barangay Kupkop Administrator.')
                    ->with('block_reason', $reason)
                    ->onlyInput('email');
            }

            // Prevent login for blocked accounts
            if ($user->status === 'blocked' || $user->is_blocked) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $reason = $user->block_reason ?: 'Violation of community adoption guidelines';

                if ($request->expectsJson()) {
                    return response()->json([
                        'message'             => 'Your account has been blocked by the Barangay Kupkop Administrator.',
                        'restriction_title'   => 'Account Access Restricted',
                        'restriction_message' => 'Your account has been blocked by the Barangay Kupkop Administrator.',
                        'block_reason'        => $reason,
                        'status'              => 'restricted',
                    ], 403);
                }

                return back()
                    ->withErrors([
                        'account_restricted' => 'Your account has been blocked by the Barangay Kupkop Administrator.',
                        'email'              => 'Your account has been blocked by the Barangay Kupkop Administrator.',
                    ])
                    ->with('is_account_restricted', true)
                    ->with('account_blocked', true)
                    ->with('restriction_status', 'blocked')
                    ->with('restriction_title', 'Account Access Restricted')
                    ->with('restriction_message', 'Your account has been blocked by the Barangay Kupkop Administrator.')
                    ->with('block_reason', $reason)
                    ->onlyInput('email');
            }

            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
