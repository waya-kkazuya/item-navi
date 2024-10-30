<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class LogRequestDetails
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $sessionId = Session::getId();
        $hashedSessionId = Hash::make($sessionId); // セッションIDをハッシュ化
        $url = $request->fullUrl(); 

        if ($user) {
            Log::withContext([
                'user_id' => $user->id, 
                'session_id' => $hashedSessionId,
                'url' => $url
            ]);
        } else {
            Log::withContext([
                'user_id' => 'guest', 
                'session_id' => $hashedSessionId,
                'url' => $url
            ]);
        }

        return $next($request);
    }
}
