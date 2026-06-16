<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function showLanding()
    {
        if (session()->has('user_id')) {
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

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->role_id != 0) {
                // For dynamic roles (Manager, Driver), verify if the role is active in the roles table
                if ($user->role && $user->role->status != 0) {
                    return redirect()->back()
                        ->withInput($request->only('email'))
                        ->withErrors(['login_error' => 'Your role has been deactivated.'])
                        ->with('auth_error', 'Your role has been deactivated.');
                }
            }

            if ($user->status != 0) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['login_error' => 'Your account is inactive.'])
                    ->with('auth_error', 'Your account is inactive.');
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            session(['user_id' => $user->id]);
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

        return redirect()->back()->with('success', 'Registration successful! You can now log in using your credentials.');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        session()->forget('user_id');
        return redirect('/')->with('success', 'Logged out successfully.');
    }
}
