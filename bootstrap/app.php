<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\InsufficientStockException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Using stateless Bearer token authentication — no CSRF or session middleware needed.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InsufficientStockException $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'STOCK_INSUFFICIENT',
                'message' => 'No hay stock suficiente para completar la operación',
                'errors' => [
                    'items' => $e->items(),
                ],
            ], 422);
        });
    })->create();
