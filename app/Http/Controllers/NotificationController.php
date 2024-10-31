<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {   
        Gate::authorize('staff-higher');

        Log::info('NotificationController index method called');

        // ログイン中のユーザーに向けた通知を取得
        // $notifications = Auth::user()->notifications; ログイン中の通知
        // $notifications = SystemNotification::all(); 全ての通知
        // ※情報がない場合、nullではなく空のコレクションとして扱われる

        $notifications = Auth::user()->notifications->map(function ($notification) {
            $notification->id = (string) $notification->id; // UUIDを文字列として扱う、明示的に文字列としないと挙動がおかしくなる
            $notification->relative_time = Carbon::parse($notification->created_at)->diffForHumans(); // 相対的な時間を追加
            
            return $notification;
        });
        
        // typeカラムでフィルターする 
        // 在庫数が少なくなっている通知
        $lowStockNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\LowStockNotification';
        })->take(20);

        // 廃棄の予定日が近づいている通知
        $disposalScheduleNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\DisposalScheduleNotification';
        });
        // 点検の予定日が近づいている通知
        $inspectionScheduleNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\InspectionScheduleNotification';
        });
        // disposalScheduleNotificationsとinspectionScheduleNotificationsを1つの配列にまとめる
        $disposalAndInspectionNotifications = $disposalScheduleNotifications->merge($inspectionScheduleNotifications)->take(20);
        
        // リクエストが追加されたときの通知
        $requestedItemNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\RequestedItemNotification';
        })->take(20);

        // タブの右上に表示する丸の判定基準のため、それぞれの未読の通知数を所得
        $unreadLowStockNotifications = $lowStockNotifications->where('read_at', null)->count();
        $unreadDisposalAndInspectionNotifications = $disposalAndInspectionNotifications->where('read_at', null)->count();
        $unreadRequestedItemNotifications = $requestedItemNotifications->where('read_at', null)->count();

        Log::info('NotificationController index method succeeded');

        return Inertia::render('Notification', [
            'notifications' => $notifications,
            'lowStockNotifications' => $lowStockNotifications,
            'disposalAndInspectionNotifications' => $disposalAndInspectionNotifications,
            'requestedItemNotifications' => $requestedItemNotifications,
            'unreadLowStockNotifications' => $unreadLowStockNotifications,
            'unreadDisposalAndInspectionNotifications' => $unreadDisposalAndInspectionNotifications,
            'unreadRequestedItemNotifications' => $unreadRequestedItemNotifications,
        ]);
    }

    // 既読にするためのAPIエンドポイント
    public function markAsRead($id)
    {
        Gate::authorize('staff-higher');

        Log::info('NotificationController API markAsRead method called');

        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            return response()->noContent(); // 204 レスポンスにコンテンツ含まれないｓ
        }

        return response()->json(['status' => 'error'], 404);
    }
}
