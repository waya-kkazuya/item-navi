<?php

namespace Tests\Feature\Authorization;

use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessTest extends TestCase
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
    public function Userが権限のない画面にアクセスできない()
    {
        // roleが9の場合のuser
        $user = User::factory()->role(9)->create();
        $this->actingAs($user);

        $item         = Item::factory()->create();
        $item_request = ItemRequest::factory()->create();

        // 表示できない場合はリダイレクトではなく、403Forbiddenにしておく
        $this->get(route('items.index'))->assertStatus(403);
        $this->get(route('items.create'))->assertStatus(403);
        $this->from(route('items.create'))
            ->post(route('items.store'), [])->assertStatus(403);
        $this->get(route('items.edit', ['item' => $item]))->assertStatus(403);
        $this->from(route('items.edit', ['item' => $item]))
            ->patch(route('items.update', $item->id), [])->assertStatus(403);

        // 詳細画面から廃棄できない
        $this->from(route('items.show', ['item' => $item]))
            ->put(route('dispose_item.disposeItem', ['item' => $item]), [])->assertStatus(403);
        // 詳細画面から点検できない
        $this->from(route('items.show', ['item' => $item]))
            ->put(route('inspect_item.inspectItem', ['item' => $item]), [])->assertStatus(403);

        // 点検と廃棄画面にはアクセスできない
        $this->get(route('inspection_and_disposal_items'))->assertStatus(403);
        // 通知にはアクセスできない
        $this->get(route('notifications.index'))->assertStatus(403);

        // リクエストは削除できない(削除ボタンは表示されていない)
        $this->from(route('item_requests.index'))
            ->delete(route('item_requests.destroy', ['item_request' => $item_request]))->assertStatus(403);

        // Dashboardはアプリアイコンにリンクが設定されていて、
        // ログイン後にアクセスすると403になってしまうので、消耗品管理画面にリダイレクトする
        // ダッシュボードにアクセスすると、消耗品管理画面へリダイレクトする
        $this->get(route('dashboard'))->assertRedirect('consumable_items');
    }

    /** @test */
    public function Userが権限のある画面にアクセスできる()
    {
        // roleが9の場合のuser
        $user = User::factory()->role(9)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 消耗品管理画面にアクセスできる、出庫できる、入庫できる
        $this->get(route('consumable_items'))->assertOk();
        $this->from(route('consumable_items'))
            ->put(route('decreaseStock', ['item' => $item]), [])->assertStatus(302); //バリデーションエラー
        $this->from(route('consumable_items'))
            ->put(route('increaseStock', ['item' => $item]), [])->assertStatus(302); //バリデーションエラー

        // リクエスト一覧、新規作成画面、新規登録はできる
        $this->get(route('item_requests.index'))->assertOk();
        $this->get(route('item_requests.create'))->assertOk();
        $this->from(route('item_requests.create'))
            ->post(route('item_requests.store'), [])->assertStatus(302); //バリデーションエラー

        // プロフィール情報画面にアクセス、プロフィール情報更新ができる
        $this->get(route('profile.edit'))->assertOk();
        $this->from(route('profile.edit'))
            ->patch(route('profile.update'), [])->assertStatus(302);
    }
}
