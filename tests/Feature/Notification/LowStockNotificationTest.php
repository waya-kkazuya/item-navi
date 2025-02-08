<?php

namespace Tests\Feature\Notification;

use App\Events\LowStockDetectEvent;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function LowStockNotificationのテスト()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Notification::fake();とEvent::fake();は使用しない
        // 送信するユーザーを世界に構築
        $users = User::factory()->createMany([
            ['role' => 1],
            ['role' => 5],
        ]);

        // テスト用のアイテムを作成
        $category = Category::factory()->create(['id' => 1]);
        $item     = Item::factory()->create([
            'category_id'   => $category->id,
            'stock'         => 2,
            'minimum_stock' => 5,
        ]);

        // LowStockDetectEventを発火
        event(new LowStockDetectEvent($item));

        // 通知がデータベースに保存されたことをアサート
        foreach ($users as $user) {
            $this->assertDatabaseHas('notifications', [
                'notifiable_id'   => $user->id,
                'notifiable_type' => User::class,
                'type'            => LowStockNotification::class,
            ]);

            // 通知内容を検証
            $notification = $user->notifications->first();
            $this->assertEquals($item->id, $notification->data['id']);
            $this->assertEquals($item->management_id, $notification->data['management_id']);
            $this->assertEquals($item->name, $notification->data['item_name']);
            $this->assertEquals($item->stock, $notification->data['quantity']);
            $this->assertEquals($item->minimum_stock, $notification->data['minimum_stock']);
            $this->assertEquals('在庫数が通知在庫数以下になっています', $notification->data['message']);
        }
    }
}
