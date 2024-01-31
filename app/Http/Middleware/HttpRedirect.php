<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;


class HttpRedirect
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
        return $next($request);
        if ($request->headers->has('X-Forwarded-Proto')) {
            if (strcmp($request->header('X-Forwarded-Proto'), 'https') === 0) {
                return $next($request);
            }
        }

        if (!$request->secure() && config('app.env') == 'production') {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}