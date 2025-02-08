<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    // 既読にするためのAPIエンドポイント
    public function markAsRead($id)
    {
        Gate::authorize('staff-higher');

        Log::info('NotificationController API markAsRead method called');

        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            return response()->noContent(); // No Content=204レスポンス、レスポンスにコンテンツが含まれない
        }

        return response()->json(['status' => 'error'], 404);
    }
}
