<?php

namespace App\Http\Middleware;

use Closure;

class IPfilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ip() == '127.0.0.1' || $request->ip() == '::1' || $request->ip() == 'localhost') {
            return $next($request);
            
        }

        abort(403);
    }
}
