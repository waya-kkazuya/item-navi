<?php

namespace Tests\Feature\Http\Controllers;

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
use App\Models\Inspection;
use App\Models\EditReason;
use Faker\Factory as FakerFactory;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use App\Services\ManagementIdService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Console\DumpCommand;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;
    // use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    /** @test */
    function 備品一覧_paginateオブジェクトを渡す()
    {
        // リレーションのダミーデータを作成
        // $categories = Category::all(); all()は使えない
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();



        // 各コレクションの要素数を出力
        echo 'Categories count: ' . $categories->count() . PHP_EOL;
        echo 'Units count: ' . $units->count() . PHP_EOL;
        echo 'Usage Statuses count: ' . $usage_statuses->count() . PHP_EOL;
        echo 'Locations count: ' . $locations->count() . PHP_EOL;
        echo 'Acquisition Methods count: ' . $aquisition_methods->count() . PHP_EOL;


        
        // 1件作成
        // Observerでのmanagement_idの生成はやめてServiceに移行
        // $items = Item::factory(20)->create();
        // $items = null; // 参照渡し
        // Item::withoutEvents(function () use (&$items ,$categories, $units, $usage_statuses, $locations, $aquisition_methods) {
        $items = Item::factory()->count(20)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            // 'name' => 'テストアイテム', // nameを上書きできる
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id
        ]);
        // });

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        // $user = User::factory()->create([
        //     'role' => '1',
        // ]);

        $this->actingAs($user);

        // $items = Item::all();

        // dd($items);

        $response = $this->get('/items')
            ->assertOk();

        // dd($response->getContent());

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Index') // Vueコンポーネント
            ->has('items.data', 20)// itemsが1件存在することを確認
            ->has('items.data', fn ($data) => $data
                ->each(fn ($item)=> $item
                    ->hasAll([
                        'id',
                        'management_id',
                        'name',
                        'category_id',
                        'image1',
                        'stock',
                        'unit_id',
                        'minimum_stock',
                        'notification',
                        'usage_status_id',
                        'end_user',
                        'location_of_use_id',
                        'storage_location_id',
                        'acquisition_method_id',
                        'acquisition_source',
                        'price',
                        'date_of_acquisition',
                        'manufacturer',
                        'product_number',
                        'remarks',
                        'qrcode',
                        'deleted_at',
                        'created_at',
                        'image_path1', //画像名から加工した画像パス
                        'pending_inspection_date',
                        'category',
                        'unit',
                        'usage_status',
                        'location_of_use',
                        'storage_location',
                        'acquisition_method',
                        'inspections',
                        'disposal',
                    ])
                )
            )
            // ->where('items.0.name', 'テストアイテム') // itemsの最初の要素のnameがテストアイテムであることを確認
        );


        //     ->has('category', fn ($category) => $category->hasAll(['id', 'name', 'created_at', 'updated_at']))  // categoryオブジェクトがid属性を持っていることを確認
        //     ->has('location_of_use', fn ($location) => $location->hasAll(['id', 'name', 'created_at', 'updated_at']))  // location_of_useオブジェクトがid属性を持っていることを確認
        //     ->has('storage_location', fn ($location) => $location->hasAll(['id', 'name', 'created_at', 'updated_at']))  // storage_locationオブジェクトがid属性を持っていることを確認
    }

    /** @test */
    function 廃棄済みの備品データを渡す()
    {
        // トグルボタンで切り替えたときに廃棄済みの備品データを渡す
        // APIのテスト
    }


    /** @test */
    function 備品の詳細画面を開く()
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        
        $item = Item::factory()->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            // 'name' => 'テストアイテム', // nameを上書きできる
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id
        ]);

        // カラムの値は上書きできる
        $pendingInspection = Inspection::factory()->create([
            'item_id' => $item->id,
            'status' => false //点検予定
        ]);

        $previousInspection = Inspection::factory()->create([
            'item_id' => $item->id,
            'status' => true //点検実行済み
        ]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        //  items/{item}部分にはidを有力
        $response = $this->get('/items/' . $item->id)
            ->assertOk();


        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Show') // Vueコンポーネント
            ->has('item', fn (Assert $page) => $page
                ->where('id', $item->id)
                ->etc()
            )
            ->where('pendingInspection.inspection_date', $pendingInspection->inspection_date->format('Y-m-d')) //日付は文字列の形式に変換、ダミーデータとの整合性
            ->where('pendingInspection.scheduled_date', $pendingInspection->scheduled_date->format('Y-m-d'))
            ->where('previousInspection.inspection_date', $previousInspection->inspection_date->format('Y-m-d'))
            ->where('previousInspection.scheduled_date', $previousInspection->scheduled_date->format('Y-m-d'))
            ->where('userName', $user->name)
        );
    }
    
    /** @test */
    function 備品詳細画面から備品編集画面を開く()
    {
        
    }

    // /** @test */
    // function 備品編集画面で備品を編集更新する()
    // {
        
    // }
    
    /** @test */
    function 備品新規登録画面をrole1で開く()
    {
        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        // $user = User::factory()->role(5)->create();
        // $user = User::factory()->role(9)->create();
        // $user = User::factory()->create();
        // dd('role', $user->role);
        $this->actingAs($user);

        $response = $this->get('items/create')
            ->assertOk();
    }

    /** @test */
    function 備品新規登録画面を開く()
    {
        $roles = [
            'admin' => 1,
            'staff' => 5,
            'user' => 9,
        ];

        foreach ($roles as $roleName => $role) {
            $user = User::factory()->role($role)->create();
            $this->actingAs($user);

            $response = $this->get('items/create');

            if ($role === 9) {
                $response->assertStatus(403);
            } else {
                $response->assertOk();
            }
        }
    }


    // /** @test */
    // function Categoryを取得できる()
    // {
    //     $category = Category::factory()->create();
    //     dump($category->id);

    // }


    /** @test */
    function 備品新規登録画面で備品を登録できる()
    {
        // ※注意
        // 備品が新規作成された裏でItemObserverによってedithistoriesテーブルにもデータが保存される

        //世界の構築が不十分
        // dump(Category::factory()->create(['id' => 1]));
        // $category = Category::factory()->create(['id' => 1]);
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);   

        // モックを作成
        $mock = Mockery::mock(ManagementIdService::class);
        $mock->shouldReceive('generate')->once()->with(1)->andReturn('CO-1111');
        // サービスコンテナで呼び出す
        $this->instance(ManagementIdService::class, $mock);



        // ※注意
        // フロントから送られてくるデータを適切に模倣しないといけいない
        $validData = [
            // 'management_id' => 'CO-1111',
            'name' => 'ペーパータオル',
            'category_id' => $categories->first()->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->last()->id,
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
        ];

        // dump(array_merge($validData, $inspectionData, $disposalData));

        // 新規作成時items
        // $response = $this->post(route('items.store'), $validData);
        // $response = $this->from('items/create')->post('items', array_merge($validData, $inspectionData, $disposalData));
        // $response = $this->from('items/create')->post('items', $validData);
        $response = $this->from('items/create')->post(route('items.store'), $validData);

        $response->assertRedirect('items');

        // $this->assertDatabaseHas('items', array_merge($validData, ['management_id' => 'CO-1111']));
        $this->assertDatabaseHas('items', [
            'management_id' => 'CO-1111',
            'name' => 'ペーパータオル',
            'category_id' => $categories->first()->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->last()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-03',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
            'qrcode' => null,
        ]);

        $item = Item::where('management_id', 'CO-1111')->first();
        dump(Item::where('management_id', 'CO-1111')->first()->id);


        // inspectionsテーブルに保存されているか確認
        $this->assertDatabaseHas('inspections', [
            'item_id' => Item::where('management_id', 'CO-1111')->first()->id,
            'inspection_scheduled_date' =>  '2024-09-10',
        ]);

        // disposalsテーブルに保存されているか確認
        $this->assertDatabaseHas('disposals', [
            'item_id' => Item::where('management_id', 'CO-1111')->first()->id,
            'disposal_scheduled_date' => '2024-09-20',
        ]);

        // その後ItemObserverによるedithistoriesテーブルへの保存をテスト
        $this->assertDatabaseHas('edithistories', [
            'edit_mode' => 'normal',
            'operation_type' => 'store',
            'item_id' => Item::where('management_id', 'CO-1111')->first()->id,
            'edited_field' => null,
            'old_value' => null,
            'new_value' => null,
            'edit_user' => Auth::user()->name,
        ]);
    }

    /** @test */
    function 備品新規登録画面でバリデーションエラーメッセージが表示される()
    {
        // データ検証　準備
        // DB保存
        // リダイレクト　200をチェックしてから30
        // $this->withoutExceptionHandling();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 認証されたユーザー情報をデバッグ
        dump(auth()->user());

        $url = 'items';

        // リファラーでなくともgetでアクセスしておけばそこからpostしたことになる
        $this->get('items/create');

        // リファラーと呼ばれる ->from()部分が大事
        $response  = $this->post($url, ['name' => str_repeat('あ', 21)]);
        // $response  = $this->from('items/create')->post($url, ['name' => str_repeat('あ', 21)]);
        $response->assertRedirect('items/create');
        $response->assertStatus(302); //リダイレクトステータス
        // $response->assertSessionHasErrors(['name' => '指定']); // セッションにエラーメッセージがあることを確認
        // $response->assertInvalid(['name' => '指定']);

        // // リダイレクト後のレスポンスを取得
        $response = $this->followRedirects($response);

        // レスポンスの内容をデバッグ
        // dump($response->getContent());

        // 繋げて書けば20文字の時も検証できる
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create') // 対象のコンポーネントを指定
            ->has('errors.name') // エラーメッセージが存在することを確認　エラーが発生しなければerrors.nameは存在しない
            ->where('errors.name', '名前は、20文字以下で指定してください。') // エラーメッセージの内容を確認
            // ->where('errors.name', '名前は必ず指定してください。') // エラーメッセージの内容を確認
        );
    }


    // 新規登録のnameのバリデーションのテスト

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
        // $response = $this->from('items/create')->post($url, array_merge($data, [
        //     // 'name' => 'ペーパータオル',
        //     'category_id' => 1,
        //     'image1' => null,
        //     'stock' => 10,
        //     'unit_id' => 1,
        //     'minimum_stock' => 2,
        //     'notification' => true,
        //     'usage_status_id' => 1,
        //     'end_user' => '山田',
        //     'location_of_use_id' => 1,
        //     'storage_location_id' => 2,
        //     'acquisition_method_id' => 1,
        //     'acquisition_source' => 'Amazon',
        //     'price' => 500,
        //     'date_of_acquisition' => '2024-09-03',
        //     'manufacturer' => null,
        //     'product_number' => null,
        //     'remarks' => 'テストコードです',
        //     'qrcode' => null,
        //     'inspection_scheduled_date' => '2024-09-10',
        //     'disposal_scheduled_date' => '2024-09-20'
        // ]));
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
            'nameが1文字以下の時はエラーメッセージが出る' => [
                ['name' => str_repeat('あ', 1), 'expectedError' => null]
            ],
            'nameが21文字の時はエラーメッセージが出る' => [
                ['name' => str_repeat('あ', 21), 'expectedError' => '名前は、20文字以下で指定してください。']
            ],
            'nameが20文字の時はエラーメッセージが出ない' => [
                ['name' => str_repeat('あ', 20), 'expectedError' => null]
            ],
        ];
    }


    /**
     * @dataProvider categoryIdSuccessCaseValidationProvider
     * @test
     */
    public function 備品新規登録categoryIdバリデーション正常系モデルとして残す($data)
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $url = 'items';
        // $dataのみにした
        $response = $this->from('items/create')->post($url, $data);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.category_id')
            ->dump()
        );
    }

    public static function categoryIdSuccessCaseValidationProvider()
    {
        return [
            'category_idが1の時はエラーメッセージが出ない' => [
                ['category_id' => 1, 'expectedError' => null]
            ],
            'category_idが11の時はエラーメッセージが出ない'=> [
                ['category_id' => 10, 'expectedError' => null]
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

        $response = $this->from('items/create')->post('items', ['category_id' => 0]);
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

        $response = $this->from('items/create')->post('items', ['category_id' => 1]);
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

        $response = $this->from('items/create')->post('items', ['category_id' => 11]);
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

        $response = $this->from('items/create')->post('items', ['category_id' => 12]);
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
        // 世界を構築
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 適切に保存されるデータでカテゴリが消耗品(catgery_id=1)の時
        $response = $this->from('items/create')->post('items', [
            'name' => 'ペーパータオル',
            'category_id' => 1,
            'image1' => null,
            'stock' => 10,
            'unit_id' => 1,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => 1,
            'end_user' => '山田',
            'location_of_use_id' => 1,
            'storage_location_id' => 2,
            'acquisition_method_id' => 1,
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
            'category_id' => 1,
            'minimum_stock' => 2
        ]);
    }

    /** @test */
    public function 備品新規登録バリデーションカテゴリが消耗品以外の時は値がminimum_stockはnullで保存される()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('items/create')->post('items', [
            'name' => 'ペーパータオル',
            'category_id' => 2,
            'image1' => null,
            'stock' => 10,
            'unit_id' => 1,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => 1,
            'end_user' => '山田',
            'location_of_use_id' => 1,
            'storage_location_id' => 2,
            'acquisition_method_id' => 1,
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
            'category_id' => 2,
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

        $response = $this->from('items/create')->post('items', ['unit_id' => 0]);
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

        $response = $this->from('items/create')->post('items', ['unit_id' => 1]);
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

        $response = $this->from('items/create')->post('items', ['unit_id' => 10]);
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

        $response = $this->from('items/create')->post('items', ['unit_id' => 11]);
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

        $response = $this->from('items/create')->post('items', ['usage_status_id' => 0]);
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

        $response = $this->from('items/create')->post('items', ['usage_status_id' => 1]);
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

        $response = $this->from('items/create')->post('items', ['usage_status_id' => 2]);
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

        $response = $this->from('items/create')->post('items', ['usage_status_id' => 3]);
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

        $url = 'items';
        $response = $this->from('items/create')->post($url, ['end_user' => '']);

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

        $url = 'items';
        $response = $this->from('items/create')->post($url, ['end_user' => str_repeat('あ', 1)]);

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

        $url = 'items';
        $response = $this->from('items/create')->post($url, ['end_user' => str_repeat('あ', 10)]);

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

        $response = $this->from('items/create')->post('items', ['location_of_use_id' => Location::min('id')]);
        // $response = $this->from('items/create')->post('items', ['location_of_use_id' => 1]);
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

        $response = $this->from('items/create')->post('items', ['location_of_use_id' => Location::max('id')]);
        // $response = $this->from('items/create')->post('items', ['location_of_use_id' => 12]);
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

        $response = $this->from('items/create')->post('items', ['location_of_use_id' => Location::max('id') + 1]);
        // $response = $this->from('items/create')->post('items', ['location_of_use_id' => 13]);
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
        // $response = $this->from('items/create')->post('items', ['storage_location_id' => 1]);
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
        // $response = $this->from('items/create')->post('items', ['storage_location_id' => 13]);
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
        // $response = $this->from('items/create')->post('items', ['acquisition_method_id' => 1]);
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
    public function 備品新規登録バリデーションacquisition_sourceが空欄()
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
    public function 備品新規登録バリデーションacquisition_sourceが1文字()
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
    public function 備品新規登録バリデーションacquisition_sourceが20文字()
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
    public function 備品新規登録バリデーションacquisition_sourceが21文字()
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
    public function 備品新規登録バリデーションpriceが空欄()
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
    public function 備品新規登録バリデーションpriceが文字列()
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
    public function 備品新規登録バリデーションpriceがマイナスの数値()
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
    public function 備品新規登録バリデーションpriceが0()
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
    public function 備品新規登録バリデーションpriceが100万()
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
    public function 備品新規登録バリデーションpriceが100万1()
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
    public function 備品新規登録バリデーションdate_of_acquisitionが空()
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
    public function 備品新規登録バリデーションdate_of_acquisitionが今日より未来()
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
    public function 備品新規登録バリデーションdate_of_acquisitionが今日当日()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 今日の日付
        $today = Carbon::now()->format('Y-m-d');

        dump($today);

        $response = $this->from('items/create')->post('items', ['date_of_acquisition' => $today] );
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->missing('errors.date_of_acquisition')
            ->dump()
        );
    }

    /** @test */
    public function 備品新規登録バリデーションdate_of_acquisitionが今日より過去()
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
            ->dump()
        );
    }


    // 新規登録のmanufacturerのバリデーションのテスト
    /** @test */
    public function 備品新規登録バリデーションmanufacturer空欄のまま保存できる()
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
    public function 備品新規登録バリデーションmanufacturer1文字で保存できる()
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
    public function 備品新規登録バリデーションmanufacturer20文字で保存できる()
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
    public function 備品新規登録バリデーションmanufacturer21文字で保存できない()
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
    public function 備品新規登録バリデーションproduct_number空欄のまま保存できる()
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
    public function 備品新規登録バリデーションproduct_number1文字で保存できる()
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
    public function 備品新規登録バリデーションproduct_number30文字で保存できる()
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
    public function 備品新規登録バリデーションproduct_number31文字で保存できない()
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
    public function 備品新規登録バリデーションremarks空欄のまま保存できる()
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
    public function 備品新規登録バリデーションremarks1文字で保存できる()
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
    public function 備品新規登録バリデーションremarks500文字で保存できる()
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
    public function 備品新規登録バリデーションremarks501文字で保存できない()
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
    public function 備品新規登録バリデーションinspection_scheduled_dateが空()
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
    public function 備品新規登録バリデーションinspection_scheduled_dateが今日より過去()
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
    public function 備品新規登録バリデーションinspection_scheduled_dateが今日当日()
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
    public function 備品新規登録バリデーションinspection_scheduled_dateがdate_of_acquisitionから3年以内()
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
    public function 備品新規登録バリデーションinspection_scheduled_dateがdate_of_acquisitionから3年と1日後()
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
    public function 備品新規登録バリデーションdisposal_scheduled_dateが空()
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
    public function 備品新規登録バリデーションdisposal_scheduled_dateが今日より過去()
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
    public function 備品新規登録バリデーションdisposal_scheduled_dateが今日当日()
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
    public function 備品新規登録バリデーションdisposal_scheduled_dateがdate_of_acquisitionから3年以内()
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
    public function 備品新規登録バリデーションdisposal_scheduled_dateがdate_of_acquisitionから3年と1日後()
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







    /** @test */
    function 備品編集画面で備品を編集更新できる()
    {
        // 世界の構築
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        $edit_reasons = EditReason::factory()->count(5)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();
        
        // dump($item->management_id);
        dump($item);
        
        $validData = [
            'name' => 'ペーパータオル',
            'category_id' => 1,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->last()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-03',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20',
            'edit_reeason_id' => $edit_reasons->first()->id,
            'edit_reason_text' => null,
        ];


        // 更新リクエストを送信
        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), $validData);
        $response->assertRedirect(route('items.show', $item));
        $response->assertStatus(302);

        $this->assertDatabaseHas('items', [
            'name' => 'ペーパータオル',
            'category_id' => 1,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->last()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-03',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
        ]);

        // 更新されたことのチェック
        $item->refresh();
        $this->assertSame('ペーパータオル', $item->name);
        $this->assertSame(1, $item->category_id);
        $this->assertSame(10, $item->stock);
        $this->assertSame($units->first()->id, $item->unit_id);
        $this->assertSame(2, $item->minimum_stock);
        $this->assertSame(true, (bool) $item->notification);
        $this->assertSame('山田', $item->end_user);
        $this->assertSame($locations->first()->id, $item->location_of_use_id);
        $this->assertSame($locations->last()->id, $item->storage_location_id);
        $this->assertSame($aquisition_methods->first()->id, $item->acquisition_method_id);
        $this->assertSame('Amazon', $item->acquisition_source);
        $this->assertSame(500, $item->price);
        $this->assertSame(null, $item->manufacturer);
        $this->assertSame(null, $item->product_number);
        $this->assertSame('テストコードです', $item->remarks);
        
        $this->assertDatabaseCount('items', 1);


        // inspectionsテーブルに保存されているか確認
        $this->assertDatabaseHas('inspections', [
            'item_id' => $item->id,
            'inspection_scheduled_date' =>  '2024-09-10',
        ]);

        // disposalsテーブルに保存されているか確認
        $this->assertDatabaseHas('disposals', [
            'item_id' => $item->id,
            'disposal_scheduled_date' => '2024-09-20',
        ]);

        // ItemObserverのupdatedメソッドで保存される
        // 更新されたカラムの分だけforeachを回すべきか
        $this->assertDatabaseHas('edithistories', [
            'edit_mode' => 'normal',
            'operation_type' => 'update',
            'item_id' => $item->id,
            // 'edited_field' => $field,
            // 'old_value' => $oldValue,
            // 'new_value' => $newValue,
            'edit_user' =>  $user->name ?? '',
            'edit_reason_id' => $edit_reasons->first()->id, //プルダウン
            'edit_reason_text' => null, //その他テキストエリア
        ]);

        // 作成したデータを19項目の差異が更新がある
        $this->assertDatabaseCount('edithistories', 19);
    }






    // 編集更新のnameのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションnameが空欄では保存できない()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')->put(route('items.update', $item), ['name' => '']);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.name')
            ->where('errors.name', '名前は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションnameが1文字()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')->put(route('items.update', $item), ['name' => str_repeat('あ', 1)]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.name')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションnameが20文字()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')->put(route('items.update', $item), ['name' => str_repeat('あ', 20)]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.name')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションnameが21文字()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')->put(route('items.update', $item), ['name' => str_repeat('あ', 21)]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.name')
            ->where('errors.name', '名前は、20文字以下で指定してください。')
            // ->dump()
        );
    }








    /** @test */
    public function 備品詳細画面で廃棄モーダルで廃棄処理できる()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成
        $item = Item::factory()->create();

        $validData = [
            'disposal_date' => '2024-09-03',
            'disposal_person' => $user->name,
            'details' => 'あいうえお'
        ];

        // 備品をソフトデリート
        $response = $this->put(route('dispose_item.disposeItem', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認
        $response->assertRedirect('items');

        $this->assertDatabaseHas('disposals', [
            'disposal_date' => '2024-09-03',
            'disposal_person' => $user->name,
            'details' => 'あいうえお'
        ]);

        // ソフトデリートされたことを確認
        $this->assertSoftDeleted('items', ['id' => $item->id]);

        // ソフトデリートされた備品が取得できないことを確認
        $this->assertNull(Item::find($item->id));

        // ソフトデリートされた備品がwithTrashedで取得できることを確認
        $this->assertNotNull(Item::withTrashed()->find($item->id));


        // 廃棄した後ItemObserverによってeidithistorieテーブルに廃棄履歴が保存される
        $this->assertDatabaseHas('edithistories', [
            'edit_mode' => 'normal',
            'operation_type' => 'soft_delete',
            'item_id' => $item->id,
            'edited_field' => null,
            'old_value' => null,
            'new_value' => null,
            'edit_user' => Auth::user()->name,
        ]);

    }

    // Authが使える
    // 備品廃棄モーダルでのバリデーションテスト






    /** @test */
    function 備品詳細画面で点検モーダルで点検処理できる_点検予定日のレコードがない場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成
        $item = Item::factory()->create();

        $validData = [
            'inspection_date' => '2024-09-03',
            'inspection_person' => $user->name,
            'details' => 'あいうえお'
        ];

        // 備品をソフトデリート
        $response = $this->put(route('inspect_item.inspectItem', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認

        $this->assertDatabaseHas('inspections', [
            'inspection_date' => '2024-09-03',
            'inspection_person' => $user->name,
            'details' => 'あいうえお'
        ]);
    }

    /** @test */
    function 備品詳細画面で点検モーダルで点検処理できる_点検予定日のレコードがある場合()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成
        $item = Item::factory()->create();

        $validData = [
            'inspection_date' => '2024-09-03',
            'inspection_person' => $user->name,
            'details' => 'あいうえお'
        ];

        // inspection_scheduled_dateが登録されていてstatusがfalseの場合、
        // 項目を上書きする
        $inspection = Inspection::factory()->create([
            'item_id' => $item->id,
            'inspection_scheduled_date' => '2024-09-01',
            'inspection_date' => null,
            'status' => false,
            'inspection_person' => null,
            'details' => null
        ]);

        // 備品をソフトデリート
        $response = $this->put(route('inspect_item.inspectItem', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認

        $this->assertDatabaseHas('inspections', [
            'inspection_date' => '2024-09-03',
            'inspection_person' => $user->name,
            'details' => 'あいうえお'
        ]);

        // 先にinspectionが存在した場合更新されているかをアサ―ト
        $inspection->refresh();
        $this->assertSame('2024-09-03', $inspection->inspection_date);
        $this->assertSame($user->name, $inspection->inspection_person);
        $this->assertSame('あいうえお', $inspection->details);
    }


    // User権限で出来ないことをテスト
    // ページへのアクセス
    // 権限のない操作
    /** @test */
    function Userは備品を管理できない()
    {
        
    }

    
    /** @test */
    function ゲストは備品を管理できない()
    {   
        $loginUrl = 'login';
        // $user = User::factory()->role(1)->create();
        // $this->actingAs($user);

        $item = Item::factory()->create();

        // ゲスト用のリダイレクトのアサ―ト
        $this->get('items')->assertRedirect($loginUrl);
        $this->get('items/create')->assertRedirect($loginUrl);
        $this->from('items/create')->post('items', [])->assertRedirect($loginUrl);
        $this->get('items/edit')->assertRedirect($loginUrl);
        $this->from('items/edit')->patch(route('items.update', $item->id), [])
            ->assertRedirect($loginUrl);
            // ->assertForbidden();
        $this->from('items/show')->delete('items/' . $item->id)
            ->assertRedirect($loginUrl);
        
   
    }


    // StockTransactionControllerのテスト
    // 後でファイルに切り出し

    /** @test */
    function 入出庫モーダルで出庫処理が出来る()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => 1,
            'stock' => 10,
            'minimum_stock' => 2
        ]);

        $validData = [
            'item_id' => $item->id,
            'transaction_type' => '出庫',
            'transaction_date' => '2024-9-3',
            'operator_name' => $user->name,
            'quantity' => 3,
        ];

        // 備品をソフトデリート
        $response = $this->put(route('decreaseStock', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $item->id,
            'transaction_type' => '出庫',
            'transaction_date' => '2024-9-3',
            'operator_name' => $user->name,
            'quantity' => 3,
        ]);
    }


    // 在庫数以下にはquantityを出来ないバリデーションRulesがStockLimit




    /** @test */
    function 入出庫モーダルで入庫処理が出来る()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);


    }

}
