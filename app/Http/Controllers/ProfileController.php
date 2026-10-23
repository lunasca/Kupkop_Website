<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ];

        // If user wants to change password, require the current password
        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $messages = [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.confirmed'                => 'The new password confirmation does not match.',
            'password.min'                      => 'The new password must be at least 6 characters.',
        ];

        $validated = $request->validate($rules, $messages);

        $oldName = $user->name;
        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Keep posted_by on user's active pets in sync
        if ($oldName !== $user->name) {
            $user->pets()->update(['posted_by' => $user->name]);
        }

        return back()->with('success', 'Your account details have been updated successfully!');
    }
}
