<?php

namespace Tests\Feature\Http\Controllers\ItemRequestController\Store;

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
use Illuminate\Support\Facades\DB;

class StoreValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();

        // テーブルのデータとIDをリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('request_statuses')->truncate();
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function tearDown(): void
    {
        // 子クラスでのクリーンアップ処理
        parent::tearDown();
    }

    // nameのバリデーションのテスト
    /** @test */
    public function リクエスト新規登録バリデーションnameが空欄で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['name' => '']);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.name')
            ->where('errors.name', '名前は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションnameが1文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['name' => str_repeat('あ', 1)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.name')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションnameが最大20文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['name' => str_repeat('あ', 40)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.name')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションnameが最大を超える21文字で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['name' => str_repeat('あ', 41)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.name')
            ->where('errors.name', '名前は、40文字以下で指定してください。')
            // ->dump()
        );
    }


    // category_idのバリデーションのテスト
    /** @test */
    public function リクエスト新規登録バリデーションcategoryIdが最小値より小さい無効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['category_id' => $categories->min('id') - 1]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.category_id')
            ->where('errors.category_id', '選択されたカテゴリは正しくありません。')
            // ->dump()
        );
    }
    
    /** @test */
    public function リクエスト新規登録バリデーションcategoryIdが最小の有効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['category_id' => $categories->min('id')]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.category_id')
            // ->dump()
        );
    }
    
    /** @test */
    public function リクエスト新規登録バリデーションcategoryIdが最大の有効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['category_id' => $categories->max('id')]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.category_id')
            // ->dump()
        );
    }
    
    /** @test */
    public function リクエスト新規登録バリデーションcategoryIdが最大値を超える無効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['category_id' => $categories->max('id') + 1]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.category_id')
            ->where('errors.category_id', '選択されたカテゴリは正しくありません。')
            // ->dump()
        );
    }


    // location_of_use_idのバリデーションのテスト
    /** @test */
    public function リクエスト新規登録バリデーションlocation_of_use_idが最小値より小さい無効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['location_of_use_id' => 0]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.location_of_use_id')
            ->where('errors.location_of_use_id', '選択された利用場所は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションlocation_of_use_idが最小の有効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['location_of_use_id' => $locations->min('id')]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.location_of_use_id')
            // ->dump()
        );
    }    

    /** @test */
    public function リクエスト新規登録バリデーションlocation_of_use_idが最大の有効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['location_of_use_id' => $locations->max('id')]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.location_of_use_id')
            // ->dump()
        );
    }    

    /** @test */
    public function リクエスト新規登録バリデーションlocation_of_use_idが最大値を超える無効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['location_of_use_id' => $locations->max('id') + 1]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.location_of_use_id')
            ->where('errors.location_of_use_id', '選択された利用場所は正しくありません。')
            // ->dump()
        );
    }



    // requestorのバリデーションテスト
    /** @test */
    public function リクエスト新規登録バリデーションrequestorが空欄で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['requestor' => '']);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.requestor')
            ->where('errors.requestor', '申請者は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションrequestorが1文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['requestor' => str_repeat('あ', 1)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.requestor')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションrequestorが20文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['requestor' => str_repeat('あ', 20)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.requestor')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションrequestorが21文字で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['requestor' => str_repeat('あ', 21)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.requestor')
            ->where('errors.requestor', '申請者は、20文字以下で指定してください。')
            // ->dump()
        );
    }


    // remarks_from_requestorのバリデーションのテスト
    /** @test */
    public function リクエスト新規登録バリデーションremarks_from_requestorが空欄で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['remarks_from_requestor' => '']);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.remarks_from_requestor')
            ->where('errors.remarks_from_requestor', '申請理由は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションremarks_from_requestorが1文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['remarks_from_requestor' => str_repeat('あ', 1)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.remarks_from_requestor')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションremarks_from_requestorが500文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['remarks_from_requestor' => str_repeat('あ', 500)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.remarks_from_requestor')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションremarks_from_requestorが501文字で最大を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['remarks_from_requestor' => str_repeat('あ', 501)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.remarks_from_requestor')
            ->where('errors.remarks_from_requestor', '申請理由は、500文字以下で指定してください。')
            // ->dump()
        );
    }


    // manufacturerのバリデーションテスト
    /** @test */
    public function リクエスト新規登録バリデーションmanufacturer空欄の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['manufacturer' => '']);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.manufacturer')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションmanufacturer1文字の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['manufacturer' => str_repeat('あ', 1)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.manufacturer')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションmanufacturer20文字の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['manufacturer' => str_repeat('あ', 20)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.manufacturer')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションmanufacturer21文字の無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['manufacturer' => str_repeat('あ', 21)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.manufacturer')
            ->where('errors.manufacturer', 'メーカーは、20文字以下で指定してください。')
            // ->dump()
        );
    }


    // referenceのバリデーションテスト
    /** @test */
    public function リクエスト新規登録バリデーションreference空欄で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['reference' => '']);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.reference')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションreference1文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['reference' => str_repeat('あ', 1)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.reference')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションreference20文字で最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['reference' => str_repeat('あ', 20)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.reference')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションreference21文字で最大を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['reference' => str_repeat('あ', 21)]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.reference')
            ->where('errors.reference', '参考サイトは、20文字以下で指定してください。')
            // ->dump()
        );
    }


    // priceのバリデーションテスト
    /** @test */
    public function リクエスト新規登録バリデーションpriceが文字列で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['price' => 'あ']);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.price')
            ->where('errors.price', '価格は整数で指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションpriceがマイナスの数値の無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['price' => -1]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->has('errors.price')
            ->where('errors.price', '価格には、0以上の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションpriceが空欄で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['price' => '']);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.price')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションpriceが0で最小の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['price' => 0]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.price')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションpriceが100万で最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['price' => 1000000]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->missing('errors.price')
            // ->dump()
        );
    }

    /** @test */
    public function リクエスト新規登録バリデーションpriceが100万1で最大を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // routeにpostする
        $response = $this->from('item-requests/create')
            ->post(route('item_requests.store'), ['price' => 1000001]);
        $response->assertRedirect('item-requests/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ItemRequests/Create')
            ->has('errors.price')
            ->where('errors.price', '価格には、1000000以下の数字を指定してください。')
            // ->dump()
        );
    }




}
