<?php

namespace Tests\Feature\Http\Controllers\ItemController\Index;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use App\Models\Category;
use App\Models\User;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Location;
use App\Models\UsageStatus;
use App\Models\AcquisitionMethod;
use App\Models\Edithistory;
use App\Models\Inspection;
use App\Models\EditReason;
use App\Models\RequestStatus;
use App\Models\StockTransaction;
use App\Services\ImageService;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use App\Services\ManagementIdService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Console\DumpCommand;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Inertia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;


class IndexMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }


    /** @test */
    function 備品一覧画面表示用のpaginateオブジェクトのデータを渡す()
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

        $items = Item::factory()->count(20)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            // 'name' => 'テストアイテム', // nameを上書きできる
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null //ソフトデリートされていない
        ]);

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/items')
            ->assertOk();
        \Log::info('レスポンス: ' . $response->status()); // レスポンスのステータスコードをログに出力

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Index')
            ->has('items.data', 20)
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
                        'inspection_scheduled_date',
                        'category',
                        'unit',
                        'usage_status',
                        'location_of_use',
                        'storage_location',
                        'acquisition_method',
                        'inspections',
                        'disposal',
                    ])
                    ->has('category', fn ($category) => $category
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('unit', fn ($unit) => $unit
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('location_of_use', fn ($location) => $location
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('storage_location', fn ($location) => $location
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('acquisition_method', fn ($acquisition_method) => $acquisition_method
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('usage_status', fn ($usage_status) => $usage_status
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    
                )
            )
        );
    }

    /** @test */
    function 廃棄済みの備品一覧画面表示用のpaginateオブジェクトのデータを渡す()
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

        $items = Item::factory()->count(20)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            // 'name' => 'テストアイテム', // nameを上書きできる
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => $this->faker->date() //ソフトデリートされた（廃棄された）
        ]);

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->get('/items?disposal=true')
            ->assertOk();
        \Log::info('レスポンス: ' . $response->status()); // レスポンスのステータスコードをログに出力

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Index')
            ->has('items.data', 20)
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
                        'inspection_scheduled_date',
                        'category',
                        'unit',
                        'usage_status',
                        'location_of_use',
                        'storage_location',
                        'acquisition_method',
                        'inspections',
                        'disposal',
                    ])
                    ->where('deleted_at', fn ($deletedAt) => (bool)strtotime($deletedAt)) // `deleted_at` が有効な日付であることを確認
                    ->has('category', fn ($category) => $category
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('unit', fn ($unit) => $unit
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('location_of_use', fn ($location) => $location
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('storage_location', fn ($location) => $location
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('acquisition_method', fn ($acquisition_method) => $acquisition_method
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                    ->has('usage_status', fn ($usage_status) => $usage_status
                        ->hasAll(['id', 'name', 'created_at', 'updated_at'])
                    )
                )
            )
        );
    }
}
