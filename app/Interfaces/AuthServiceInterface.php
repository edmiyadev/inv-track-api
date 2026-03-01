<?php

namespace App\Interfaces;

interface AuthServiceInterface
{
    public function login(array $request);

    public function register(array $request);

    public function logout();
}
