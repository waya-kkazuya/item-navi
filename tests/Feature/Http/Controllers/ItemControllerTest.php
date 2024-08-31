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
        // カテゴリとサプライヤーのダミーデータを作成
        $categories = Category::factory()->count(11)->create();
        // $categories = Category::all();;
        $units = Unit::factory()->count(10)->create();
        // $units = Unit::all();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        // $usage_statuses = UsageStatus::all();
        $locations = Location::factory()->count(12)->create();
        // $locations = Location::all();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        // $aquisition_methods = AcquisitionMethod::all();


        // 各コレクションの要素数を出力
        echo 'Categories count: ' . $categories->count() . PHP_EOL;
        echo 'Units count: ' . $units->count() . PHP_EOL;
        echo 'Usage Statuses count: ' . $usage_statuses->count() . PHP_EOL;
        echo 'Locations count: ' . $locations->count() . PHP_EOL;
        echo 'Acquisition Methods count: ' . $aquisition_methods->count() . PHP_EOL;

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();

        
        // 1件作成
        // Observerを無効にする
        $items = null; // 参照渡し
        Item::withoutEvents(function () use (&$items ,$categories, $units, $usage_statuses, $locations, $aquisition_methods) {
            $items = Item::factory()->count(20)->create([
                'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
                'name' => 'テストアイテム', // nameを上書きできる
                'category_id' => $categories->random()->id,
                'unit_id' => $units->random()->id,
                'usage_status_id' => $usage_statuses->random()->id,
                'location_of_use_id' => $locations->random()->id,
                'storage_location_id' => $locations->random()->id,
                'acquisition_method_id' => $aquisition_methods->random()->id
            ]);
        });

        $this->actingAs($user);

        // $items = Item::all();
        // dd($items);

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


        // // テストデータを生成、テストの世界を構築
        // $items = Item::factory(20)->create();

        // // 権限レベルが必要な値以上のユーザーを作成
        // $user = User::factory()->create([
        //     'role' => '1',
        // ]);


        // $this->actingAs($user)->get('items')
        //     ->assertOk()
        //     ->assertInertia(fn ($page) => $page->component('Items/Index')
        //     ->has('items.data', 20) // items.dataの数が20であることを確認
        //     ->has('items.data', fn ($data) => $data->each(fn ($item) => $item->hasAll([
        //         'id',
        //         'name',
        //         'category_id',
        //         'image01',
        //         'stock',
        //         'minimum_stock',
        //         'usage_status',
        //         'end_user',
        //         'location_of_use_id',
        //         'storage_location_id',
        //         'acquisition_category',
        //         'where_to_buy',
        //         'price',
        //         'date_of_acquisition',
        //         'inspection_schedule',
        //         'disposal_schedule',
        //         'manufacturer',
        //         'product_number',
        //         'remarks',
        //         'qrcode_path',
        //         'created_at'
        //     ])
        //     ->has('category', fn ($category) => $category->hasAll(['id', 'name', 'created_at', 'updated_at']))  // categoryオブジェクトがid属性を持っていることを確認
        //     ->has('location_of_use', fn ($location) => $location->hasAll(['id', 'name', 'created_at', 'updated_at']))  // location_of_useオブジェクトがid属性を持っていることを確認
        //     ->has('storage_location', fn ($location) => $location->hasAll(['id', 'name', 'created_at', 'updated_at']))  // storage_locationオブジェクトがid属性を持っていることを確認
        // )));         
    }


}
