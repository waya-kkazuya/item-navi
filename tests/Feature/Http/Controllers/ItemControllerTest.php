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
use Faker\Factory as FakerFactory;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use App\Services\ManagementIdService;
use Illuminate\Support\Facades\Auth;


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

    /** @test */
    function 備品編集画面で備品を編集更新する()
    {
        
    }
    
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

        // itemsテーブルだけでなく、inspectionsテーブルとdisposalsテーブルにも保存されているか確認が必要
        // inspectionsテーブルに保存されているか確認
        // $inspectionData = [
        //     'inspection_scheduled_date' => '2024-09-10',
        // ];
        // $this->post('inspections', array_merge($inspectionData, ['item_id' => $item->id]));
        $this->assertDatabaseHas('inspections', [
            'item_id' => Item::where('management_id', 'CO-1111')->first()->id,
            'inspection_scheduled_date' =>  '2024-09-10',
        ]);

        // disposalsテーブルに保存されているか確認
        // $disposalData = [
        //     'disposal_scheduled_date' => '2024-09-20',
        // ];
        // $this->post('inspections', array_merge($disposalData, ['item_id' => $item->id]));
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

        // dump($response->getContent());

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

    /**
     * @dataProvider nameValidationProvider
     * @test
     */
    public function 備品新規登録バリデーションname($data)
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $url = 'items';
        $response = $this->from('items/create')->post($url, array_merge($data, [
            // 'name' => 'ペーパータオル',
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
        ]));
        // $response = $this->from('items/create')->post($url, ['name' => $name]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        // 基本形
        // $response->assertInertia(fn (Assert $page) => $page
        //     ->component('Items/Create')
        //     ->has('errors.name', $expectedError !== null)
        //     ->where('errors.name', $expectedError)
        // );

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
            'nameが21文字の時はエラーメッセージが出る' => [
                ['name' => str_repeat('あ', 21), 'expectedError' => '名前は、20文字以下で指定してください。']
            ],
            'nameが20文字の時はエラーメッセージが出ない' => [
                ['name' => str_repeat('あ', 20), 'expectedError' => null]
            ],
        ];
    }


    /**
     * @dataProvider categoryIdValidationProvider
     * @test
     */
    public function 備品新規登録バリデーションcategoryId($data)
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
        $response = $this->from('items/create')->post($url, array_merge($data, [
            'name' => 'ペーパータオル',
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
        ]));
        // $response = $this->from('items/create')->post($url, ['name' => $name]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        // dd($response->inertiaPage()['prop']['errors']);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->when($data['expectedError'] !== null, fn ($page) => $page
                ->has('errors.category_id')
                ->where('errors.category_id', $data['expectedError'])
            )
            ->when($data['expectedError'] === null, fn ($page) => $page
                ->where('errors.category_id', null)
            )
            ->dump()
        );
    }

    public static function categoryIdValidationProvider()
    {
        // $maxCategoryId = Category::max('id');
        // $notExistCategoryId = $maxCategoryId + 1;
        // // dump($notExistCategoryId);

        return [
            'category_idが存在しないIDの時はエラーメッセージが出る' => [
                ['category_id' => 12, 'expectedError' => '選択されたカテゴリは正しくありません。']
            ],
            'category_idが正しいIDの時はエラーメッセージが出ない' => [
                ['category_id' => 1, 'expectedError' => null]
            ],
        ];
    }

    /** @test */
    public function 備品新規登録バリデーションcategoryId個別テスト()
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
        // $response = $this->from('items/create')->post($url, [
        //     'name' => 'ペーパータオル',
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
        // ]);
        $response = $this->from('items/create')->post($url, ['category_id' => 1]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
                ->missing('errors.category_id')
                // ->where('errors.category_id', null)
            )
            ->dump();
    }




    /**
     * @dataProvider stockValidationProvider
     * @test
     */
    public function 備品新規登録バリデーションstock($data)
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $url = 'items';
        $response = $this->from('items/create')->post($url, array_merge($data, [
            'name' => 'ペーパータオル',
            'image1' => null,
            // 'stock' => 10,
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
        ]));
        // $response = $this->from('items/create')->post($url, ['name' => $name]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->when($data['expectedError'] !== null, fn ($page) => $page
                ->has('errors.stock')
                ->where('errors.stock', $data['expectedError'])
            )
            ->when($data['expectedError'] === null, fn ($page) => $page
                ->missing('errors.stock')
            )
            ->dump()
        );
    }

    public static function stockValidationProvider()
    {
        return [
            'stockが-1の時はエラーメッセージが出る' => [
                ['stock' => -1, 'expectedError' => '在庫数には、0以上の数字を指定してください。']
            ],
            'stockが0の時はエラーメッセージが出ない' => [
                ['stock' => 0, 'expectedError' => null]
            ],
            'stockが200の時はエラーメッセージが出ない' => [
                ['stock' => 200, 'expectedError' => null]
            ],
            'stockが201の時はエラーメッセージが出る' => [
                ['stock' => 201, 'expectedError' => '在庫数には、200以下の数字を指定してください。']
            ],
        ];
    }


    /** @test */
    public function 備品新規登録バリデーションstock個別に確認()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // エラー原因はarray_merge()する$dataと配列の両方にcountがあって重複していた
        $url = 'items';
        $response = $this->from('items/create')->post($url, [
            'name' => 'ペーパータオル',
            'stock' => -1,
            'image1' => null,
            // 'stock' => 10,
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
        // $response = $this->from('items/create')->post($url, ['name' => $name]);
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        // dd($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->has('errors.stock')
            ->where('errors.stock','在庫数には、0以上の数字を指定してください。')
            ->dump()
        );
            // ->when($data['expectedError'] === null, fn ($page) => $page
            //     ->missing('errors.stock')

    }


    /**
     * @dataProvider unitIdValidationProvider
     * @test
     */
    public function 備品新規登録バリデーションunitId($data)
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
        $response = $this->from('items/create')->post($url, array_merge($data, [
            'name' => 'ペーパータオル',
            'image1' => null,
            'stock' => 10,
            // 'unit_id' => 1,
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
        ]));
        
        $response->assertRedirect('items/create'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->when($data['expectedError'] !== null, fn ($page) => $page
                ->has('errors.unit_id')
                ->where('errors.unit_id', $data['expectedError'])
            )
            ->when($data['expectedError'] === null, fn ($page) => $page
                ->where('errors.unit_id', null)
                ->missing('errors.unit_id')
            )
            ->dump()
        );
    }

    public static function unitIdValidationProvider()
    {
        return [
            'unit_idが0の時はエラーメッセージが出る' => [
                ['unit_id' => 0, 'expectedError' => '選択されたunit idは正しくありません。']
            ],
            'unit_idが1の時はエラーメッセージが出ない' => [
                ['unit_id' => 1, 'expectedError' => null]
            ],
            'unit_idが10の時はエラーメッセージが出ない' => [
                ['unit_id' => 10, 'expectedError' => null]
            ],
            'unit_idが11の時はエラーメッセージが出る' => [
                ['unit_id' => 11, 'expectedError' => '選択されたunit idは正しくありません。']
            ],
        ];
    }










    /** @test */
    function 備品編集時のバリデーションが表示される()
    {
        
    }


    /** @test */
    function 備品編集画面で備品を編集できる()
    {
        // 世界の構築
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();      

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();
        
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
            // 'inspection_scheduled_date' => '2024-09-10',
            // 'disposal_scheduled_date' => '2024-09-20'
        ];


        // 更新リクエストを送信
        $response = $this->from('items/edit')
            ->patch(route('items.update', $item->id), $validData);

        $response->assertRedirect('items/edit');

        // $this->assertDatabaseHas('items', array_merge($validData, ['management_id' => 'CO-1111']));

        // $item = Item::where('management_id', 'CO-1111')->first();
        // dump(Item::where('management_id', 'CO-1111')->first()->id);



    }


    /** @test */
    function 備品詳細画面で備品をソフトデリートできる()
    {
        
    }



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


}
