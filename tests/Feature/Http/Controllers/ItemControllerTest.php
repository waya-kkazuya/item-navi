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

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

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
    function 備品新規登録画面を開く()
    {
        // adminユーザーを作成
        // $user = User::factory()->role(1)->create();
        // $this->actingAs($user);

        $response = $this->get('items/create')
            ->assertOk();
    }

 
    function 備品新規登録画面で備品を登録する()
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

        // リファラーと呼ばれる ->from()部分が大事
        $response  = $this->from('items/create')->post($url, ['name' => str_repeat('あ', 21)]);
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
    public function 備品新規登録画面でバリデーションname($name, $expectedError)
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $url = 'items';
        $response = $this->from('items/create')->post($url, ['name' => $name]);
        $response->assertRedirect('items/create');
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        // $response->assertInertia(fn (Assert $page) => $page
        //     ->component('Items/Create')
        //     ->has('errors.name', $expectedError !== null)
        //     ->where('errors.name', $expectedError)
        // );

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Create')
            ->when($expectedError !== null, fn ($page) => $page
                ->has('errors.name')
                ->where('errors.name', $expectedError)
            )
            ->when($expectedError === null, fn ($page) => $page
                ->missing('errors.name')
            )
        );

    }

    public static function nameValidationProvider()
    {
        return [
            '21文字の時はエラーメッセージが出る' => [str_repeat('あ', 21), '名前は、20文字以下で指定してください。'],
            '20文字の時はエラーメッセージが出ない' => [str_repeat('あ', 20), null],
            '空文字の時はエラーメッセージが出る' => ['', '名前は必ず指定してください。'],
        ];
    }

}
