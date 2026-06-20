<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $permission)
{
    // kama unataka ruhusu wote kwa sasa
    // unaweza kuweka true temporarily

    if (auth()->check()) {

        // hapa unaweza baadaye kuweka logic ya DB
        return $next($request);
    }

    abort(403, 'Unauthorized');
}
}
