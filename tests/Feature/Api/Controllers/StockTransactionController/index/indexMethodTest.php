<?php

namespace Tests\Feature\Api\Controllers\StockTransactionController\index;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Inertia;
use Faker\Factory as FakerFactory;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\StockTransaction;

class indexMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function 入出庫履歴モーダル用のデータをAPIで取得できる()
    {
        // 世界の構築
        $user = User::factory()->role(1)->create(); // adminユーザーを作成
        $this->actingAs($user);   

        $item = Item::factory()->create();
        // $item->idの編集履歴をDBに保存
        $stockTransactions = StockTransaction::factory()->count(20)->create(['item_id' => $item->id]);

        // Inertiaリクエストのシミュレーション、ヘッダーが追加される
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => Inertia::getVersion(),
        ])->get('/api/stock_transactions?item_id=' . $item->id);
        
        $response->assertOk();

        // dd($response->json());

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('stockTransactions', 10)
                ->has('stockTransactions', fn ($json) => 
                    $json->each(fn ($json) => 
                        $json->where('item_id', $item->id)
                            ->where('transaction_type', fn($type) => in_array($type, ['入庫', '出庫', '登録', '修正']))
                            ->where('quantity', fn($quantity) => is_int($quantity))
                            ->where('operator_name', fn($name) => is_string($name))
                            ->etc()
                )
            )
            ->has('labels', 10)
            ->has('stocks', 10)
            ->has('transaction_types', 10)
            ->where('minimum_stock', fn($value) => is_int($value))
        );
    }

}
