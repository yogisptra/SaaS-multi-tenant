<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $users = User::paginate(15);
        return $this->successResponse('Users retrieved successfully', $users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'member'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['company_id'] = app(\App\Services\TenantService::class)->getCompanyId();

        $user = User::create($validated);

        return $this->successResponse('User created successfully', $user, 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);
        return $this->successResponse('User retrieved successfully', $user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'role' => ['sometimes', Rule::in(['admin', 'member'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $this->successResponse('User updated successfully', $user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();
        return $this->successResponse('User deleted successfully');
    }
}
