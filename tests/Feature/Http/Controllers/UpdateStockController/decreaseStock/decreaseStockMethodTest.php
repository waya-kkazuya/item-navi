<?php

namespace Tests\Feature\Http\Controllers\UpdateStockController\decreaseStock;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class decreaseStockMethodTest extends TestCase
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

    // 在庫数以下にはquantityを出来ないバリデーションRulesがStockLimit
    /** @test */
    public function 出庫モーダルで出庫処理が出来る()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id'   => $category->id,
            'stock'         => 10,
            'minimum_stock' => 2,
        ]);

        $validData = [
            'item_id'          => $item->id,
            'transaction_type' => '出庫',
            'operator_name'    => $user->name,
            'quantity'         => 3,
        ];

        // 備品を出庫処理
        $response = $this->put(route('decreaseStock', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認

        $this->assertDatabaseHas('items', [
            'id'    => $item->id,
            'stock' => '7',
        ]);

        $this->assertDatabaseHas('stock_transactions', [
            'item_id'          => $item->id,
            'transaction_type' => '出庫',
            'operator_name'    => $user->name,
            'quantity'         => -3, //出庫はマイナスの数値で保存する
        ]);
    }
}
