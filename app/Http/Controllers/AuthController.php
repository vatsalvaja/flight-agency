<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function showLanding()
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        return view('landing');
    }

    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            if ($user->role_id != 1) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['login_error' => 'Access denied. Administrator privileges required.'])
                    ->with('auth_error', 'Access denied. Administrator privileges required.');
            }

            if ($user->status != 0) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['login_error' => 'Your account is inactive.'])
                    ->with('auth_error', 'Your account is inactive.');
            }

            session(['admin_logged_in' => true]);
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back to Wings Control Center.');
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['login_error' => 'Invalid email or password.'])
            ->with('auth_error', 'Invalid email or password.');
    }

    /**
     * Handle registration (demo placeholder).
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        return redirect()->back()->with('success', 'Registration successful! You can now log in using the admin credentials.');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        session()->forget('admin_logged_in');
        return redirect('/')->with('success', 'Logged out successfully.');
    }
}
