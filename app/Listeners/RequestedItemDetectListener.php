<?php

namespace App\Listeners;

use App\Events\RequestedItemDetectEvent;
use App\Notifications\RequestedItemNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;

class RequestedItemDetectListener
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
    public function handle(RequestedItemDetectEvent $event): void
    {
        // user権限以外の全てのユーザーに通知する場合
        $users = User::whereIn('role', [1, 5])->get(); // roleが1（admin）または5（staff）のユーザーを取得
        foreach ($users as $user) {
            $user->notify(new RequestedItemNotification($event->itemRequest));
        }
    }
}
