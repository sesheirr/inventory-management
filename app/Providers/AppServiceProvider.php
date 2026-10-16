<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        if (! $this->app->runningInConsole()) {
            /** @var Request $request */
            $request = $this->app->make('request');
            $forwardedProto = strtolower((string) $request->header('X-Forwarded-Proto', ''));
            $cfVisitor = strtolower((string) $request->header('CF-Visitor', ''));
            $host = strtolower((string) $request->getHost());

            $isTunnelOrHttps = $request->isSecure()
                || str_contains($forwardedProto, 'https')
                || str_contains($cfVisitor, '"https"')
                || str_ends_with($host, '.trycloudflare.com')
                || str_ends_with($host, '.ngrok-free.app')
                || str_ends_with($host, '.loca.lt');

            if ($isTunnelOrHttps) {
                URL::forceScheme('https');
                config(['session.secure' => true]);
            }

            $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
            if ((! $isLocalHost || $isTunnelOrHttps) && is_file(public_path('build/manifest.json'))) {
                Vite::useHotFile(storage_path('framework/vite.hot.disabled'));
            }
        }

        Gate::define('manage-users', function (User $user): bool {
            return $user->isSuperAdmin();
        });

        Gate::define('update-user-role', function (User $user): bool {
            return $user->isSuperAdmin();
        });
    }
}
