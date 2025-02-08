<?php

namespace Tests\Feature\Api\Controllers\ItemController\Restore;

use App\Models\Item;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestoreMethodTest extends TestCase
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
    public function 廃棄された備品を復元できる()
    {
        // adminユーザーでログイン
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // アイテムを作成してソフトデリート
        $item = Item::factory()->create();
        $item->delete();

        // アイテムがソフトデリートされていることを確認
        $this->assertSoftDeleted('items', ['id' => $item->id]);

        // アイテムをAPI経由で復元
        $response = $this->json('POST', route('api.items.restore', ['id' => $item->id]));

        // レスポンスの確認
        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => '備品を復元しました',
                'item'    => [
                    'id'         => $item->id,
                    'deleted_at' => null,
                ],
            ]);

        // アイテムが復元されたことを確認
        $this->assertDatabaseHas('items', [
            'id'         => $item->id,
            'deleted_at' => null,
        ]);
    }
}
