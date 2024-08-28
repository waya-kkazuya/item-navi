<?php

namespace App\Listeners;

use App\Events\LowStockDetectEvent;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;

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
        // UpdateStcokControllerで条件を満たしてからイベントは発火される
        
        // 在庫数が一定数以下になったときの通知処理
        // 通知方法は、1,画面表示 2,LINEでの通知
        
        // $user = User::find(1); // 通知を受け取るユーザーを取得
        // $user->notify(new InventoryLowNotification($event->inventoryItem));
        
        // user権限以外の全てのユーザーに通知する場合
        $users = User::whereIn('role', [1, 5])->get(); // roleが1（admin）または5（staff）のユーザーを取得
        foreach ($users as $user) {
            $user->notify(new LowStockNotification($event->item));
        }
    }
}
