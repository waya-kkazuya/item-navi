<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;
use App\Models\SystemNotification;

class NotificationController extends Controller
{
    public function index()
    {   
        $notifications = SystemNotification::all();

        // dd($notifications);
        // json形式を読み取れる形式に変換する
        $notifications = SystemNotification::all()->map(function ($notification) {
            $notification->data = json_decode($notification->data, true);
            return $notification;
        });

        // Notificaitonsテーブルのtypeカラムで分ける
        // $type = $request->query('type');
        // if ($type) {
        //     return Notification::where('type', $type)->get();
        // }


        return Inertia::render('Notification', [
            'notifications' => $notifications
        ]);


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

        return view('notifications.index', [
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
