<?php

namespace App\Providers;

use App\Interfaces\AuthServiceInterface;
use App\Interfaces\SupplierServiceInterface;
use App\Models\Supplier;
use App\Policies\SupplierPolicy;
use App\Services\AuthService;
use App\Services\SupplierService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(SupplierServiceInterface::class, SupplierService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Supplier::class, SupplierPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
