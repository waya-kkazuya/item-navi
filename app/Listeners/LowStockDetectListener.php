<?php

namespace App\Listeners;

use App\Events\LowStockDetectEvent;
use App\Models\User;
use App\Notifications\LowStockNotification;

class LowStockDetectListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LowStockDetectEvent $event): void
    {
        // UpdateStcokController@decreaseStockで条件を満たした場合、イベントは発火される
        // 在庫数が一定数以下になったときの通知処理

        // user権限以外の全てのユーザーに通知する場合
        $users = User::whereIn('role', [1, 5])->get(); // roleが1（admin）または5（staff）のユーザーを取得
        foreach ($users as $user) {
            $user->notify(new LowStockNotification($event->item));
        }
    }
}
