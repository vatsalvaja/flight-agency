<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('role')->orderBy('id', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('status', 0)->orderBy('role_name', 'asc')->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|integer', // Can be 0 for admin, or point to roles
            'status' => 'required|in:0,1',
        ]);

        // If role_id is not 0, verify role exists in the roles table
        if ($validated['role_id'] != 0) {
            $roleExists = Role::where('id', $validated['role_id'])->exists();
            if (!$roleExists) {
                return redirect()->back()->withInput()->withErrors(['role_id' => 'Selected role is invalid.']);
            }
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::where('status', 0)->orderBy('role_name', 'asc')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        if ($validated['role_id'] != 0) {
            $roleExists = Role::where('id', $validated['role_id'])->exists();
            if (!$roleExists) {
                return redirect()->back()->withInput()->withErrors(['role_id' => 'Selected role is invalid.']);
            }
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting oneself
        if ($user->id === session('user_id')) {
            return redirect()->route('users.index')->with('error', 'Cannot delete the currently logged in user.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
