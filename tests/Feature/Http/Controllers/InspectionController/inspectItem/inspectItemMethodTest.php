<?php

namespace Tests\Feature\Http\Controllers\InspectionController\inspectItem;

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

class inspectItemMethodTest extends TestCase
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
        $response = $this->from('items/'.$item->id)
            ->put(route('inspect_item.inspectItem', $item->id), $validData);
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
        $inspection = Inspection::withoutEvents(function () use ($item) {
            return Inspection::factory()->create([
                'item_id' => $item->id,
                'inspection_scheduled_date' => '2024-09-01',
                'inspection_date' => null,
                'status' => false,
                'inspection_person' => null,
                'details' => null
            ]);
        });
        // 備品をソフトデリート
        $response = $this->from('items/'.$item->id)
            ->put(route('inspect_item.inspectItem', $item->id), $validData);
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
}
