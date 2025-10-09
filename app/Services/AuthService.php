<?php

namespace App\Services;

use App\Interfaces\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService implements AuthServiceInterface
{
    public function login(array $request)
    {
        if (!Auth::attempt($request)) {
            return false;
        }

        $user = User::where('username', $request['username'])->first();

        if (!$user) {
            return false;
        }

        $token = $user->createToken("API ACCESS TOKEN")->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];
    }
    public function register(array $request)
    {
        $user = User::create($request);
        if (!$user) {
            return false;
        }

        return $user;
    }
    public function logout()
    {
        Auth::user()->tokens()->delete();
        return true;
    }
}
