<?php

namespace Tests\Feature\Http\Controllers\UpdateStockController\increaseStock;

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
use Illuminate\Support\Facades\DB;

class increaseStockMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function 入庫モーダルで入庫処理が出来る()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
            'transaction_type' => '入庫',
            'transaction_date' => '2024-9-3',
            'operator_name' => $user->name,
            'quantity' => 3,
        ];

        // 備品を入庫処理
        $response = $this->from('consumable_items')
            ->put(route('increaseStock', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock' => '13'
        ]);

        $this->assertDatabaseHas('stock_transactions', [
            'item_id' => $item->id,
            'transaction_type' => '入庫',
            'transaction_date' => '2024-9-3',
            'operator_name' => $user->name,
            'quantity' => 3,
        ]);
    }
}
