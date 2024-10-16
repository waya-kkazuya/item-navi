<?php

namespace Tests\Feature\Http\Controllers\DashboardController\Index;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Faker\Factory as FakerFactory;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\Edithistory;
use App\Models\EditReason;
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
    function DashboardControllerがすべての備品情報を渡せる_カテゴリでまとめた時()
    {
        Item::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard?type=1')
            ->assertOk();

        $this->assertCount(10, Item::all());

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('allItems', 10)
        );
    }

    /** @test */
    function DashboardControllerがすべての備品情報を渡せる_利用場所でまとめた時()
    {
        Item::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard?type=2')
            ->assertOk();

        $this->assertCount(10, Item::all());

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('allItems', 10)
        );
    }


    /** @test */
    function DashboardControllerがカテゴリでまとめたデータを渡せる()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $items = $categories->map(function ($category) {
            return Item::factory()->create(['category_id' => $category->id]);
        });

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard?type=1')
            ->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('itemsByType', fn (Assert $data) =>
                $categories->pluck('name')->each(fn ($name) =>
                    $data->has($name)
                )
            )
        );
    }

    /** @test */
    function DashboardControllerが利用場所でまとめたデータを渡せる()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();        

        $items = $locations->map(function ($location) {
            return Item::factory()->create(['location_of_use_id' => $location->id]);
        });

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard?type=2')
            ->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('itemsByType', fn (Assert $data) =>
                $locations->pluck('name')->each(fn ($name) =>
                    $data->has($name)
                )
            )
        );
    }

    /** @test */
    function DashboardControllerが編集履歴のデータを渡せる()
    {
        $item = Item::factory()->create();
        $editReason = EditReason::factory()->create();

        $edithistories = Edithistory::factory()->count(20)->create();

        // 3つの編集履歴を作成
        // $edithistories = collect([
        //     EditHistory::factory()->create([
        //         'item_id' => $item->id,
        //         'edit_reason_id' => $editReason->id,
        //         'created_at' => Carbon::now()->subDays(1),
        //         'operation_type' => 'store',
        //     ]),
        //     EditHistory::factory()->create([
        //         'item_id' => $item->id,
        //         'edit_reason_id' => $editReason->id,
        //         'created_at' => Carbon::now()->subDays(1),
        //         'operation_type' => 'update',
        //     ]),
        //     EditHistory::factory()->create([
        //         'item_id' => $item->id,
        //         'edit_reason_id' => $editReason->id,
        //         'created_at' => Carbon::now(),
        //         'operation_type' => 'stock_in',
        //     ]),
        // ]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard')
            ->assertOk();

        // テストデータを日付ごとにグループ化
        // $groupedEdithistories = $edithistories->groupBy(function ($history) {
        //     return $history->created_at->format('Y-m-d');
        // });
        
        // 20件の編集履歴も日付でまとめたら20件ではなくなる
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('groupedEdithistories')
            // ->where('groupedEdithistories', fn (Assert $data) =>
            //     collect($data->toArray())->each(function ($values, $date) use ($data) {
            //         $data->has($date)
            //             ->has($values)
            //         }
            //     )
            // )
        );
            // ->where('groupedEdithistories', fn(Assert $data) =>
            //         $data->each(fn ($values, $date) =>
            //             $data->has($values, fn (Assert $daysData) =>
            //                 $daysData->hasAll([
            //                     'day_of_week',
            //                     'operation_description'
            //                 ])
            //             )
            //         )
            //     )
    }
}
