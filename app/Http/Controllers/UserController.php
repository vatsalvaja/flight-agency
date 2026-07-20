<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.users.index');
    }

    public function list()
    {
        $users = User::with('role')->orderBy('id', 'desc')->get()->map(function (User $user) {
            return $this->formatUser($user);
        });

        return response()->json([
            'success' => true,
            'message' => 'Users loaded successfully.',
            'data' => $users,
        ]);
    }

    public function getDataById(User $user)
    {
        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'User loaded successfully.',
            'data' => $this->formatUser($user),
        ]);
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
        return $this->save($request);
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
     * Redirect direct resource show requests back to the AJAX listing.
     */
    public function show(User $user)
    {
        return redirect()->route('users.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->merge(['id' => $user->id]);

        return $this->save($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting oneself
        if ($user->id === session('user_id')) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the currently logged in user.',
                    'data' => null,
                ], 422);
            }

            return redirect()->route('users.index')->with('error', 'Cannot delete the currently logged in user.');
        }

        $user->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
                'data' => null,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function save(Request $request)
    {
        $user = $request->filled('id') ? User::find($request->input('id')) : null;

        if ($request->filled('id') && ! $user) {
            return $this->userErrorResponse($request, 'User not found.', 404);
        }

        $validator = Validator::make($request->all(), $this->validationRules($user?->id));

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check the form errors below.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        if (! $this->isDriverRoleId((int) $validated['role_id'])) {
            $validated['license_number'] = null;
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($user) {
            $user->update($validated);
            $message = 'User updated successfully.';
        } else {
            $user = User::create($validated);
            $message = 'User created successfully.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatUser($user->fresh('role')),
            ]);
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    private function validationRules(?int $userId = null): array
    {
        $selectedRoleId = (int) request()->input('role_id');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ($userId ? 'nullable' : 'required') . '|string|min:6',
            'phone' => 'nullable|string|max:20',
            'role_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ((int) $value === 0) {
                        return;
                    }

                    if (! Role::where('id', $value)->where('status', 0)->exists()) {
                        $fail('Selected role is invalid.');
                    }
                },
            ],
            'license_number' => [
                Rule::requiredIf($this->isDriverRoleId($selectedRoleId)),
                'nullable',
                'string',
                'max:255',
            ],
            'status' => 'required|in:0,1',
        ];
    }

    private function formatUser(User $user): array
    {
        $roleName = ((int) $user->role_id === 0) ? 'Admin (System)' : ($user->role->role_name ?? 'N/A');
        $isDriver = $this->isDriverRoleName($roleName);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'license_number' => $user->license_number,
            'role_id' => $user->role_id,
            'role_name' => $roleName,
            'is_driver' => $isDriver,
            'status' => (string) $user->status,
            'initials' => $user->getInitials(),
            'edit_url' => route('users.edit', $user->id),
            'delete_url' => route('users.destroy', $user->id),
            'data_url' => route('users.data', $user->id),
            'created_at' => $user->created_at ? $user->created_at->format('M d, Y') : null,
            'updated_at' => $user->updated_at ? $user->updated_at->format('M d, Y h:i A') : null,
        ];
    }

    private function isDriverRoleId(int $roleId): bool
    {
        if ($roleId === 0) {
            return false;
        }

        $roleName = Role::where('id', $roleId)->value('role_name');

        return $this->isDriverRoleName($roleName);
    }

    private function isDriverRoleName(?string $roleName): bool
    {
        return $roleName !== null && strcasecmp(trim($roleName), 'Driver') === 0;
    }

    private function userErrorResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], $status);
        }

        return redirect()->route('users.index')->with('error', $message);
    }
}
