<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya bisa diakses oleh Super Admin.');
        }

        return $next($request);
    }
}
