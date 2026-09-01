<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::define('manage-users', function (User $user): bool {
            return $user->isSuperAdmin();
        });

        Gate::define('update-user-role', function (User $user): bool {
            return $user->isSuperAdmin();
        });
    }
}
