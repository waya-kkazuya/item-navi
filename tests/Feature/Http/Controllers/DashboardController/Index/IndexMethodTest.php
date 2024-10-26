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
use Illuminate\Support\Facades\Log;

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

        // DBに存在するデータを構築、まとめるのはコントローラ側UseCase側
        // 4つの編集履歴を作成、当日にCarbon::now()を使うとおかしくなるので使用しない
        // $editHistory3,$editHistory4は同じ日付でまとめられているかテスト
        $editHistory1 = EditHistory::factory()->create([
            'item_id' => $item->id,
            'operation_type' => 'store',
            'created_at' => Carbon::now()->subDays(1),
        ]);
        $editHistory2 = EditHistory::factory()->create([
            'item_id' => $item->id,
            'operation_type' => 'update',
            'created_at' => Carbon::now()->subDays(2),
        ]);
        $editHistory3 = EditHistory::factory()->create([
            'item_id' => $item->id,
            'operation_type' => 'stock_in',
            'created_at' => Carbon::now()->subDays(3),
        ]);
        $editHistory4 = EditHistory::factory()->create([
            'item_id' => $item->id,
            'operation_type' => 'stock_out',
            'created_at' => Carbon::now()->subDays(3)->subHour(), //1時間前
        ]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard')
            ->assertOk();
        
        // slackにログ送信テスト用
        Log::critical('editHistory1 created_at: ' . $editHistory1->created_at);
        Log::critical('editHistory1 operation_type: ' . $editHistory1->operation_type);
        Log::info('editHistory2 created_at: ' . $editHistory2->created_at);
        Log::info('editHistory2 operation_type: ' . $editHistory2->operation_type);
        Log::info('editHistory3 created_at: ' . $editHistory3->created_at);
        Log::info('editHistory4 created_at: ' . $editHistory4->created_at);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('groupedEdithistories')     
            ->has('groupedEdithistories.'.Carbon::now()->subDays(1)->format('Y-m-d')) //具体的な日付でグループ化されていることを確認する
            ->where('groupedEdithistories.'.Carbon::now()->subDays(1)->format('Y-m-d').'.0.item_id', $editHistory1->item_id)
            ->where('groupedEdithistories.'.Carbon::now()->subDays(1)->format('Y-m-d').'.0.operation_type', $editHistory1->operation_type)
            ->has('groupedEdithistories.'.Carbon::now()->subDays(2)->format('Y-m-d')) 
            ->where('groupedEdithistories.'.Carbon::now()->subDays(2)->format('Y-m-d').'.0.item_id', $editHistory2->item_id)
            ->where('groupedEdithistories.'.Carbon::now()->subDays(2)->format('Y-m-d').'.0.operation_type', $editHistory2->operation_type)
            ->has('groupedEdithistories.'.Carbon::now()->subDays(3)->format('Y-m-d')) 
            ->where('groupedEdithistories.'.Carbon::now()->subDays(3)->format('Y-m-d').'.0.item_id', $editHistory3->item_id)
            ->where('groupedEdithistories.'.Carbon::now()->subDays(3)->format('Y-m-d').'.0.operation_type', $editHistory3->operation_type)
            ->has('groupedEdithistories.'.Carbon::now()->subDays(3)->format('Y-m-d')) 
            ->where('groupedEdithistories.'.Carbon::now()->subDays(3)->format('Y-m-d').'.1.item_id', $editHistory4->item_id)
            ->where('groupedEdithistories.'.Carbon::now()->subDays(3)->format('Y-m-d').'.1.operation_type', $editHistory4->operation_type)
            // ->dump()
        );
    }
}
