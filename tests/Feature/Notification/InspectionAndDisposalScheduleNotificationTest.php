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

class InspectionAndDisposalScheduleNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function InspectionScheduleNotificationとDisposalScheduleNotificationのテスト()
    {


        $item = Item::factory()->create();

        // 予定されている点検と廃棄を作成
        $inspection = Inspection::factory()->create([
            'item_id' => $item->id,
            'inspection_scheduled_date' => Carbon::today()->addWeek(),
        ]);
        $disposal = Disposal::factory()->create([
            'item_id' => $item->id,
            'disposal_scheduled_date' => Carbon::today()->addWeek(),
        ]);

        // 送信するユーザーを世界に構築
        $users = User::factory()->createMany([
            ['role' => 1],
            ['role' => 5],
        ]);

        // Artisanコマンドを実行してスケジュールをトリガー
        Artisan::call('schedule:run');

        // 通知がデータベースに保存されていることをアサート
        foreach ($users as $user) {
            $this->assertDatabaseHas('notifications', [
                'notifiable_id' => $user->id,
                'notifiable_type' => User::class,
                'type' => InspectionScheduleNotification::class,
            ]);
            $this->assertDatabaseHas('notifications', [
                'notifiable_id' => $user->id,
                'notifiable_type' => User::class,
                'type' => DisposalScheduleNotification::class,
            ]);
        }
    }
}
