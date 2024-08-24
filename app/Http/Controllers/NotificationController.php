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
        $notifications = Auth::user()->notifications;
        // $notifications = SystemNotification::all();

        // これは1人のユーザーの情報か？？

        // dd($notifications);
        // json形式を読み取れる形式に変換する
        $notifications = SystemNotification::all()->map(function ($notification) {
            $notification->data = json_decode($notification->data, true);
            // diffForHumansで相対的な時間を追加、人間のためのという意味
            $notification->relative_time = Carbon::parse($notification->created_at)->diffForHumans();
            
            return $notification;
        });

        // Notificaitonsテーブルのtypeカラムで分ける
        // $type = $request->query('type');
        // if ($type) {
        //     return Notification::where('type', $type)->get();
        // }
        
        //typeカラムでフィルターする 

        $lowStockNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\LowStockNotification';
        });

        $disposalScheduleNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\DisposalScheduleNotification';
        });

        $inspectionScheduleNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\InspectionScheduleNotification';
        });

        $requestedItemNotifications = $notifications->filter(function ($notification) {
            return $notification->type === 'App\Notifications\RequestedItemNotification';
        });

        return Inertia::render('Notification', [
            'notifications' => $notifications,
            'lowStockNotifications' => $lowStockNotifications,
            'disposalScheduleNotifications' => $disposalScheduleNotifications,
            'inspectionScheduleNotifications' => $inspectionScheduleNotifications,
            'requestedItemNotifications' => $requestedItemNotifications,
        ]);


        // // API通信
        // return [
        //     'notifications' => SystemNotification::all()
        // ];
    }
}
