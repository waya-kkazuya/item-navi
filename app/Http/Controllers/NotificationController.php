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
        // $notifications = Auth::user()->notifications;
        // $notifications = SystemNotification::all();

        // dd($notifications);
        // json形式を読み取れる形式に変換する
        $notifications = Auth::user()->notifications->map(function ($notification) {
            $notification->id = (string) $notification->id; // UUIDを文字列として扱う
            // $notification->data = json_decode($notification->data, true); // json形式を読み取れる形式に変換する
            $notification->relative_time = Carbon::parse($notification->created_at)->diffForHumans(); // 相対的な時間を追加

            return $notification;
        });
        // $notifications = SystemNotification::all()->map(function ($notification) {
        //     $notification->data = json_decode($notification->data, true);
        //     // diffForHumansで相対的な時間を追加、人間のためのという意味
        //     $notification->relative_time = Carbon::parse($notification->created_at)->diffForHumans();
            
        //     return $notification;
        // });


        
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
        // 廃棄予定日と点検予定日の通知のデータを一つにまとめる
        // disposalScheduleNotificationsとinspectionScheduleNotificationsを1つの配列にまとめる
        $disposalAndInspectionNotifications = $disposalScheduleNotifications->merge($inspectionScheduleNotifications);

        $requestedItemNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\RequestedItemNotification';
        });

        // dd($requestedItemNotifications);

        // 配列に変換する
        // $notificationsArray = $notifications->toArray();
        // $lowStockNotificationsArray = $lowStockNotifications->toArray();
        // $disposalAndInspectionNotificationsArray = $disposalAndInspectionNotifications->toArray();
        // $requestedItemNotificationsArray = $requestedItemNotifications->toArray();


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
        // dd($id);
        Log::info('コントローラーまで来ました');
        Log::info($id);

        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            return response()->noContent(); // 204 No Content
        }
        return response()->json(['status' => 'error'], 404);
    }
}
