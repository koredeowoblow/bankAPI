<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   // app/Http/Middleware/CheckStaffPermission.php

public function handle($request, Closure $next, $permission)
{
    $staff = auth()->user()->staff;

    if (!$staff || !$staff->roles()->whereHas('permissions', fn ($q) => $q->where('name', $permission))->exists()) {
        abort(403, 'Unauthorized.');
    }

    return $next($request);
}

}
