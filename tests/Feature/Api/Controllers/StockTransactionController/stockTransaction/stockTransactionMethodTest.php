<?php

namespace Tests\Feature\Api\Controllers\StockTransactionController\stockTransaction;

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

class stockTransactionMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function 入出庫履歴モーダル用のデータをAPIで取得できる()
    {
        // 世界の構築
        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);   

        $item = Item::factory()->create();
        // $item->idの編集履歴をDBに保存
        $stockTransactions = StockTransaction::factory()->count(20)->create(['item_id' => $item->id]);

        // Inertiaリクエストのシミュレーション、ヘッダーが追加される
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => Inertia::getVersion(),
        ])->get('/api/stock_transactions?item_id=' . $item->id);
        
        $response->assertOk();

        // dd($response->json());

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('stockTransactions', 10)
                ->has('stockTransactions', fn ($json) => 
                    $json->each(fn ($json) => 
                        $json->where('item_id', $item->id)
                            ->where('transaction_type', fn($type) => in_array($type, ['入庫', '出庫']))
                            ->where('quantity', fn($quantity) => is_int($quantity))
                            ->where('operator_name', fn($name) => is_string($name))
                            ->where('transaction_date', fn($date) => strtotime($date) !== false)
                            ->etc()
                )
            )
        );
    }

}
