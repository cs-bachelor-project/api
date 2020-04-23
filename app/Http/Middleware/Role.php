<?php

namespace App\Http\Middleware;

use Closure;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (auth()->check()) {
            if ($request->user()->hasAnyRole($roles)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
    }
}
