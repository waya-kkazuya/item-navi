<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class TestCsrfMiddleware
{
    public function handle($request, Closure $next)
    {
        if (app()->environment('testing')) {
            $request->headers->set('X-CSRF-TOKEN', csrf_token());
            Session::put('_token', csrf_token());
        }

        return $next($request);
    }
}
