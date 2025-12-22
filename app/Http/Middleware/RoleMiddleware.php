<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $role = strtolower(trim((string) ($user->role ?? '')));

        // roles datang dari route: role:admin,coa,rm
        $allowed = array_map(fn ($r) => strtolower(trim($r)), $roles);

        if (!in_array($role, $allowed, true)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
