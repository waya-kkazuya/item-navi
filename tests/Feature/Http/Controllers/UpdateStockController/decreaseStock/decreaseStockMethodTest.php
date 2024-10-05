<?php

namespace Tests\Feature\Http\Controllers\UpdateStockController\decreaseStock;

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

class decreaseStockMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    // 在庫数以下にはquantityを出来ないバリデーションRulesがStockLimit
  
    /** @test */
    function 出庫モーダルで出庫処理が出来る()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'stock' => 10,
            'minimum_stock' => 2
        ]);

        $validData = [
            'item_id' => $item->id,
            'transaction_type' => '出庫',
            'transaction_date' => '2024-9-3',
            'operator_name' => $user->name,
            'quantity' => 3,
        ];

        // 備品を出庫処理
        $response = $this->put(route('decreaseStock', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock' => '7'
        ]);

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $item->id,
            'transaction_type' => '出庫',
            'transaction_date' => '2024-9-3',
            'operator_name' => $user->name,
            'quantity' => 3,
        ]);
    }
}
