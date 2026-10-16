<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya bisa diakses oleh Super Admin.');
        }

        return $next($request);
    }
}
