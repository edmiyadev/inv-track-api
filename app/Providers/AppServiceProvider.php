<?php

namespace App\Providers;

use App\Interfaces\AuthServiceInterface;
use App\Interfaces\InventoryMovementServiceInterface;
use App\Interfaces\InventoryStockServiceInterface;
use App\Interfaces\PermissionServiceInterface;
use App\Interfaces\ProductCategoryServiceInterface;
use App\Interfaces\ProductServiceInterface;
use App\Interfaces\PurchaseServiceInterface;
use App\Interfaces\RoleServiceInterface;
use App\Interfaces\SupplierServiceInterface;
use App\Interfaces\UserServiceInterface;
use App\Interfaces\WarehouseServiceInterface;
use App\Models\Supplier;
use App\Policies\SupplierPolicy;
use App\Services\AuthService;
use App\Services\InventoryMovementService;
use App\Services\InventoryStockService;
use App\Services\PermissionService;
use App\Services\ProductCategoryService;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\RoleService;
use App\Services\SupplierService;
use App\Services\UserService;
use App\Services\WarehouseService;
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
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(ProductCategoryServiceInterface::class, ProductCategoryService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(WarehouseServiceInterface::class, WarehouseService::class);
        $this->app->bind(InventoryStockServiceInterface::class, InventoryStockService::class);
        $this->app->bind(InventoryMovementServiceInterface::class, InventoryMovementService::class);
        $this->app->bind(PurchaseServiceInterface::class, PurchaseService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The policies are self-discovering from Laravel.
        // Gate::policy(Supplier::class, SupplierPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
