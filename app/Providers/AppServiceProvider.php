<?php

namespace App\Providers;

use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin() && str_starts_with($ability, 'platform.')) {
                return true;
            }
        });

        // Role Gates
        Gate::define('owner', fn (User $user) => app(TenantContext::class)->isOwner());
        Gate::define('admin', fn (User $user) => app(TenantContext::class)->isAdmin() || app(TenantContext::class)->isOwner());
        Gate::define('technician', fn (User $user) => app(TenantContext::class)->isTechnician() || app(TenantContext::class)->isOwner());

        // Permission Gates
        Gate::define('manage-finances', fn (User $user) => $user->hasPermission('finances.view'));
        Gate::define('manage-store', fn (User $user) => $user->hasPermission('store.settings'));
        Gate::define('manage-technicians', fn (User $user) => $user->hasPermission('technicians.manage'));
        Gate::define('view-reports', fn (User $user) => $user->hasPermission('reports.view'));
    }
}
