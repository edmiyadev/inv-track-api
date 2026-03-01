<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Interfaces\AuthServiceInterface;

class AuthController extends Controller
{
    private readonly AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $resp = $this->authService->login($request->validated());

        if (! $resp) {
            return response(['message' => 'Credenciales incorrectas'], 402);
        }

        return response([
            'status' => 'success',
            'message' => 'loging success',
            'data' => $resp,
        ]);

    }

    public function register(RegisterRequest $request)
    {
        $resp = $this->authService->register($request->validated());

        if (! $resp) {
            return response(['message' => 'Error creating user'], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'user created',
            'data' => $resp,
        ], 201);
    }

    public function logout()
    {
        $this->authService->logout();

        return response([
            'status' => 'success',
            'message' => 'logout success',
        ]);
    }
}
