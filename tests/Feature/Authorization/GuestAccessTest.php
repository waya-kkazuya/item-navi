<?php

namespace Tests\Feature\Authorization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Location;
use App\Models\UsageStatus;
use App\Models\AcquisitionMethod;
use App\Models\Edithistory;
use App\Models\Inspection;
use App\Models\EditReason;
use App\Models\ItemRequest;
use App\Models\RequestStatus;
use App\Models\StockTransaction;
use App\Services\ImageService;
use Faker\Factory as FakerFactory;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use App\Services\ManagementIdService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Console\DumpCommand;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Inertia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;
use Illuminate\Support\Facades\Session;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function ゲストログインユーザーは画面にアクセスできる()
    {   
        $loginUrl = 'login';
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // リダイレクトは
        $this->get(route('dashboard'))->assertOk();

        $this->get(route('items.index'))->assertOk();
        $this->get(route('items.create'))->assertOk();
        $this->get(route('items.edit', ['item' => $item]))->assertOk();     
        $this->get(route('consumable_items'))->assertOk();
        $this->get(route('inspection_and_disposal_items'))->assertOk();
        $this->get(route('item_requests.index'))->assertOk();
        $this->get(route('item_requests.create'))->assertOk();
        $this->get(route('notifications.index'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
    }

    /** @test */
    function ゲストログインユーザーは備品の新規登録が許可されない()
    {   
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('items.create'))->assertOk();
        $response = $this->from(route('items.create'))
            ->post('items', [])->assertRedirect(route('items.create'));
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create') // コンポーネント名を指定
            ->has('flash.message')
            ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
            ->has('flash.status')
            ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function ゲストログインユーザーは備品の編集更新が許可されない()
    {   
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('items.edit', ['item' => $item]))->assertOk();
        $response = $this->from(route('items.edit', ['item' => $item]))
            ->patch(route('items.update', $item->id), [])->assertRedirect(route('items.edit', ['item' => $item]));
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function ゲストログインユーザーは備品の復元が許可されない()
    {   
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        // アイテムを作成し、ソフトデリートする
        $item = Item::factory()->create();
        $item->delete();

        // アイテムがソフトデリートされたことを確認
        $this->assertSoftDeleted('items', ['id' => $item->id]);

        // アイテムを復元するリクエストを送信
        $response = $this->from(route('items.index'))
            ->post(route('items.restore', $item->id));

        // レスポンスが期待通りか確認
        $response->assertStatus(302);
        $response->assertRedirect(route('items.index'));
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Index')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );

        // アイテムが復元されていないことを確認
        $this->assertSoftDeleted('items', ['id' => $item->id]);
    }

    /** @test */
    function 廃棄モーダルで廃棄の実施が許可されない()
    {
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('items.show', ['item' => $item]))->assertOk();
        $response = $this->from(route('items.show', ['item' => $item]))
            ->put(route('dispose_item.disposeItem', ['item' => $item]), [])->assertStatus(302);
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Show')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function 点検モーダルで点検の実施が許可されない()
    {
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('items.show', ['item' => $item]))->assertOk();
        $response = $this->from(route('items.show', ['item' => $item]))
            ->put(route('inspect_item.inspectItem', ['item' => $item]), [])->assertStatus(302);
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Show')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function 出庫モーダルで出庫の実施が許可されない()
    {
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('consumable_items'))->assertOk();
        $response = $this->from(route('consumable_items'))
            ->put(route('decreaseStock', ['item' => $item]), [])->assertStatus(302);
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function 入庫モーダルで入庫の実施が許可されない()
    {
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('consumable_items'))->assertOk();
        $response = $this->from(route('consumable_items'))
            ->put(route('increaseStock', ['item' => $item]), [])->assertStatus(302);
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function リクエストの新規登録が許可されない()
    {
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('item_requests.create'))->assertOk();
        $response = $this->from(route('item_requests.create'))
            ->post(route('item_requests.store', []))->assertStatus(302);
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function リクエストの削除が許可されない()
    {
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item_request = ItemRequest::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('item_requests.index'))->assertOk();
        $response = $this->from(route('item_requests.index'))
            ->delete(route('item_requests.destroy', ['item_request' => $item_request]))->assertStatus(302);
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Index')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }

    /** @test */
    function APIによるリクエストのステータスの変更が許可されない()
    {
        $item_requests = ItemRequest::factory()->count(4)->create();
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item_request = ItemRequest::factory()->create();

        // APIリクエストを送信
        $response = $this->postJson(route('item-requests.update-status', $item_request->id), [
            'requestStatusId' => 2,
        ]);

        // レスポンスの検証
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'ゲストには許可されていない機能です、ログインして実行してください',
                'status' => 'danger'
            ]);
    }

    /** @test */
    function プロフィール情報の更新が許可されない()
    {
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item_request = ItemRequest::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('profile.edit'))->assertOk();
        $response = $this->from(route('profile.edit'))
            ->patch(route('profile.update'), [])->assertStatus(302);
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Edit')
                ->has('flash.message')
                ->where('flash.message', 'ゲストには許可されていない機能です、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );
    }



    

    // プロフィール情報の更新

}
