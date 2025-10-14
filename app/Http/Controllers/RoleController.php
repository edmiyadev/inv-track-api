<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Interfaces\RoleServiceInterface;
use App\Traits\Authorizes;

class RoleController extends Controller
{
    use Authorizes;

    private readonly RoleServiceInterface $roleService;

    public function __construct(RoleServiceInterface $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        // $this->authorize('viewAny', Role::class);

        $roles = $this->roleService->getAllRoles();
        return response([
            "status" => 'success',
            'message' => 'Roles retrieved successfully',
            'data' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        // $this->authorize('create', Role::class);

        $role = $this->roleService->createRole($request->validated());

        if (!$role) {
            return response([
                "status" => 'error',
                'message' => 'Error creating role'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $roleId)
    {
        $role = $this->roleService->getRoleById($roleId);
        // $this->authorize('view', $role);

        if (!$role) {
            return response([
                "status" => 'error',
                'message' => 'Role not found'
            ], 404);
        }

        return response([
            "status" => 'success',
            'message' => 'Role retrieved successfully',
            'data' => $role
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $roleId)
    {
        $role = $this->roleService->getRoleById($roleId);

        // $this->authorize('update', $role);

        if (!$role) {
            return response([
                "status" => 'error',
                'message' => 'Role not found'
            ], 404);
        }

        $roleUpdated = $this->roleService->updateRole($role, $request->validated());

        if (!$roleUpdated) {
            return response([
                "status" => 'error',
                'message' => 'Error updating role'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Role updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $roleId)
    {
        $role = $this->roleService->getRoleById($roleId);
        // $this->authorize('delete', $role);
        if (!$role) {
            return response([
                "status" => 'error',
                'message' => 'Role not found'
            ], 404);
        }

        if (!$this->roleService->deleteRole($role)) {
            return response([
                "status" => 'error',
                'message' => 'Error deleting role'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Role deleted successfully'
        ]);
    }
}
