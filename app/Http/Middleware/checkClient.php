<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Response;

class checkClient
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
        if (Auth::user()->type == 1) {
            return $next($request);
        }
        return Response::json([
            "message" => "unauthorized"
        ], 401);
    }
}
