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
    }

    /** @test */
    function InterventionImageテスト()
    {
        // テスト用の画像ファイルを準備
        Storage::fake('public');
        $image = UploadedFile::fake()->image('test_image.jpg');

        try {
            // 画像を処理するコード
            $manager = new ImageManager(new Driver());
            $image = $manager->read($image->getPathname());
        } catch (\Intervention\Image\Exception\NotReadableException $e) {
            Log::error('Image not readable: ' . $e->getMessage());
            $this->fail('Image processing failed: ' . $e->getMessage());
        }
    
        $this->assertTrue(true);
    }

    /** @test */
    function 備品新規登録画面で備品を登録できる、消耗品の時()
    {
        // ※注意
        // 備品が新規作成された裏でItemObserverによってedithistoriesテーブルにもデータが保存される

        //世界の構築が不十分
        // dump(Category::factory()->create(['id' => 1]));
        $category = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        // $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);   

        // モックを作成
        $mock = Mockery::mock(ManagementIdService::class);
        $mock->shouldReceive('generate')
                ->once()
                ->with($category->id)
                ->andReturn('CO-1111');
        // サービスコンテナで呼び出す
        $this->instance(ManagementIdService::class, $mock);

        // テスト用の画像ファイルを準備
        // Storage::fake('public');
        // $fakeimage = UploadedFile::fake()->image('test_image.jpg');

        // ※注意
        // フロントから送られてくるデータを適切に模倣しないといけいない
        $validData = [
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            // 'image_file' => $image,
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

        // $response = $this->from('items/create')->post('items', $validData);
        $response = $this->from('items/create')->post(route('items.store'), $validData);
        // ステータスコード
        // dd($response->status());
        // レスポンスの内容を確認
        // dd($response->getContent());
        $response->assertStatus(302); 
        $response->assertRedirect('items');


        // 仮のディスクに保存されたすべてのファイルの一覧を取得
        // $allFiles = Storage::disk('public')->allFiles();
        // echo "仮のディスクに保存されたファイル一覧:\n";
        // print_r($allFiles);

        // dd($image->hashName());
        // データベースに画像ファイルが保存されていることを確認
        // Storage::disk('public')->assertExists('items/'.$image->hashName());

        // $this->assertDatabaseHas('items', array_merge($validData, ['management_id' => 'CO-1111']));
        $this->assertDatabaseHas('items', [
            'management_id' => 'CO-1111',
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            // 'image1' => 'items/'.$image->hashName(),
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
    function 備品新規登録画面で備品を登録できる、消耗品以外の時()
    {
        // ※注意
        // 備品が新規作成された裏でItemObserverによってedithistoriesテーブルにもデータが保存される

        //世界の構築が不十分
        // dump(Category::factory()->create(['id' => 1]));
        $category = Category::factory()->create([
            'id' => 2,
            'name' => 'IT機器'
        ]);
        // $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
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
            // 'management_id' => 'CO-1111',
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
        // $response = $this->from('items/create')->post('items', array_merge($validData, $inspectionData, $disposalData));
        // $response = $this->from('items/create')->post('items', $validData);
        $response = $this->from('items/create')->post(route('items.store'), $validData);

        $response->assertRedirect('items');

        // $this->assertDatabaseHas('items', array_merge($validData, ['management_id' => 'CO-1111']));
        $this->assertDatabaseHas('items', [
            'management_id' => 'CO-1111',
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => null,
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

}
