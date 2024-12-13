<?php

namespace Tests\Feature\Api\Controllers\UpdateStockController\getStock;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as FakerFactory;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class getStockMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function API通信で存在する備品の在庫数を取得できる()
    {
        // 世界の構築
        $user = User::factory()->role(1)->create(); // adminユーザーを作成
        $this->actingAs($user);

        // 備品を作成
        $item = Item::factory()->create(['stock' => 10]);

        // APIエンドポイントにGETリクエストを送信
        $response = $this->getJson("/api/consumable_items/{$item->id}/stock");

        // レスポンスの内容確認
        $response->assertJson(['stock' => 10]);
    }

    /** @test */
    function API通信で存在しないIDでは備品の在庫数を取得できない()
    {
        // 世界の構築
        $user = User::factory()->role(1)->create(); // adminユーザーを作成
        $this->actingAs($user);

        // 備品を作成
        $item = Item::factory()->create(['stock' => 10]);

        $invalidItemId = $item->id + 1;        

        // 存在しないIdでAPIにGETリクエストを送信
        $response = $this->getJson("/api/consumable_items/{$invalidItemId}/stock");

        // レスポンスの内容確認
        $response->assertStatus(404);
    }
}