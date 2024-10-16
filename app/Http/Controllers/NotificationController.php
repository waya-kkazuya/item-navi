<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {   
        // ログイン中のユーザーに向けた通知を取得
        // 情報がない場合、nullではなく空のコレクションとして扱われる
        // $notifications = Auth::user()->notifications; ログイン中の通知
        // $notifications = SystemNotification::all(); 全ての通知

        $notifications = Auth::user()->notifications->map(function ($notification) {
            $notification->id = (string) $notification->id; // UUIDを文字列として扱う、明示的に文字列としないと挙動がおかしくなる
            $notification->relative_time = Carbon::parse($notification->created_at)->diffForHumans(); // 相対的な時間を追加
            
            return $notification;
        });
        
        // typeカラムでフィルターする 
        // 在庫数が少なくなっている通知
        $lowStockNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\LowStockNotification';
        });


        // 廃棄の予定日が近づいている通知
        $disposalScheduleNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\DisposalScheduleNotification';
        });
        // 点検の予定日が近づいている通知
        $inspectionScheduleNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\InspectionScheduleNotification';
        });
        // disposalScheduleNotificationsとinspectionScheduleNotificationsを1つの配列にまとめる
        $disposalAndInspectionNotifications = $disposalScheduleNotifications->merge($inspectionScheduleNotifications);

        
        // リクエストが追加されたときの通知
        $requestedItemNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\RequestedItemNotification';
        });

        return Inertia::render('Notification', [
            'notifications' => $notifications,
            'lowStockNotifications' => $lowStockNotifications,
            'disposalAndInspectionNotifications' => $disposalAndInspectionNotifications,
            'requestedItemNotifications' => $requestedItemNotifications,
        ]);
    }

    // 既読にするためのAPIエンドポイント
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            return response()->noContent(); // 204 レスポンスにコンテンツ含まれないｓ
        }

        return response()->json(['status' => 'error'], 404);
    }
}
