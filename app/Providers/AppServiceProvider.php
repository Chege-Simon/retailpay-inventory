<?php

namespace App\Providers;

use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Store;
use App\Policies\InventoryPolicy;
use App\Policies\SalePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(Store::class, SalePolicy::class);
        Gate::policy(Inventory::class, InventoryPolicy::class);
        Gate::policy(Store::class, InventoryPolicy::class);
    }
}
