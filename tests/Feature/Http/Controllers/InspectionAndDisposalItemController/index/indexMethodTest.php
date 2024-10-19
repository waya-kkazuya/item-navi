<?php

namespace Tests\Feature\Http\Controllers\InspectionAndDisposalItemController\index;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use App\Models\User;
use App\Models\Item;
use App\Models\Inspection;
use App\Models\Disposal;
use Inertia\Testing\AssertableInertia as Assert;
use Carbon\Carbon;

class IndexMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function 点検と廃棄画面の予定と履歴の情報をVue側に渡せる()
    {
        // DBにデータを構築
        // Inspectionのデータ
        // 点検予定日
        $scheduledInspections = Inspection::withoutEvents(function () {
            return Inspection::factory()->count(10)->create(['status' => 0]); // 点検がされていない
        });

        // 点検履歴
        $historyInspections = Inspection::withoutEvents(function () {
            return Inspection::factory()->count(10)->create(['status' => 1]); // 点検済み
        });

        // Disposalのデータ
        // 廃棄予定日
        $scheduledDisposals = Disposal::withoutEvents(function () {
            return Disposal::factory()->count(10)->create([
                'item_id' => Item::factory()->create(['deleted_at' => null])->id, // 廃棄されていない
                'disposal_scheduled_date' => Carbon::today()->addWeek(),
            ]);
        });

        // 廃棄履歴
        $historyDisposals = Disposal::withoutEvents(function () {
            return Disposal::factory()->count(10)->create([
                'item_id' => Item::factory()->create(['deleted_at' => Carbon::now()])->id, // 廃棄済み
            ]);
        });

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/inspection-and-disposal-items')
            ->assertOk();
        
        $response->assertInertia(fn (Assert $page) => $page
            ->component('InspectionAndDisposalItems/Index')
            ->has('scheduledInspections.data', 10)
            ->has('historyInspections.data', 10)
            ->has('scheduledDisposals.data', 10)
            ->has('historyDisposals.data', 10)
        );
    }
}
