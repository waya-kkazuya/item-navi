<?php

namespace Tests\Feature\Http\Controllers\NotificationController\Index;

use App\Models\Disposal;
use App\Models\Inspection;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\User;
use App\Notifications\DisposalScheduleNotification;
use App\Notifications\InspectionScheduleNotification;
use App\Notifications\LowStockNotification;
use App\Notifications\RequestedItemNotification;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function NotificationControllerがデータを渡せる()
    {
        $user = User::factory()->role(1)->create();

        $item       = Item::factory()->create();
        $inspection = Inspection::withoutEvents(function () {
            return Inspection::factory()->create();
        });
        $disposal = Disposal::withoutEvents(function () {
            return Disposal::factory()->create();
        });

        $itemRequest = ItemRequest::factory()->create();

        $lowStockNotification           = new LowStockNotification($item);
        $disposalScheduleNotification   = new DisposalScheduleNotification($inspection);
        $inspectionScheduleNotification = new InspectionScheduleNotification($disposal);
        $requestedItemNotification      = new RequestedItemNotification($itemRequest);

        // 通知をデータベースに保存
        $user->notify($lowStockNotification);
        $user->notify($disposalScheduleNotification);
        $user->notify($inspectionScheduleNotification);
        $user->notify($requestedItemNotification);

        $this->actingAs($user);

        $response = $this->get('/notifications')
            ->assertOk();

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Notification')
                ->has('notifications')
                ->has('lowStockNotifications', 1)
                ->has('inspectionAndDisposalNotifications', 2)
                ->has('requestedItemNotifications', 1)
        );
    }
}
