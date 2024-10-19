<?php

namespace Tests\Feature\Notification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use App\Models\Inspection;
use App\Models\Disposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\InspectionSchedule;

class InspectionAndDisposalScheduleNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function InspectionScheduleNotificationのテスト()
    {
        // 通知を送るすべてのタイミングの点検日を準備
        $inspections = Inspection::withoutEvents(function () {
            return [
                Inspection::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム1'])->id,
                    'inspection_scheduled_date' => Carbon::today()->addWeeks(4),
                ]),
                Inspection::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム2'])->id,
                    'inspection_scheduled_date' => Carbon::today()->addWeeks(2),
                ]),
                Inspection::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム3'])->id,
                    'inspection_scheduled_date' => Carbon::today()->addWeek(),
                ]),
                Inspection::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム4'])->id,
                    'inspection_scheduled_date' => Carbon::today()->addDays(3),
                ]),
                Inspection::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム5'])->id,
                    'inspection_scheduled_date' => Carbon::today()->addDay(),
                ]),
                Inspection::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム6'])->id,
                    'inspection_scheduled_date' => Carbon::today(),
                ]),
            ];
        });
        // 送信するユーザーを世界に構築
        $users = User::factory()->createMany([
            ['role' => 1],
            ['role' => 5],
        ]);

        // コマンドを個別に実行する
        Artisan::call('app:inspection-schedule');

        // 通知がデータベースに保存されていることをアサート
        foreach ($users as $user) {
            $this->assertDatabaseHas('notifications', [
                'notifiable_id' => $user->id,
                'notifiable_type' => User::class,
                // 'type' => InspectionScheduleNotification::class,
            ]);

            // 通知内容を検証
            $notifications = $user->notifications;
            $this->assertCount(6, $notifications); 
            
            foreach ($notifications as $notification) {
                // それぞれの$itemのidを取得
                $item = Item::find($notification->data['id']);
                $this->assertEquals($item->id, $notification->data['id']);
                $this->assertEquals($item->management_id, $notification->data['management_id']);
                $this->assertEquals($item->name, $notification->data['item_name']);
                $this->assertEquals('点検予定日が近づいています', $notification->data['message']);
            }
        }
    }

    /** @test */
    function DisposalScheduleNotificationのテスト()
    {
        // 通知を送るすべてのタイミングの点検日を作成、DisposalObserverは無効化
        $disposals = Disposal::withoutEvents(function () {
            return [
                Disposal::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム1'])->id,
                    'disposal_scheduled_date' => Carbon::today()->addWeeks(4),
                ]),
                Disposal::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム2'])->id,
                    'disposal_scheduled_date' => Carbon::today()->addWeeks(2),
                ]),
                Disposal::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム3'])->id,
                    'disposal_scheduled_date' => Carbon::today()->addWeek(),
                ]),
                Disposal::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム4'])->id,
                    'disposal_scheduled_date' => Carbon::today()->addDays(3),
                ]),
                Disposal::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム5'])->id,
                    'disposal_scheduled_date' => Carbon::today()->addDay(),
                ]),
                Disposal::factory()->create([
                    'item_id' => Item::factory()->create(['name' => 'テストアイテム6'])->id,
                    'disposal_scheduled_date' => Carbon::today(),
                ]),
            ];
        });

        // 送信するユーザーを世界に構築
        $users = User::factory()->createMany([
            ['role' => 1],
            ['role' => 5],
        ]);

        // コマンドを個別に実行する
        Artisan::call('app:disposal-schedule');

        // 通知がデータベースに保存されていることをアサート
        foreach ($users as $user) {
            $this->assertDatabaseHas('notifications', [
                'notifiable_id' => $user->id,
                'notifiable_type' => User::class,
                // 'type' => DisposalScheduleNotification::class,
            ]);

            // 通知内容を検証
            $notifications = $user->notifications;
            $this->assertCount(6, $notifications); 

            foreach ($notifications as $notification) {
                // それぞれの$itemのidを取得
                $item = Item::find($notification->data['id']);
                $this->assertEquals($item->id, $notification->data['id']);
                $this->assertEquals($item->management_id, $notification->data['management_id']);
                $this->assertEquals($item->name, $notification->data['item_name']);
                $this->assertEquals('廃棄予定日が近づいています', $notification->data['message']);
            }
        }
    }
}
