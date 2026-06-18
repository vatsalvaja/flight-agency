<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user_id')) {
            return redirect('/')->with('auth_error', 'Please log in to access the control center.');
        }

        $user = User::find($request->session()->get('user_id'));

        if (!$user) {
            $request->session()->forget('user_id');
            return redirect('/')->with('auth_error', 'User session invalid.');
        }

        if ($user->status != 0) {
            $request->session()->forget('user_id');
            return redirect('/')->with('auth_error', 'Your account is inactive.');
        }

        // Share logged-in user globally with views
        view()->share('loggedUser', $user);

        // Path authorization check
        $path = $request->path(); // e.g. admin/companies
        
        // Admin (role_id === 0) has access to everything
        if ($user->role_id === 0) {
            return $next($request);
        }

        // Manager checks
        $isManager = false;
        if ($user->role_id > 0 && $user->role) {
            $isManager = (stripos($user->role->role_name, 'manager') !== false);
        }

        // Driver checks
        $isDriver = false;
        if ($user->role_id > 0 && $user->role) {
            $isDriver = (stripos($user->role->role_name, 'driver') !== false);
        }

        // Common paths allowed for all authenticated roles
        $allowedCommonPaths = [
            'admin',
            'logout',
            'admin/profile',
            'admin/account-settings',
            'admin/change-password'
        ];

        // Restrict paths
        if ($isManager) {
            if (in_array($path, $allowedCommonPaths) || 
                $path === 'admin/assign-luggage' || 
                str_starts_with($path, 'admin/assign-luggage/') ||
                $path === 'admin/driver-activities' ||
                $path === 'admin/reports') {
                return $next($request);
            }
            return redirect('/admin')->with('error', 'Unauthorized access.');
        }

        if ($isDriver) {
            if (in_array($path, $allowedCommonPaths) || 
                $path === 'admin/assignable-orders' || 
                str_starts_with($path, 'admin/assignable-orders/') ||
                $path === 'admin/reports') {
                return $next($request);
            }
            return redirect('/admin')->with('error', 'Unauthorized access.');
        }

        // Default deny if role is undefined or not allowed
        return redirect('/')->with('auth_error', 'Unauthorized role access.');
    }
}
