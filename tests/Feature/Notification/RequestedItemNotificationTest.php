<?php

namespace Tests\Feature\Notification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Events\RequestedItemDetectEvent;
use App\Listeners\RequestedItemDetectListener;
use App\Models\User;
use App\Models\ItemRequest;
use App\Notifications\RequestedItemNotification;

class RequestedItemNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function RequestedItemNotificationのテスト()
    {
        // 送信するユーザーを世界に構築
        $users = User::factory()->createMany([
            ['role' => 1],
            ['role' => 5],
        ]);
        
        $itemRequest = ItemRequest::factory()->create();

        // LowStockDetectEventを発火
        event(new RequestedItemDetectEvent($itemRequest));

        // 通知がデータベースに保存されたことをアサート
        foreach ($users as $user) {
            $this->assertDatabaseHas('notifications', [
                'notifiable_id' => $user->id,
                'notifiable_type' => User::class,
                'type' => RequestedItemNotification::class,
            ]);

            // 通知内容を検証
            $notification = $user->notifications->first();
            $this->assertEquals($itemRequest->id, $notification->data['id']);
            $this->assertEquals($itemRequest->name, $notification->data['item_name']);
            $this->assertEquals($itemRequest->requestor, $notification->data['requestor']);
            $this->assertEquals($itemRequest->remarks_from_requestor, $notification->data['remarks_from_requestor']);
            $this->assertEquals('備品のリクエストが追加されました', $notification->data['message']);
        }
    }
}
