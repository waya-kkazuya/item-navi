<?php

namespace Tests\Feature\Http\Controllers\ItemController\Store;

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
use App\Services\QrCodeService;
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
use Illuminate\Support\Facades\App;

class StoreValidationTest extends TestCase
{
    use RefreshDatabase;
    // use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    // 新規登録のnameのバリデーションのテスト（データプロバイダを使用）
    /**
     * @dataProvider nameValidationProvider
    * @test
    */
    public function 備品新規登録バリデーションname($data)
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $url = 'items';
        $response = $this->from('items/create')->post($url, $data);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->when($data['expectedError'] !== null, fn ($page) => $page
                ->has('errors.name')
                ->where('errors.name', $data['expectedError'])
            )
            ->when($data['expectedError'] === null, fn ($page) => $page
                ->missing('errors.name')
            )
        );
    }
    
    public static function nameValidationProvider()
    {
        return [
            'nameが空文字の時はエラーメッセージが出る' => [
                ['name' => '', 'expectedError' => '名前は必ず指定してください。']
            ],
            'nameが1文字以下の時はエラーメッセージが出ない' => [
                ['name' => str_repeat('あ', 1), 'expectedError' => null]
            ],
            'nameが41文字の時はエラーメッセージが出る' => [
                ['name' => str_repeat('あ', 41), 'expectedError' => '名前は、40文字以下で指定してください。']
            ],
            'nameが40文字の時はエラーメッセージが出ない' => [
                ['name' => str_repeat('あ', 40), 'expectedError' => null]
            ],
        ];
    }
    

    // 新規登録のcategory_idのバリデーションのテスト    
    /** @test */
    public function 備品新規登録バリデーションcategoryIdが最小値より小さい無効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['category_id' => $categories->min('id') - 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.category_id')
            ->where('errors.category_id', '選択されたカテゴリは正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションcategoryIdが最小の有効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['category_id' => $categories->min('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.category_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションcategoryIdが最大の有効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['category_id' => $categories->max('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.category_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションcategoryIdが最大値を超える無効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['category_id' => $categories->max('id') + 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.category_id')
            ->where('errors.category_id', '選択されたカテゴリは正しくありません。')
            // ->dump()
        );
    }


    // 新規登録のstockのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションstockが最小値より小さい無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['stock' => -1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.stock')
            ->where('errors.stock', '在庫数には、0以上の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションstockが最小の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['stock' => 0]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.stock')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションstockが最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['stock' => 200]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.stock')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションstockが最大値を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['stock' => 201]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.stock')
            ->where('errors.stock', '在庫数には、200以下の数字を指定してください。')
            // ->dump()
        );
    }


    // カテゴリが消耗品以外の時、minimum_stockがnullで保存されることをテスト
    /** @test */
    public function 備品新規登録バリデーションカテゴリが消耗品の時はminimum_stockの値は保存される()
    {
        // CI環境でのQrCodeServiceのモック化
        if (App::environment('testing')) {
            $mock = Mockery::mock(QrCodeService::class);
            $mock->shouldReceive('upload')
                ->once()
                ->with(Mockery::type(Item::class))
                ->andReturn([
                    'labelNameToStore' => 'mocked_label.jpg',
                    'qrCodeNameToStore' => 'mocked_qrcode.png'
                ]);
            $this->app->instance(QrCodeService::class, $mock);
        }

        // 世界を構築
        // $categories = Category::factory()->count(11)->create();
        $category = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 適切に保存されるデータでカテゴリが消耗品(catgery_id=1)の時
        $response = $this->from('items/create')->post('items', [
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->first()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-03',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
            'qrcode' => null,
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20'  
        ]);
        \Log::debug('$response', ['context' => $response]);
        $response->assertRedirect('items'); //URLにリダイレクト
        $response->assertStatus(302);

        $this->assertDatabaseHas('items', [
            'category_id' => 1,
            'minimum_stock' => 2
        ]);

        // モックが呼び出されたことを確認
        $this->qrCodeService->shouldHaveReceived('upload')->once();
    }

    /** @test */
    public function 備品新規登録バリデーションカテゴリが消耗品以外の時は値がminimum_stockはnullで保存される()
    {
        // 世界を構築
        // $categories = Category::factory()->count(11)->create();
        $category = Category::factory()->create([
            'id' => 2,
            'name' => 'IT機器'
        ]);
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', [
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->first()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-03',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
            'qrcode' => null,
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20'  
        ]);
        $response->assertRedirect('items'); //URLにリダイレクト
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('items', [
            'category_id' => $category->id,
            'minimum_stock' => null
        ]);
    }

    // 新規登録のminimum_stockのバリデーションのテスト、カテゴリは消耗品(category_id=1)で固定
    /** @test */
    public function 備品新規登録バリデーションminimum_stockが最小値より小さい無効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', [
            'category_id' => 1,
            'minimum_stock' => -1
        ]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.minimum_stock')
            ->where('errors.minimum_stock', '通知在庫数には、0以上の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションminimum_stockが最小の有効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', [
            'category_id' => 1,
            'minimum_stock' => 0
        ]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.minimum_stock')
            ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションminimum_stockが最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', [
            'category_id' => 1,
            'minimum_stock' => 50
        ]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.minimum_stock')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションminimum_stockが最大値を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', [
            'category_id' => 1,
            'minimum_stock' => 51
        ]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.minimum_stock')
            ->where('errors.minimum_stock', '通知在庫数には、50以下の数字を指定してください。')
            // ->dump()
        );
    }


    // 新規登録のunit_idのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションunit_idが最小値より小さい無効値()
    {
        // 世界を構築
        $units = Unit::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['unit_id' => $units->min('id') - 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.unit_id')
            ->where('errors.unit_id', '選択された単位は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションunit_idが最小の有効値()
    {
        // 世界を構築
        $units = Unit::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['unit_id' => $units->min('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.unit_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションunit_idが最大の有効値()
    {
        // 世界を構築
        $units = Unit::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['unit_id' => $units->max('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.unit_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションunit_idが最大値を超える無効値()
    {
        // 世界を構築
        $units = Unit::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['unit_id' => $units->max('id') + 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.unit_id')
            ->where('errors.unit_id', '選択された単位は正しくありません。')
            // ->dump()
        );
    }

    
    // チェックボックスの値が保存されるかのテスト
    // →保存されるかどうかのテストは新規登録でやっているので大丈夫



    // 新規登録のusage_status_idのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションusage_status_idが最小値より小さい無効値()
    {
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['usage_status_id' => $usage_statuses->min('id') - 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.usage_status_id')
            ->where('errors.usage_status_id', '選択された利用状況は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションusage_status_idが最小の有効値()
    {
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['usage_status_id' => $usage_statuses->min('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.usage_status_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションusage_status_idが最大の有効値()
    {
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['usage_status_id' => $usage_statuses->max('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.usage_status_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションusage_status_idが最大値を超える無効値()
    {
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['usage_status_id' => $usage_statuses->max('id') + 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.usage_status_id')
            ->where('errors.usage_status_id', '選択された利用状況は正しくありません。')
            // ->dump()
        );
    }

    
    
    // 新規登録のend_userのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションend_user空欄のまま保存できる()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['end_user' => '']);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.end_user')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションend_user1文字で保存できる()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['end_user' => str_repeat('あ', 1)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.end_user')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションend_user10文字で保存できる()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['end_user' => str_repeat('あ', 10)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.end_user')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションend_user11文字で保存できない()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $url = 'items';
        $response = $this->from('items/create')->post($url, ['end_user' => str_repeat('あ', 11)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.end_user')
            ->where('errors.end_user', '使用者は、10文字以下で指定してください。')
        );
    }
    

    // 新規登録のlocation_of_use_idのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションlocation_of_use_idが最小値より小さい無効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['location_of_use_id' => 0]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.location_of_use_id')
            ->where('errors.location_of_use_id', '選択された利用場所は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションlocation_of_use_idが最小の有効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['location_of_use_id' => $locations->min('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.location_of_use_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションlocation_of_use_idが最大の有効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);
        
        dump(Location::max('id'));

        $response = $this->from('items/create')->post('items', ['location_of_use_id' => $locations->max('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.location_of_use_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションlocation_of_use_idが最大値を超える無効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['location_of_use_id' => $locations->max('id') + 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.location_of_use_id')
            ->where('errors.location_of_use_id', '選択された利用場所は正しくありません。')
            // ->dump()
        );
    }


    // 新規登録のstorage_location_idのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションstorage_location_idが最小値より小さい無効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['storage_location_id' => 0]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.storage_location_id')
            ->where('errors.storage_location_id', '選択された保管場所は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションstorage_location_idが最小の有効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['storage_location_id' => Location::min('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.storage_location_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションstorage_location_idが最大の有効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);
        
        dump(Location::max('id'));

        $response = $this->from('items/create')->post('items', ['storage_location_id' => Location::max('id')]);
        // $response = $this->from('items/create')->post('items', ['storage_location_id' => 12]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.storage_location_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションstorage_location_idが最大値を超える無効値()
    {
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['storage_location_id' => Location::max('id') + 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.storage_location_id')
            ->where('errors.storage_location_id', '選択された保管場所は正しくありません。')
            // ->dump()
        );
    }


    // 新規登録のacquisition_method_idのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションacquisition_method_idが最小値より小さい無効値()
    {
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['acquisition_method_id' => 0]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.acquisition_method_id')
            ->where('errors.acquisition_method_id', '選択された取得区分は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションacquisition_method_idが最小の有効値()
    {
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['acquisition_method_id' => AcquisitionMethod::min('id')]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.acquisition_method_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションacquisition_method_idが最大の有効値()
    {
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);
        
        dump(AcquisitionMethod::max('id'));

        $response = $this->from('items/create')->post('items', ['acquisition_method_id' => AcquisitionMethod::max('id')]);
        // $response = $this->from('items/create')->post('items', ['acquisition_method_id' => 12]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.acquisition_method_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションacquisition_method_idが最大値を超える無効値()
    {
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['acquisition_method_id' => AcquisitionMethod::max('id') + 1]);
        // $response = $this->from('items/create')->post('items', ['acquisition_method_id' => 13]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.acquisition_method_id')
            ->where('errors.acquisition_method_id', '選択された取得区分は正しくありません。')
            // ->dump()
        );
    }


    // 新規登録のacquisition_sourceのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションacquisition_sourceが空欄の無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['acquisition_source' => '']);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.acquisition_source')
            ->where('errors.acquisition_source', '取得先は必ず指定してください。')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションacquisition_sourceが1文字の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['acquisition_source' => str_repeat('あ', 1)] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.acquisition_source')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションacquisition_sourceが20文字の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['acquisition_source' => str_repeat('あ', 20)] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.acquisition_source')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションacquisition_sourceが21文字の無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['acquisition_source' => str_repeat('あ', 21)] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.acquisition_source')
            ->where('errors.acquisition_source', '取得先は、20文字以下で指定してください。')
        );
    }


    // 新規登録のpriceのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションpriceが空欄で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['price' => '']);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.price')
            ->where('errors.price', '価格は必ず指定してください。')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションpriceが文字列で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['price' => 'あ']);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.price')
            ->where('errors.price', '価格は整数で指定してください。')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションpriceがマイナスの数値の無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['price' => -1] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.price')
            ->where('errors.price', '価格には、0以上の数字を指定してください。')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションpriceが0で最小の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['price' => 0] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.price')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションpriceが100万で最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['price' => 1000000] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.price')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションpriceが100万1で最大を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['price' => 1000001] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.price')
            ->where('errors.price', '価格には、1000000以下の数字を指定してください。')
        );     
    }



    // 新規登録のdate_of_acquisitionのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションdate_of_acquisitionが空で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['date_of_acquisition' => ''] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.date_of_acquisition')
            ->where('errors.date_of_acquisition', '取得年月日は必ず指定してください。')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdate_of_acquisitionが今日より未来の日付で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 今日より未来の日付を生成
        $tomorrow = Carbon::now()->addDays(1)->format('Y-m-d');

        $response = $this->from('items/create')->post('items', ['date_of_acquisition' => $tomorrow] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.date_of_acquisition')
            ->where('errors.date_of_acquisition', '取得年月日には、今日以前の日付をご利用ください。')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdate_of_acquisitionが今日当日で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 今日の日付
        $today = Carbon::now()->format('Y-m-d');

        // dump($today);

        $response = $this->from('items/create')->post('items', ['date_of_acquisition' => $today] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.date_of_acquisition')
            // ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdate_of_acquisitionが今日より過去の日付で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 昨日の日付
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        dump($yesterday);

        $response = $this->from('items/create')->post('items', ['date_of_acquisition' => $yesterday] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.date_of_acquisition')
            // ->dump()
        );
    }


    // 新規登録のmanufacturerのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションmanufacturer空欄の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['manufacturer' => '']);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.manufacturer')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションmanufacturer1文字の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['manufacturer' => str_repeat('あ', 1)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.manufacturer')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションmanufacturer20文字で最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['manufacturer' => str_repeat('あ', 20)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.manufacturer')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションmanufacturer21文字で最大を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['manufacturer' => str_repeat('あ', 21)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.manufacturer')
            ->where('errors.manufacturer', 'メーカーは、20文字以下で指定してください。')
        );
    }
    


    // 新規登録のproduct_numberのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションproduct_number空欄で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['product_number' => '']);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.product_number')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションproduct_number1文字の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['product_number' => str_repeat('あ', 1)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.product_number')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションproduct_number30文字で最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['product_number' => str_repeat('あ', 30)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.product_number')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションproduct_number31文字で最大を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['product_number' => str_repeat('あ', 31)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.product_number')
            ->where('errors.product_number', '製品番号は、30文字以下で指定してください。')
        );
    }



    // 新規登録のremarksのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションremarks空欄の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['remarks' => '']);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.remarks')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションremarks1文字の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['remarks' => str_repeat('あ', 1)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.remarks')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションremarks500文字で最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['remarks' => str_repeat('あ', 500)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.remarks')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションremarks501文字で最大を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['remarks' => str_repeat('あ', 501)]);

        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.remarks')
            ->where('errors.remarks', '備考は、500文字以下で指定してください。')
        );
    }



    // 新規登録のinspection_scheduled_dateのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションinspection_scheduled_dateが空で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['inspection_scheduled_date' => ''] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.inspection_scheduled_date')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションinspection_scheduled_dateが今日より過去の日付で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 昨日の日付
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'inspection_scheduled_date' => $yesterday
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.inspection_scheduled_date')
            ->where('errors.inspection_scheduled_date', '点検予定日は取得年月日以降の日付を入力してください')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションinspection_scheduled_dateが今日当日で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 今日の日付
        $today = Carbon::now()->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'inspection_scheduled_date' => $today
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.inspection_scheduled_date')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションinspection_scheduled_dateがdate_of_acquisitionから3年以内で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年後の日付
        $threeYearsLater = Carbon::now()->addYears(3)->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'inspection_scheduled_date' => $threeYearsLater 
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.inspection_scheduled_date')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションinspection_scheduled_dateがdate_of_acquisitionから3年と1日後で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年と1日後の日付
        $threeYearsAndOneDayLater = Carbon::now()->addYears(3)->addDay()->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'inspection_scheduled_date' => $threeYearsAndOneDayLater 
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.inspection_scheduled_date')
            ->where('errors.inspection_scheduled_date', '点検予定日は取得年月日から3年以内の日付を入力してください')
        );
    }



    // 新規登録のdisposal_scheduled_dateのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションdisposal_scheduled_dateが空で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', ['disposal_scheduled_date' => ''] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.disposal_scheduled_date')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdisposal_scheduled_dateが今日より過去の日付で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 昨日の日付
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'disposal_scheduled_date' => $yesterday
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.disposal_scheduled_date')
            ->where('errors.disposal_scheduled_date', '廃棄予定日は取得年月日以降の日付を入力してください')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdisposal_scheduled_dateが今日当日で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 今日の日付
        $today = Carbon::now()->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'disposal_scheduled_date' => $today
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.disposal_scheduled_date')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdisposal_scheduled_dateがdate_of_acquisitionから3年以内で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年後の日付
        $threeYearsLater = Carbon::now()->addYears(3)->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'disposal_scheduled_date' => $threeYearsLater 
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.disposal_scheduled_date')
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdisposal_scheduled_dateがdate_of_acquisitionから3年と1日後で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年と1日後の日付
        $threeYearsAndOneDayLater = Carbon::now()->addYears(3)->addDay()->format('Y-m-d');

        $response = $this->from('items/create')->post('items', [
            'date_of_acquisition' => $date_of_acquisition,
            'disposal_scheduled_date' => $threeYearsAndOneDayLater 
        ] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.disposal_scheduled_date')
            ->where('errors.disposal_scheduled_date', '廃棄予定日は取得年月日から3年以内の日付を入力してください')
        );
    }
}
