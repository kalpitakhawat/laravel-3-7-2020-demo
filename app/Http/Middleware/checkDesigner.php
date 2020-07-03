<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Response;

class checkDesigner
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
        if (Auth::user()->type == 2) {
            return $next($request);
        }
        return Response::json([
            "message" => "unauthorized"
        ], 401);
    }
}
