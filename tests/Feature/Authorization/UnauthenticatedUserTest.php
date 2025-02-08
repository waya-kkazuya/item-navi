<?php

namespace Tests\Feature\Authorization;

use App\Models\Item;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnauthenticatedUserTest extends TestCase
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
    public function ログインしていないユーザーはログイン後の画面にアクセスできない()
    {
        $loginUrl = 'login';
        // $user = User::factory()->role(1)->create(); // ログインはしない
        // $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('dashboard'))->assertRedirect($loginUrl);
        $this->get(route('items.index'))->assertRedirect($loginUrl);
        $this->get(route('items.create'))->assertRedirect($loginUrl);
        $this->from(route('items.create'))->post(route('items.store'), [])->assertRedirect($loginUrl);
        $this->get(route('items.show', ['item' => $item]))->assertRedirect($loginUrl);
        $this->get(route('items.edit', ['item' => $item]))->assertRedirect($loginUrl);
        $this->from(route('items.edit', ['item' => $item]))->patch(route('items.update', $item->id), [])->assertRedirect($loginUrl);

        // 詳細画面から廃棄できない
        $this->from(route('items.show', ['item' => $item]))
            ->put(route('dispose_item.disposeItem', ['item' => $item]), [])->assertRedirect($loginUrl);
        // 詳細画面から点検できない
        $this->from(route('items.show', ['item' => $item]))
            ->put(route('inspect_item.inspectItem', ['item' => $item]), [])->assertRedirect($loginUrl);

        $this->get(route('consumable_items'))->assertRedirect($loginUrl);
        // 消耗品管理画面にアクセスできる、出庫できる、入庫できる
        $this->from(route('consumable_items'))
            ->put(route('decreaseStock', ['item' => $item]), [])->assertRedirect($loginUrl);
        $this->from(route('consumable_items'))
            ->put(route('increaseStock', ['item' => $item]), [])->assertRedirect($loginUrl);

        $this->get(route('inspection_and_disposal_items'))->assertRedirect($loginUrl);
        $this->get(route('item_requests.index'))->assertRedirect($loginUrl);
        $this->get(route('notifications.index'))->assertRedirect($loginUrl);
        $this->get(route('profile.edit'))->assertRedirect($loginUrl);
        $this->from(route('profile.edit'))->patch(route('profile.update'))->assertRedirect($loginUrl);
    }
}
