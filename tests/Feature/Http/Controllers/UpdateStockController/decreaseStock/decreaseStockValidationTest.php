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

class decreaseStockValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    // DecreaseStockRequestのバリデーションのテスト
    // 在庫数以下にはquantityを出来ないバリデーションRulesがStockLimit
    
    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションtransaction_dateがnullで無効値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['transaction_date' => null]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.transaction_date')
            ->where('errors.transaction_date', '入出庫日は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションtransaction_dateが文字列で無効値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['transaction_date' => 'あ']);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.transaction_date')
            ->where('errors.transaction_date', '入出庫日には有効な日付を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションtransaction_dateが数字で無効値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['transaction_date' => 1]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.transaction_date')
            ->where('errors.transaction_date', '入出庫日には有効な日付を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションtransaction_dateが日付で有効値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['transaction_date' => '2024-9-3']);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->missing('errors.transaction_date')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションoperator_nameがnullで無効値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['operator_name' => null]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.operator_name')
            ->where('errors.operator_name', '実施者は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションoperator_nameが1文字で有効値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['operator_name' => str_repeat('あ', 1)]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->missing('errors.operator_name')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションoperator_nameが10文字で有効境界値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['operator_name' => str_repeat('あ', 10)]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->missing('errors.operator_name')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションoperator_nameが11文字で無効境界値な場合()
    {
        // 世界を構築
        $category = Category::factory()->create(['id' => 1]);

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成、消耗品(category_id=1)のみ入出庫できる
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['operator_name' => str_repeat('あ', 11)]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.operator_name')
            ->where('errors.operator_name', '実施者は、10文字以下で指定してください。')
            // ->dump()
        );
    }


    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションquantityがnullで無効値な場合()
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

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['quantity' => null]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.quantity')
            ->where('errors.quantity', '数量は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションquantityが0で無効境界値な場合()
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

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['quantity' => 0]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.quantity')
            ->where('errors.quantity', '数量には、1以上の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションquantityが1で有効境界値な場合()
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

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['quantity' => 1]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->missing('errors.quantity')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションquantity上限が在庫数で有効境界値な場合()
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

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['quantity' => 10]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->missing('errors.quantity')
            // ->dump()
        );
    }

    /** @test */
    function 入出庫モーダルでの出庫処理バリデーションquantity上限が在庫数で無効境界値な場合()
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

        $response = $this->from('consumable_items')
            ->put(route('decreaseStock', $item->id), ['quantity' => 11]);
        $response->assertStatus(302);
        $response->assertRedirect('consumable_items');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ConsumableItems/Index')
            ->has('errors.quantity')
            ->where('errors.quantity', '在庫数以上の数量は出庫できません')
            // ->dump()
        );
    }
}
