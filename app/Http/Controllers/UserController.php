<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolesRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use App\Traits\Authorizes;

class UserController extends Controller
{
    use Authorizes;

    protected readonly UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->getAllUsers();
        return response([
            "status" => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = $this->userService->createUser($request->validated());

        if (!$user) {
            return response([
                "status" => 'error',
                'message' => 'Error creating user'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $userId)
    {
        $user = $this->userService->getUserById($userId);
        $this->authorize('view', $user);

        if (!$user) {
            return response([
                "status" => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response([
            "status" => 'success',
            'message' => 'User retrieved successfully',
            'data' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, int|string $userId)
    {
        $user = $this->userService->getUserById($userId);
        $this->authorize('update', $user);

        if (!$user) {
            return response([
                "status" => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $userUpdated = $this->userService->updateUser($user, $request->validated());

        if (!$userUpdated) {
            return response([
                "status" => 'error',
                'message' => 'Error updating user'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int|string $userId)
    {
        $user = $this->userService->getUserById($userId);
        $this->authorize('delete', $user);

        if (!$user) {
            return response([
                "status" => 'error',
                'message' => 'User not found'
            ], 404);
        }

        if (!$this->userService->deleteUser($user)) {
            return response([
                "status" => 'error',
                'message' => 'Error deleting user'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    public function syncRoles(RolesRequest $request, int|string $userId)
    {
        $user = $this->userService->getUserById($userId);

        if (empty($request->roles)) {
            return response([
                "status" => 'success',
                'message' => 'Roles updated successfully (no roles provided)'
            ], 200);
        }

        $this->authorize('syncRoles', $request->validated());

        if (!$user) {
            return response([
                "status" => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $userUpdated = $this->userService->syncRoles($user, $request->validated());

        if (!$userUpdated) {
            return response([
                "status" => 'error',
                'message' => 'Error updating user roles'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'User roles updated successfully'
        ]);
    }
}
