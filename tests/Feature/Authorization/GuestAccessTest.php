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

        // ログインしていないユーザーのリダイレクトのアサ―ト
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('items.index'))->assertOk();
        $this->get(route('items.create'))->assertOk();
        $this->from(route('items.create'))->post('items', [])->assertRedirect(route('items.create'));
        $this->get(route('items.edit', ['item' => $item]))->assertOk();
        $this->from(route('items.edit', ['item' => $item]))
            ->patch(route('items.update', $item->id), [])->assertRedirect(route('items.edit', ['item' => $item]));
        $this->from(route('items.show',['item' => $item]))
            ->delete('items/'.$item->id)->assertRedirect(route('items.index'));
        
        $this->get(route('consumable_items'))->assertOk();
        $this->get(route('inspection_and_disposal_items'))->assertOk();
        $this->get(route('item_requests.index'))->assertOk();
        $this->get(route('notifications.index'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
    }

    /** @test */
    function ゲストログインユーザーはDB保存処理をする処理を実行出来ない()
    {   
        $loginUrl = 'login';
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        // DBを変更する機能
        // ミドルウェアでどう設定するか
        $this->get(route('items.create'))->assertOk();
        $this->from(route('items.create'))->post('items', [])->assertRedirect(route('items.create'));
        $this->get(route('items.edit', ['item' => $item]))->assertOk();
        $response = $this->from(route('items.edit', ['item' => $item]))
            ->patch(route('items.update', $item->id), [])->assertRedirect(route('items.edit', ['item' => $item]));
        $response = $this->followRedirects($response);

        // dd($response);
        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit') // コンポーネント名を指定
            ->where('flash.message', 'ゲストには許可されていません、ログインして実行してください') // フラッシュメッセージを指定
        );

        $this->from('items/show')->delete('items/' . $item->id)->assertRedirect($loginUrl);
        
        $this->get('consumable_items')->assertRedirect($loginUrl);
        $this->get('inspection-and-disposal-items')->assertRedirect($loginUrl);
        $this->get('item-requests')->assertRedirect($loginUrl);
        $this->get('notifications')->assertRedirect($loginUrl);
        $this->get('profile')->assertRedirect($loginUrl);
    }

    /** @test */
    function ゲストログインユーザーは備品の新規登録が許可されない()
    {   
        // ゲストログインユーザー
        $user = User::factory()->role(0)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // ログインしていないユーザーのリダイレクトのアサ―ト
        // DBを変更する機能
        // ミドルウェアでどう設定するか
        $this->get(route('items.create'))->assertOk();
        $response = $this->from(route('items.create'))
            ->post('items', [])->assertRedirect(route('items.create'));
        $response = $this->followRedirects($response);

        // フラッシュメッセージの内容をアサート
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create') // コンポーネント名を指定
            ->has('flash.message')
            ->where('flash.message', 'ゲストには許可されていません、ログインして実行してください')
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
                ->where('flash.message', 'ゲストには許可されていません、ログインして実行してください')
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
                ->where('flash.message', 'ゲストには許可されていません、ログインして実行してください')
                ->has('flash.status')
                ->where('flash.status', 'danger')
        );

        // アイテムが復元されていないことを確認
        $this->assertSoftDeleted('items', ['id' => $item->id]);
    }


    // 廃棄モーダルによる廃棄、点検モーダルによる点検
    // 入出庫モーダルによる入庫、出庫
    // リクエストの新規登録

}
