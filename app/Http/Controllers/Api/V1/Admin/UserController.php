<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('roles')
            ->when($request->role, fn($q) => $q->role($request->role))
            ->when($request->search, fn($q) => $q
                ->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('last_name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->is_active))
            ->orderBy('last_name')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'employee_id' => 'nullable|string|unique:users,employee_id',
            'student_id' => 'nullable|string|unique:users,student_id',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'birthdate' => 'nullable|date',
            'is_active' => 'boolean',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            ...$validated,
            'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);
        $user->load('roles');

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'student_id' => $user->student_id,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'birthdate' => $user->birthdate,
                'is_active' => $user->is_active,
                'roles' => $user->getRoleNames(),
            ],
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'student_id' => $user->student_id,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'birthdate' => $user->birthdate,
                'is_active' => $user->is_active,
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => ['sometimes', Password::min(8)],
            'employee_id' => 'nullable|string|unique:users,employee_id,' . $user->id,
            'student_id' => 'nullable|string|unique:users,student_id,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'birthdate' => 'nullable|date',
            'is_active' => 'boolean',
            'role' => 'sometimes|string|exists:roles,name',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['first_name']) || isset($validated['last_name'])) {
            $validated['name'] = trim(
                ($validated['first_name'] ?? $user->first_name) . ' ' .
                ($validated['last_name'] ?? $user->last_name)
            );
        }

        $user->update($validated);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        $user->load('roles');

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'is_active' => $user->is_active,
            ],
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
