<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Batasi akses route ke role tertentu.
     *
     * @param  string  ...$roles  Nilai dari `UserRole` (mis. `admin`, `technician`).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $role = $user->role instanceof UserRole ? $user->role->value : $user->role;

        if (! in_array($role, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk tindakan ini.');
        }

        return $next($request);
    }
}
