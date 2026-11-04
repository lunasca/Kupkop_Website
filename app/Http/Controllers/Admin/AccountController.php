<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountRestrictionMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the Accounts Management page (User accounts, Blocked accounts, or Archive tab).
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        // Tab selection: 'users', 'blocked', or 'archive' (defaults to 'users')
        $tab = $request->input('tab');
        if (!$tab) {
            $tab = ($request->input('status') === 'blocked') ? 'blocked' : 'users';
        }
        if (!in_array($tab, ['users', 'blocked', 'archive'])) {
            $tab = 'users';
        }

        // Live badge counts (exclude admin accounts)
        $userCount    = User::where('is_admin', false)->whereNull('archived_at')->count();
        $blockedCount = User::where('is_admin', false)->whereNull('archived_at')->where(function ($q) {
            $q->whereIn('status', ['blocked', 'suspended', 'restricted'])->orWhere('is_blocked', true);
        })->count();
        $archiveCount = User::where('is_admin', false)->whereNotNull('archived_at')->count();

        // Base query: never show admin accounts
        $query = User::where('is_admin', false);

        if ($tab === 'archive') {
            $query->whereNotNull('archived_at');
        } elseif ($tab === 'blocked') {
            $query->whereNull('archived_at')
                  ->where(function ($q) {
                      $q->whereIn('status', ['blocked', 'suspended', 'restricted'])
                        ->orWhere('is_blocked', true);
                  });
        } else {
            $query->whereNull('archived_at');

            // Status filter (only relevant for User accounts tab)
            if ($request->filled('status') && strtolower($request->status) !== 'all') {
                $statusFilter = strtolower($request->status);
                if (in_array($statusFilter, ['active', 'blocked'])) {
                    $query->where('status', $statusFilter);
                }
            }
        }

        // Search: by name or email
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        // Order newest first
        $users = $query->latest()->get();

        // Attach active post count to each user
        $users->each(function (User $user) {
            $user->active_posts_count = $user->pets()
                ->whereNull('archived_at')
                ->count();
        });

        return view('pages.admin.accounts', compact('users', 'tab', 'userCount', 'blockedCount', 'archiveCount'));
    }

    /**
     * Block a user account.
     */
    public function block(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        // Self-protection: admin cannot block themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot block your own account.');
        }

        // Admin-protection: cannot block other admins
        if ($user->is_admin) {
            return back()->with('error', 'Admin accounts cannot be blocked.');
        }

        // Already archived accounts cannot be blocked
        if ($user->archived_at) {
            return back()->with('error', 'Archived accounts cannot be blocked directly. Restore first.');
        }

        // Validate optional reason (defaults to a standard message if not provided)
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $reason = trim($validated['reason'] ?? '') ?: 'Violation of community guidelines.';

        $user->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => $reason,
            'blocked_at'   => now(),
        ]);

        // Invalidate all active sessions for this user
        $this->invalidateUserSessions($user);

        // Send notification email
        try {
            Mail::to($user->email)->send(new AccountRestrictionMail($user, 'blocked', $reason));
        } catch (\Exception $e) {
            // Email failure should not block the admin action
        }

        return back()->with('success', $user->name . ' has been blocked.');
    }

    /**
     * Unblock a user account.
     */
    public function unblock(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        if ($user->is_admin) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Admin accounts cannot be modified.'], 422);
            }
            return back()->with('error', 'Admin accounts cannot be modified.');
        }

        $user->update([
            'status'       => 'active',
            'is_blocked'   => false,
            'block_reason' => null,
            'blocked_at'   => null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            $userCount    = User::where('is_admin', false)->whereNull('archived_at')->count();
            $blockedCount = User::where('is_admin', false)->whereNull('archived_at')->where(function ($q) {
                $q->whereIn('status', ['blocked', 'suspended', 'restricted'])->orWhere('is_blocked', true);
            })->count();
            $archiveCount = User::where('is_admin', false)->whereNotNull('archived_at')->count();

            return response()->json([
                'success'       => true,
                'message'       => $user->name . ' has been unblocked.',
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'user_count'    => $userCount,
                'blocked_count' => $blockedCount,
                'archive_count' => $archiveCount,
            ]);
        }

        return back()->with('success', $user->name . ' has been unblocked.');
    }

    /**
     * Soft-delete (archive) a user account.
     */
    public function archive(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        // Self-protection
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        // Admin-protection
        if ($user->is_admin) {
            return back()->with('error', 'Admin accounts cannot be removed.');
        }

        // Validate optional reason
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $reason = trim($validated['reason'] ?? '') ?: 'Violation of community guidelines.';

        $user->update([
            'archived_at'  => now(),
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => $reason,
            'blocked_at'   => now(),
        ]);

        // Invalidate sessions
        $this->invalidateUserSessions($user);

        // Send notification email
        try {
            Mail::to($user->email)->send(new AccountRestrictionMail($user, 'archived', $reason));
        } catch (\Exception $e) {
            // Email failure should not block the admin action
        }

        return back()->with('success', $user->name . '\'s account has been moved to the archive.');
    }

    /**
     * Restore an archived user account.
     */
    public function restore(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        if ($user->is_admin) {
            return back()->with('error', 'Admin accounts cannot be modified.');
        }

        $user->update([
            'archived_at'  => null,
            'status'       => 'active',
            'is_blocked'   => false,
            'block_reason' => null,
            'blocked_at'   => null,
        ]);

        return back()->with('success', $user->name . '\'s account has been restored.');
    }

    /**
     * Permanently delete a user account from the database.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403, 'Unauthorized access to the Admin Portal.');

        // Self-protection
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot permanently delete your own account.');
        }

        // Admin-protection
        if ($user->is_admin) {
            return back()->with('error', 'Admin accounts cannot be deleted.');
        }

        // Validate optional reason
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $reason = trim($validated['reason'] ?? '') ?: 'Account permanently removed by administrator.';

        $userName  = $user->name;
        $userEmail = $user->email;

        // Cascade: invalidate sessions before deletion
        $this->invalidateUserSessions($user);

        // Send notification email BEFORE deletion (so we still have the email)
        try {
            // Create a temporary object for the email since the user will be deleted
            $userCopy = clone $user;
            $userCopy->name = $userName;
            Mail::to($userEmail)->send(new AccountRestrictionMail($userCopy, 'deleted', $reason));
        } catch (\Exception $e) {
            // Email failure should not block the admin action
        }

        $user->delete();

        return back()->with('success', $userName . '\'s account has been permanently deleted.');
    }

    /**
     * Invalidate all active sessions for a given user by cycling their remember token.
     * This forces re-authentication on next request.
     */
    protected function invalidateUserSessions(User $user): void
    {
        // Cycle the remember_token to invalidate "remember me" cookies
        $user->forceFill(['remember_token' => null])->save();

        // For session-based auth: delete the user's session records from the sessions table
        // (if using database sessions driver). We guard gracefully if the table doesn't exist.
        try {
            \DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        } catch (\Exception $e) {
            // Sessions table may not exist (e.g. file driver) — silently ignore
        }
    }
}
