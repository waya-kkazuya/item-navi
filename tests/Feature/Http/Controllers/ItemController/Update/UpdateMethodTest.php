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



class UpdateMethodTest extends TestCase
{
    use RefreshDatabase;
    // use DatabaseTransactions;

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

    // Observerが邪魔をしている？？
    // 課題1, テスト上でInspectionsが更新されない 
    // 課題2, InspcetionObserverを使用すると、Edithistoryのoperation_typeがあべこべになるのを修正
    // 解決策①セッションを使用する、解決策②ItemControllerにEdithistoryの保存を直接書き、ユースケースの整理
    // 課題3, ファイルアップロードのImageServiceがモック出来ていない、Mockの返す画像名で保存されていない
    // 課題4, Disposalの方も確認する

    /** @test */
    function 備品編集画面で備品を編集更新できる()
    {
        // 世界の構築
        // $categories = Category::factory()->count(11)->create();
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
        $edit_reasons = EditReason::factory()->count(5)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);


        // $item = Item::factory()->create();
        $item = Item::factory()->create([
            'date_of_acquisition' => '2024-09-01',
        ]);
        // dd($item);

        // itemの段階でinspectionsテーブルにレコードがあるか設定する＝世界構築
        // Inspection::factory()->create()をやらない限りデータは保存されていない
        // 1, inspectionsにレコードがない場合->レコードを生成せずそのまま
        // 何もコードはいらない

        // 2, inspectionsにレコードがある場合->inspectionsのレコードを生成する
        $inspection = Inspection::factory()->create([
            'item_id' => $item->id,
            'inspection_scheduled_date' => '2024-09-05',
            'status' => false
        ]);
        
        // dd($inspection);
        
        // fakeの画像を準備
        // Storage::fake('public');
        // $fakeImage = UploadedFile::fake()->image('test_image.jpg');
        
        // dd($fakeImage);

        // ImageServiceのモックを作成
        // $mock = Mockery::mock(ImageService::class);
        // $mock->shouldReceive('resizeUpload')
        //     ->once()
        //     ->with(Mockery::on(function ($arg) {
        //         \Log::info($arg);
        //         return $arg instanceof UploadedFile && $arg->getClientOriginalName() === 'test_image.jpg';
        //     }))
        //     ->andReturn('mocked_image.jpg');

        // $this->instance(ImageService::class, $mock);

        // モックの戻り値を確認
        // dd($this->imageService->resizeUpload($this->fakeImage));
        // $this->assertEquals('mocked_image.jpg', $mock->resizeUpload($fakeImage));

        $validData = [
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            'image_file' => null,
            'image1' => $this->fakeImage,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => 1, //trueだとテストでパスしない
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->last()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-01',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20',
            'edit_reason_id' => $edit_reasons->first()->id,
            'edit_reason_text' => 'あいうえおかきくけこ',
        ];


        // 更新リクエストを送信
        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), $validData);
        $response->assertRedirect('items/'.$item->id); // 詳細画面にリダイレクトする
        $response->assertStatus(302);

        // セッションデータが正しく設定されているか確認
        // dd(Session::get('operation_type'));
        $this->assertEquals('update', Session::get('operation_type'));

        // モックが呼ばれているか確認
        // $mock->shouldHaveReceived('resizeUpload')->once();

        $this->assertDatabaseHas('items', [
            'name' => 'ペーパータオル',
            'category_id' => $category->id,
            // 'image1' => 'mocked_image.jpg',
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
            'date_of_acquisition' => '2024-09-01',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
        ]);

        // 更新されたことのチェック
        $item->refresh();
        $this->assertSame('ペーパータオル', $item->name);
        $this->assertSame($category->id, $item->category_id);
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

        // $inspection->refresh();
        // inspectionsテーブルに保存されているか確認
        $this->assertDatabaseHas('inspections', [
            'item_id' => $item->id,
            'inspection_scheduled_date' =>  '2024-09-10',
            'status' => 0, //false
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
            'edit_reason_text' => 'あいうえおかきくけこ', //その他テキストエリア
        ]);

        // 作成したデータを19項目の更新がある
        $this->assertDatabaseCount('edithistories', 19);
    }
}
