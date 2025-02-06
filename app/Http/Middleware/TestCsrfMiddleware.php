<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class TestCsrfMiddleware
{
    public function handle($request, Closure $next)
    {
        if (app()->environment('testing')) {
            $csrfToken = csrf_token(); //同じセッション内なら同じ値を返す
            $request->headers->set('X-CSRF-TOKEN', $csrfToken);
            Session::put('_token', $csrfToken);
            \Log::info('CSRFトークン: ' . $csrfToken); // デバッグ用のログ出力
        }

        return $next($request);
    }
}
