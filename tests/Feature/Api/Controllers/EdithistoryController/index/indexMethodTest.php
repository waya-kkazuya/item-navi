<?php

namespace Tests\Feature\Api\Controllers\EdithistoryController\index;

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

class indexMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    // edithistoriesの編集履歴モーダル用のデータを取得できる
    /** @test */
    function 編集履歴モーダル用のデータをAPIで取得できる()
    {
        // 世界の構築
        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);   

        $item = Item::factory()->create();
        // $item->idの編集履歴をDBに保存
        $edithistories = Edithistory::factory()->count(20)->create(['item_id' => $item->id]);

        // Inertiaリクエストのシミュレーション、Inertiaでヘッダーに付与される情報
        // $response = $this->withHeaders([
        //     'X-Inertia' => 'true',
        //     'X-Inertia-Version' => Inertia::getVersion(),
        // ])->get('/api/edithistory?item_id=' . $item->id);
        $response = $this->get('/api/edithistory?item_id=' . $item->id);
        // $response = $this->get('/api/edithistory', ['item_id' => $item->id]);
        $response->assertOk();
        // dd($response->json());

        // dd($response);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('edithistories',10)
                ->has('edithistories', fn ($json) =>
                    $json->each(fn ($json) =>
                        $json->where('item_id', $item->id)
                            ->where('edit_mode', 'normal')
                            ->where('operation_type', function($operation_type) {
                                \Log::info('Operation Type: ' . $operation_type); // デバッグ出力
                                return in_array($operation_type, ['store', 'update', 'stock_in', 'stock_out', 'soft_delete', 'restore']);
                            })
                            ->has('edited_field')
                            ->has('old_value')
                            ->has('new_value')
                            ->where('edit_user', fn($edit_user) => !is_null($edit_user) && is_string($edit_user))
                            ->has('edit_reason_id')
                            ->has('edit_reason_text')
                            ->etc()
                )
            )
        );
    }

}
