<?php

namespace App\Listeners;

use App\Events\LowStockDetectEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        // dd($event->greet);

        if ($event->item->stocks < 100) {
            dd('100下回りました！');
            
            // 在庫数が一定数以下になったときの通知処理
            // 通知方法は、画面表示、メール送信、Slack通知など、要件に応じて適切なものを選択します
            
            // $user = User::find(1); // 通知を受け取るユーザーを取得
            // $user->notify(new InventoryLowNotification($event->inventoryItem));
            
            // 全てのユーザーに通知する場合
            $users = User::all(); // すべてのユーザーを取得
            foreach ($users as $user) {
                $user->notify(new InventoryLowNotification($event->inventoryItem));
            }
        }
    }
}
