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
use App\Services\ManagementIdService;
use App\Services\QrCodeService;
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

class StoreMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();

        // フェイクの画像ファイルを作成
        $this->fakeImage = UploadedFile::fake()->image('test_image.jpg');

        // ImageServiceのモックを作成
        $this->imageService = Mockery::mock(ImageService::class);
        $this->imageService->shouldReceive('resizeUpload')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof UploadedFile && $arg->getClientOriginalName() === 'test_image.jpg';
            }))
            ->andReturn('mocked_image.jpg');
        // サービスコンテナにモックを登録
        $this->app->instance(ImageService::class, $this->imageService);
    }

    /** @test */
    function 備品新規登録画面で備品を登録できる、消耗品の時()
    {
        // 備品が新規作成された裏でItemObserverによってedithistoriesテーブルにもデータが保存される

        $category = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $categories = Category::factory()->count(10)->create(); //残りのカテゴリ
        $categories = $categories->concat($category); //全てのカテゴリ
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create(); //admin権限
        $this->actingAs($user);

        // モックを作成
        $mock = Mockery::mock(ManagementIdService::class);
        $mock->shouldReceive('generate')
                ->once()
                ->with($category->id)
                ->andReturn('CO-1111');
        // サービスコンテナで呼び出す
        $this->instance(ManagementIdService::class, $mock);

        $validData = [
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image_file' => $this->fakeImage,
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

        $response = $this->from('items/create')
            ->post(route('items.store'), $validData);
        
        // dd($response->status()); // ステータスコード
        // dd($response->getContent()); // レスポンスの内容を確認
        
        $response->assertStatus(302);
        \Log::debug('$response', $response);
        $response->assertRedirect('items');

        $this->assertDatabaseHas('items', [
            'management_id' => 'CO-1111',
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image1' => 'mocked_image.jpg',
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => 1, //true
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
            // 'qrcode' => null, // QRコードは実際には生成される
        ]);

        $item = Item::where('management_id', 'CO-1111')->first();
        dump(Item::where('management_id', 'CO-1111')->first()->id);


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

        // その後ItemObserverによるedithistoriesテーブルへの保存をアサ―ト
        $this->assertDatabaseHas('edithistories', [
            'edit_mode' => 'normal',
            'operation_type' => 'store',
            'item_id' => $item->id,
            'edited_field' => null,
            'old_value' => null,
            'new_value' => null,
            'edit_user' => Auth::user()->name,
        ]);
    }

    /** @test */
    function 備品新規登録画面で備品を登録できる、消耗品以外の時()
    {
        // 備品が新規作成された裏でItemObserverによってedithistoriesテーブルにもデータが保存される

        $category = Category::factory()->create([
            'id' => 2,
            'name' => 'IT機器'
        ]);
        $categories = Category::factory()->count(10)->create(); //残りのカテゴリ
        $categories = $categories->concat($category); //全てのカテゴリ
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create(); //admin権限
        $this->actingAs($user);   

        // モックを作成
        $mock = Mockery::mock(ManagementIdService::class);
        $mock->shouldReceive('generate')
                ->once()
                ->with($category->id)
                ->andReturn('CO-1111');
        // サービスコンテナで呼び出す
        $this->instance(ManagementIdService::class, $mock);

        $validData = [
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image_file' => $this->fakeImage,
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

        $response = $this->from('items/create')
            ->post(route('items.store'), $validData);

        $response->assertRedirect('items');

        $this->assertDatabaseHas('items', [
            'management_id' => 'CO-1111',
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image1' => 'mocked_image.jpg',
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => null, //IT機器なのでminimum_stockはnull
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
            'item_id' => $item->id,
            'inspection_scheduled_date' =>  '2024-09-10',
        ]);

        // disposalsテーブルに保存されているか確認
        $this->assertDatabaseHas('disposals', [
            'item_id' => $item->id,
            'disposal_scheduled_date' => '2024-09-20',
        ]);

        // その後ItemObserverによるedithistoriesテーブルへの保存をテスト
        $this->assertDatabaseHas('edithistories', [
            'edit_mode' => 'normal',
            'operation_type' => 'store',
            'item_id' => $item->id,
            'edited_field' => null,
            'old_value' => null,
            'new_value' => null,
            'edit_user' => Auth::user()->name,
        ]);
    }
}
