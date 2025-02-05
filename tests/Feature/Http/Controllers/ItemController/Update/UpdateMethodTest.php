<?php

namespace Tests\Feature\Http\Controllers\ItemController\Update;

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
use App\Models\Disposal;
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
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Console\DumpCommand;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Inertia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;
use Illuminate\Support\Facades\Session;



class UpdateMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create(); 
    }

    protected function tearDown(): void
    {
        // 子クラスでのクリーンアップ処理
        parent::tearDown();
    }

    /** @test */
    function 備品編集画面で備品を編集更新できる_点検予定日のレコードが存在かつ廃棄予定日のレコードが存在_画像アップロードはモック()
    {
        $this->withoutExceptionHandling(); // 詳細なエラーメッセージを表示

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

        // 世界の構築
        // 変更前後のカテゴリを準備
        $category_after = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $category_before = Category::factory()->create([
            'id' => 2,
            'name' => 'IT機器'
        ]);
        $categories = Category::factory()->count(9)->create(); //残りのカテゴリ
        $categories = $categories->concat($category_before)->concat($category_after); //全てのカテゴリ
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        $edit_reasons = EditReason::factory()->count(5)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 変更前の備品を作成
        // ここでItemObserverのcreatedでedithistoryに$operation_type=storeで保存される
        $item = Item::factory()->create([
            'name' => '備品名変更前',
            'category_id' => $category_before->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units[0]->id,
            'minimum_stock' => 2,
            'notification' => 1, //trueだとパスしない
            'usage_status_id' => $usage_statuses[0]->id,
            'end_user' => '鈴木',
            'location_of_use_id' => $locations[0]->id,
            'storage_location_id' => $locations[0]->id,
            'acquisition_method_id' => $aquisition_methods[0]->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-01',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => '備考更新前',
        ]);
        
        // 点検予定日のレコードが存在する場合
        // ここでInspectionObserverのcreatedメソッドで
        // $operation_type=storeゆえにedithistory(既存のものを編集した場合なので)には保存されない
        session(['operation_type' => 'store']);
        $inspection = Inspection::factory()->create([
            'item_id' => $item->id,
            'inspection_scheduled_date' => '2024-09-05',
            'status' => false
        ]);
        session()->forget('operation_type'); // セッションから'operation_type'を削除

        // 廃棄予定日のレコードが存在する場合
        // ここでDisposalObserverのcreatedメソッドで
        // $operation_type=storeゆえにedithistory(既存のものを編集した場合なので)には保存されない
        session(['operation_type' => 'store']);
        $disposal = Disposal::factory()->create([
            'item_id' => $item->id,
            'disposal_scheduled_date' => '2024-09-15',
        ]);
        session()->forget('operation_type'); // セッションから'operation_type'を削除

        $validData = [
            'name' => '備品名更新後',
            'category_id' => $category_after->id,
            'image_file' => $this->fakeImage,
            'stock' => 11,
            'unit_id' => $units[1]->id,
            'minimum_stock' => 3,
            'notification' => 0,
            'usage_status_id' => $usage_statuses[1]->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations[1]->id,
            'storage_location_id' => $locations[1]->id,
            'acquisition_method_id' => $aquisition_methods[1]->id,
            'acquisition_source' => '楽天',
            'price' => 1000,
            'date_of_acquisition' => '2024-09-02',
            'manufacturer' => 'メーカー更新後',
            'product_number' => '製品番号更新後',
            'remarks' => '備考更新後',
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20',
            'edit_reason_id' => $edit_reasons->first()->id,
            'edit_reason_text' => 'あいうえおかきくけこ',
        ];

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), $validData);
        $response->assertRedirect(route('items.show', ['item' => $item]));
        $response->assertStatus(302);

        $this->assertDatabaseHas('items', [
            'name' => '備品名更新後',
            'category_id' => $category_after->id,
            'image1' => 'mocked_image.jpg',
            'stock' => 11,
            'unit_id' => $units[1]->id,
            'minimum_stock' => 3,
            'notification' => 0,
            'usage_status_id' => $usage_statuses[1]->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations[1]->id,
            'storage_location_id' => $locations[1]->id,
            'acquisition_method_id' => $aquisition_methods[1]->id,
            'acquisition_source' => '楽天',
            'price' => 1000,
            'date_of_acquisition' => '2024-09-02',
            'manufacturer' => 'メーカー更新後',
            'product_number' => '製品番号更新後',
            'remarks' => '備考更新後',
        ]);

        // 更新されたことのチェック 同じレコードかどうか
        $item->refresh();
        $this->assertDatabaseCount('items', 1);
        $this->assertSame('備品名更新後', $item->name);
        $this->assertSame($category_after->id, $item->category_id);
        $this->assertSame('mocked_image.jpg', $item->image1);
        $this->assertSame(11, $item->stock);
        $this->assertSame($units[1]->id, $item->unit_id);
        $this->assertSame(3, $item->minimum_stock);
        $this->assertSame(false, (bool) $item->notification);
        $this->assertSame($usage_statuses[1]->id, $item->usage_status_id);
        $this->assertSame('山田', $item->end_user);
        $this->assertSame($locations[1]->id, $item->location_of_use_id);
        $this->assertSame($locations[1]->id, $item->storage_location_id);
        $this->assertSame($aquisition_methods[1]->id, $item->acquisition_method_id);
        $this->assertSame('楽天', $item->acquisition_source);
        $this->assertSame(1000, $item->price);
        $this->assertSame('2024-09-02', $item->date_of_acquisition);
        $this->assertSame('メーカー更新後', $item->manufacturer);
        $this->assertSame('製品番号更新後', $item->product_number);
        $this->assertSame('備考更新後', $item->remarks);
            
        // inspectionsテーブルに保存されているか確認
        $inspection->refresh();
        //2件、更新ではなく、新規にレコードが追加されている
        $this->assertDatabaseCount('inspections', 1);
        $this->assertDatabaseHas('inspections', [
            'item_id' => $item->id,
            'inspection_scheduled_date' =>  '2024-09-10',
            'status' => 0,
        ]);

        // disposalsテーブルに保存されているか確認
        $disposal->refresh();
        $this->assertDatabaseCount('disposals', 1);
        $this->assertDatabaseHas('disposals', [
            'item_id' => $item->id,
            'disposal_scheduled_date' => '2024-09-20',
        ]);

        dump(Edithistory::all()->toArray());
        
        // edithistoriesには計21件のレコードが作成された
        // 最初の準備で1件のItemControllerのstore->備品作成時1レコード
        // （点検レコード更新で1レコード、廃棄レコード更新で1レコードの場合はedithistoriesには保存されない）
        // ItemControllerのupdateで20レコードレコード、備品の方が18項目更新、Inspectionが1件更新とDisposalが1件更新
        $this->assertDatabaseCount('edithistories', 21);

        $fields = [
            'name' => ['old_value' => '備品名変更前', 'new_value' => '備品名更新後'],
            'category_id' => ['old_value' => $category_before->id, 'new_value' => $category_after->id],
            // ItemObserver側で$fiedlsがimage1の時はedithistoryに保存しないようにした
            // 理由：アプリの編集履歴に画像名が出るのも良くないから
            // 'image1' => ['old_value' => null, 'new_value' => 'mocked_image.jpg'],
            'stock' => ['old_value' => 10, 'new_value' => 11],
            'unit_id' => ['old_value' => $units[0]->id, 'new_value' => $units[1]->id],
            'minimum_stock' => ['old_value' => 2, 'new_value' => 3],
            'notification' => ['old_value' => 1, 'new_value' => 0],
            'usage_status_id' => ['old_value' => $usage_statuses[0]->id, 'new_value' => $usage_statuses[1]->id],
            'end_user' => ['old_value' => '鈴木', 'new_value' => '山田'],
            'location_of_use_id' => ['old_value' => $locations[0]->id, 'new_value' => $locations[1]->id],
            'storage_location_id' => ['old_value' => $locations[0]->id, 'new_value' => $locations[1]->id],
            'acquisition_method_id' => ['old_value' => $aquisition_methods[0]->id, 'new_value' => $aquisition_methods[1]->id],
            'acquisition_source' => ['old_value' => 'Amazon', 'new_value' => '楽天'],
            'price' => ['old_value' => 500, 'new_value' => 1000],
            'date_of_acquisition' => ['old_value' => '2024-09-01', 'new_value' => '2024-09-02'],
            'manufacturer' => ['old_value' => null, 'new_value' => 'メーカー更新後'],
            'product_number' => ['old_value' => null, 'new_value' => '製品番号更新後'],
            'remarks' => ['old_value' => '備考更新前', 'new_value' => '備考更新後'],
            'inspection_scheduled_date' => ['old_value' => '2024-09-05', 'new_value' => '2024-09-10'],
            'disposal_scheduled_date' => ['old_value' => '2024-09-15', 'new_value' => '2024-09-20']
        ];
        
        foreach ($fields as $field => $values) {
            $this->assertDatabaseHas('edithistories', [
                'edit_mode' => 'normal',
                'operation_type' => 'update',
                'item_id' => $item->id,
                'edited_field' => $field,
                'old_value' => $values['old_value'],
                'new_value' => $values['new_value'],
                'edit_user' => $user->name ?? '',
                'edit_reason_id' => $edit_reasons->first()->id,
                'edit_reason_text' => 'あいうえおかきくけこ',
            ]);
        }
    }

    /** @test */
    function 備品編集画面で備品を編集更新できる_点検予定日のレコードが無いかつ廃棄予定日のレコードが無い_画像アップロードはモック()
    {   
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

        // 世界の構築
        $category_after = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $category_before = Category::factory()->create([
            'id' => 2,
            'name' => 'IT機器'
        ]);
        $categories = Category::factory()->count(9)->create(); //残りのカテゴリ
        $categories = $categories->concat($category_before)->concat($category_after); //全てのカテゴリ
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        $edit_reasons = EditReason::factory()->count(5)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // 変更前の備品を作成
        // ここでItemObserverのcreatedでedithistoryに$operation_type=storeで保存される
        $item = Item::factory()->create([
            'name' => '備品名変更前',
            'category_id' => $category_before->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units[0]->id,
            'minimum_stock' => 2,
            'notification' => 1, //trueだとパスしない
            'usage_status_id' => $usage_statuses[0]->id,
            'end_user' => '鈴木',
            'location_of_use_id' => $locations[0]->id,
            'storage_location_id' => $locations[0]->id,
            'acquisition_method_id' => $aquisition_methods[0]->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-01',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => '備考更新前',
        ]);

        $validData = [
            'name' => '備品名更新後',
            'category_id' => $category_after->id,
            'image_file' => $this->fakeImage,
            'stock' => 11,
            'unit_id' => $units[1]->id,
            'minimum_stock' => 3,
            'notification' => 0,
            'usage_status_id' => $usage_statuses[1]->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations[1]->id,
            'storage_location_id' => $locations[1]->id,
            'acquisition_method_id' => $aquisition_methods[1]->id,
            'acquisition_source' => '楽天',
            'price' => 1000,
            'date_of_acquisition' => '2024-09-02',
            'manufacturer' => 'メーカー更新後',
            'product_number' => '製品番号更新後',
            'remarks' => '備考更新後',
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20',
            'edit_reason_id' => $edit_reasons->first()->id,
            'edit_reason_text' => 'あいうえおかきくけこ',
        ];

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), $validData);
        $response->assertRedirect(route('items.show', ['item' => $item]));
        $response->assertStatus(302);

        $this->assertDatabaseHas('items', [
            'name' => '備品名更新後',
            'category_id' => $category_after->id,
            'image1' => 'mocked_image.jpg',
            'stock' => 11,
            'unit_id' => $units[1]->id,
            'minimum_stock' => 3,
            'notification' => 0,
            'usage_status_id' => $usage_statuses[1]->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations[1]->id,
            'storage_location_id' => $locations[1]->id,
            'acquisition_method_id' => $aquisition_methods[1]->id,
            'acquisition_source' => '楽天',
            'price' => 1000,
            'date_of_acquisition' => '2024-09-02',
            'manufacturer' => 'メーカー更新後',
            'product_number' => '製品番号更新後',
            'remarks' => '備考更新後',
        ]);

        // 更新されたことのチェック 同じレコードかどうか
        $item->refresh();
        $this->assertDatabaseCount('items', 1);
        $this->assertSame('備品名更新後', $item->name);
        $this->assertSame($category_after->id, $item->category_id);
        $this->assertSame('mocked_image.jpg', $item->image1); // image1自体はモックの画像名に正しく変更されている
        $this->assertSame(11, $item->stock);
        $this->assertSame($units[1]->id, $item->unit_id);
        $this->assertSame(3, $item->minimum_stock);
        $this->assertSame(false, (bool) $item->notification);
        $this->assertSame($usage_statuses[1]->id, $item->usage_status_id);
        $this->assertSame('山田', $item->end_user);
        $this->assertSame($locations[1]->id, $item->location_of_use_id);
        $this->assertSame($locations[1]->id, $item->storage_location_id);
        $this->assertSame($aquisition_methods[1]->id, $item->acquisition_method_id);
        $this->assertSame('楽天', $item->acquisition_source);
        $this->assertSame(1000, $item->price);
        $this->assertSame('2024-09-02', $item->date_of_acquisition);
        $this->assertSame('メーカー更新後', $item->manufacturer);
        $this->assertSame('製品番号更新後', $item->product_number);
        $this->assertSame('備考更新後', $item->remarks);
        
        
        // inspectionsテーブルに保存されているか確認
        $this->assertDatabaseCount('inspections', 1);
        $this->assertDatabaseHas('inspections', [
            'item_id' => $item->id,
            'inspection_scheduled_date' =>  '2024-09-10',
            'status' => 0,
        ]);

        // disposalsテーブルに保存されているか確認
        $this->assertDatabaseCount('disposals', 1);
        $this->assertDatabaseHas('disposals', [
            'item_id' => $item->id,
            'disposal_scheduled_date' => '2024-09-20',
        ]);

        dump(Edithistory::all()->toArray());

        // 最初の準備で1件、備品作成で1レコード
        // 残りは更新した20件のデータの分
        $this->assertDatabaseCount('edithistories', 21);

        $fields = [
            'name' => ['old_value' => '備品名変更前', 'new_value' => '備品名更新後'],
            'category_id' => ['old_value' => $category_before->id, 'new_value' => $category_after->id],
            // ItemObserver側で$fiedlsがimage1の時はedithistoryに保存しないようにした
            // 理由：アプリの編集履歴に画像名が出るのも良くないから
            // 'image1' => ['old_value' => null, 'new_value' => 'mocked_image.jpg'], 
            'stock' => ['old_value' => 10, 'new_value' => 11],
            'unit_id' => ['old_value' => $units[0]->id, 'new_value' => $units[1]->id],
            'minimum_stock' => ['old_value' => 2, 'new_value' => 3],
            'notification' => ['old_value' => 1, 'new_value' => 0],
            'usage_status_id' => ['old_value' => $usage_statuses[0]->id, 'new_value' => $usage_statuses[1]->id],
            'end_user' => ['old_value' => '鈴木', 'new_value' => '山田'],
            'location_of_use_id' => ['old_value' => $locations[0]->id, 'new_value' => $locations[1]->id],
            'storage_location_id' => ['old_value' => $locations[0]->id, 'new_value' => $locations[1]->id],
            'acquisition_method_id' => ['old_value' => $aquisition_methods[0]->id, 'new_value' => $aquisition_methods[1]->id],
            'acquisition_source' => ['old_value' => 'Amazon', 'new_value' => '楽天'],
            'price' => ['old_value' => 500, 'new_value' => 1000],
            'date_of_acquisition' => ['old_value' => '2024-09-01', 'new_value' => '2024-09-02'],
            'manufacturer' => ['old_value' => null, 'new_value' => 'メーカー更新後'],
            'product_number' => ['old_value' => null, 'new_value' => '製品番号更新後'],
            'remarks' => ['old_value' => '備考更新前', 'new_value' => '備考更新後'],
            'inspection_scheduled_date' => ['old_value' => null, 'new_value' => '2024-09-10'],
            'disposal_scheduled_date' => ['old_value' => null, 'new_value' => '2024-09-20']
        ];
        
        foreach ($fields as $field => $values) {
            $this->assertDatabaseHas('edithistories', [
                'edit_mode' => 'normal',
                'operation_type' => 'update',
                'item_id' => $item->id,
                'edited_field' => $field,
                'old_value' => $values['old_value'],
                'new_value' => $values['new_value'],
                'edit_user' => $user->name ?? '',
                'edit_reason_id' => $edit_reasons->first()->id,
                'edit_reason_text' => 'あいうえおかきくけこ',
            ]);
        }
    }
}
