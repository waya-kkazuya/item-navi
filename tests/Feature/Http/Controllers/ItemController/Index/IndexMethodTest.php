<?php

namespace Tests\Feature\Http\Controllers\ItemController\Index;

use App\Models\AcquisitionMethod;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Unit;
use App\Models\UsageStatus;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexMethodTest extends TestCase
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
    public function 備品一覧画面表示用のpaginateオブジェクトのデータを渡す()
    {
        // Arrange Act Assert を明示する

        // リレーションのダミーデータを作成
        // $categories = Category::all(); all()は使えない
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $items = Item::factory()->count(20)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            // 'name' => 'テストアイテム', // nameを上書きできる
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null, // ソフトデリートされていない
        ]);

        // adminユーザーでログイン
        $this->actingAs(User::factory()->role(1)->create());

        $response = $this->get('/items')
            ->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Index')
            ->has('items.data', 20)
            ->has('items.data', fn ($data) => $data
                ->each(fn ($item) => $item
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
                        'image_path1', // 画像名から加工した画像パス
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
    public function 廃棄済みの備品一覧画面表示用のpaginateオブジェクトのデータを渡す()
    {
        // リレーションのダミーデータを作成
        // $categories = Category::all(); all()は使えない
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $items = Item::factory()->count(20)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            // 'name' => 'テストアイテム', // nameを上書きできる
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => $this->faker->date(), // ソフトデリートされた（廃棄された）
        ]);

        // adminユーザーでログイン
        $this->actingAs(User::factory()->role(1)->create());

        $response = $this->get('/items?disposal=true')
            ->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Index')
            ->has('items.data', 20)
            ->has('items.data', fn ($data) => $data
                ->each(fn ($item) => $item
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
                        'image_path1', // 画像名から加工した画像パス
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
                    ->where('deleted_at', fn ($deletedAt) => (bool) strtotime($deletedAt)) // `deleted_at` が有効な日付であることを確認
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
    public function category_idで備品を絞り込める(): void
    {
        $targetCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        Item::factory()->count(3)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $targetCategory->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        Item::factory()->count(2)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $otherCategory->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        $this->actingAs(User::factory()->role(1)->create());

        $this->get("/items?categoryId={$targetCategory->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 3)   // 対象カテゴリの3件だけ
            );
    }

    /** @test */
    public function 存在しないcategory_idでは絞り込まれず全件返る(): void
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $items = Item::factory()->count(5)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null, // ソフトデリートされていない
        ]);

        $this->actingAs(User::factory()->role(1)->create());

        $this->get('/items?categoryId=99999')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 5)   // 現状は全件返る
            );
    }

    /** @test */
    public function location_of_use_idで備品を絞り込める(): void
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $targetLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();

        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        Item::factory()->count(3)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $targetLocation->id,
            'storage_location_id' => $otherLocation->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        Item::factory()->count(2)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $otherLocation->id,
            'storage_location_id' => $otherLocation->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        $this->actingAs(User::factory()->role(1)->create());

        $this->get("/items?locationOfUseId={$targetLocation->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 3)   // 使用場所が対象の3件だけ
            );
    }

    /** @test */
    public function 存在しないlocation_of_use_idでは絞り込まれず全件返る(): void
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        Item::factory()->count(5)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        $this->actingAs(User::factory()->role(1)->create());

        $this->get('/items?locationOfUseId=99999')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 5)   // 現状は全件返る
            );
    }

    /** @test */
    public function storage_location_idで備品を絞り込める(): void
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $targetLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();

        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        Item::factory()->count(3)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $otherLocation->id,      // わざと別のを入れる
            'storage_location_id' => $targetLocation->id,    // 絞り込み対象
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        Item::factory()->count(2)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $otherLocation->id,
            'storage_location_id' => $otherLocation->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        $this->actingAs(User::factory()->role(1)->create());

        $this->get("/items?storageLocationId={$targetLocation->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 3)   // 保管場所が対象の3件だけ
            );
    }

    /** @test */
    public function 存在しないstorage_location_idでは絞り込まれず全件返る(): void
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        Item::factory()->count(5)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
        ]);

        $this->actingAs(User::factory()->role(1)->create());

        $this->get('/items?storageLocationId=99999')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 5)   // 現状は全件返る
            );
    }

    #[Test]
    #[DataProvider('arrayParamProvider')]
    public function プルダウンidに配列を渡しても500にならず全件返る(string $param): void
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $items = Item::factory()->count(5)->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null, // ソフトデリートされていない
        ]);

        // adminユーザーでログイン
        $this->actingAs(User::factory()->role(1)->create());

        $this->get("/items?{$param}[]=1&{$param}[]=2")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('items.data', 5));
    }

    public static function arrayParamProvider(): array
    {
        return [
            'category' => ['categoryId'],
            'location_of_use' => ['locationOfUseId'],
            'storage_location' => ['storageLocationId'],
        ];
    }

    #[Test]
    public function sort_orderに不正な値を渡してもエラーにならず新しい順で返る(): void
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        // 作成日の異なる2件。desc なら new が先頭に来るはず
        $old = Item::factory()->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
            'created_at' => now()->subDays(2),
        ]);

        $new = Item::factory()->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id,
            'deleted_at' => null,
            'created_at' => now(),
        ]);

        $this->actingAs(User::factory()->role(1)->create());

        $this->get('/items?sortOrder=invalid')
            ->assertOk()                       // 500 にならない
            ->assertInertia(fn (Assert $page) => $page
                ->has('items.data', 2)
                ->where('items.data.0.id', $new->id)   // 先頭が新しい方 = desc に倒れた証拠
            );
    }
}
