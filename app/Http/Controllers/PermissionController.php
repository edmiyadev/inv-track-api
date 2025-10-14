<?php

namespace App\Http\Controllers;

use App\Interfaces\PermissionServiceInterface;

class PermissionController extends Controller
{

    private readonly PermissionServiceInterface $permissionService;
    /**
     * Display a listing of the resource.
     */

    public function __construct(PermissionServiceInterface $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();

        return response([
            "status" => 'success',
            'message' => 'Permissions retrieved successfully',
            'data' => $permissions
        ]);
    }
}
