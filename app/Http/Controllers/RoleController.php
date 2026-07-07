<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.roles.index');
    }

    public function list()
    {
        $roles = Role::withCount('users')->orderBy('id', 'desc')->get()->map(function (Role $role) {
            return $this->formatRole($role);
        });

        return response()->json([
            'success' => true,
            'message' => 'Roles loaded successfully.',
            'data' => $roles,
        ]);
    }

    public function getDataById(Role $role)
    {
        $role->loadCount('users');

        return response()->json([
            'success' => true,
            'message' => 'Role loaded successfully.',
            'data' => $this->formatRole($role),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function save(Request $request)
    {
        $role = $request->filled('id') ? Role::find($request->input('id')) : null;

        if ($request->filled('id') && ! $role) {
            return $this->roleErrorResponse($request, 'Role not found.', 404);
        }

        $validator = Validator::make($request->all(), $this->validationRules($role?->id));

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

        if ($role) {
            $role->update($validated);
            $message = 'Role updated successfully.';
        } else {
            $role = Role::create($validated);
            $message = 'Role created successfully.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            $role->loadCount('users');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatRole($role->fresh()->loadCount('users')),
            ]);
        }

        return redirect()->route('roles.index')->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Redirect direct resource show requests back to the AJAX listing.
     */
    public function show(Role $role)
    {
        return redirect()->route('roles.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->merge(['id' => $role->id]);

        return $this->save($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting active system roles if in use
        if ($role->users()->count() > 0) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role because it is assigned to existing users.',
                    'data' => null,
                ], 422);
            }

            return redirect()->route('roles.index')->with('error', 'Cannot delete role because it is assigned to existing users.');
        }

        $role->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
                'data' => null,
            ]);
        }

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    private function validationRules(?int $roleId = null): array
    {
        return [
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'role_name')->ignore($roleId),
            ],
            'status' => 'required|in:0,1',
        ];
    }

    private function formatRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'role_name' => $role->role_name,
            'status' => (string) $role->status,
            'users_count' => $role->users_count ?? $role->users()->count(),
            'edit_url' => route('roles.edit', $role->id),
            'delete_url' => route('roles.destroy', $role->id),
            'data_url' => route('roles.data', $role->id),
            'created_at' => $role->created_at ? $role->created_at->format('M d, Y') : null,
            'updated_at' => $role->updated_at ? $role->updated_at->format('M d, Y h:i A') : null,
        ];
    }

    private function roleErrorResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], $status);
        }

        return redirect()->route('roles.index')->with('error', $message);
    }
}
