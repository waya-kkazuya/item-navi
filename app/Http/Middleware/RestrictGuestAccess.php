<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RestrictGuestAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role == 0) {
            Log::info("RestrictGuestAccessミドルウェア");

            if ($request->expectsJson()) {
                Log::info("RestrictGuestAccessミドルウェア:APIリクエスト");
                return response()->json([
                    'message' => 'ゲストには許可されていない機能です、ログインして実行してください',
                    'status'  => 'danger',
                ], 403);
            }

            return redirect()->back()
                ->with([
                    'message' => 'ゲストには許可されていない機能です、ログインして実行してください',
                    'status'  => 'danger',
                ]);
        }
        return $next($request);
    }
}
