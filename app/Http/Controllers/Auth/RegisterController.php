<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the registration form (fallback if accessed directly).
     */
    public function showRegistrationForm(): View
    {
        return view('pages.auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $email = strtolower(trim($validated['email']));
        $name = trim($validated['name']);

        // Create the user
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
        ]);

        // Automatically log them in after registration
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('auth_success', 'Account created successfully! Welcome to Kupkop, ' . $user->name . '.');
    }
}
