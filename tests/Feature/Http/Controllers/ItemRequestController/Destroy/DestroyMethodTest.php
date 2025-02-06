<?php

namespace Tests\Feature\Http\Controllers\ItemRequestController\Destroy;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\User;
use App\Models\ItemRequest;

class DestroyMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    protected function tearDown(): void
    {
        // 子クラスでのクリーンアップ処理
        parent::tearDown();
    }

    /** @test */
    function リクエストが削除できる()
    {
        $itemRequest = ItemRequest::factory()->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->delete(route('item_requests.destroy', $itemRequest))
            ->assertRedirect(route('item_requests.index'));

        $response->assertSessionHas([
            'message' => 'リクエストを削除しました',
            'status' => 'danger'
        ]);

        // データベースの検証
        $this->assertDatabaseMissing('item_requests', ['id' => $itemRequest->id]);
    }
}
