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
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class UpdateValidationTest extends TestCase
{
    use RefreshDatabase;
    // use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // \Artisan::call('cache:clear');

        $this->faker = FakerFactory::create();

        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function tearDown(): void
    {
        // 子クラスでのクリーンアップ処理
        parent::tearDown();
    }

    // 編集更新のnameのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションnameが空欄で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['name' => '']);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.name')
            ->where('errors.name', '名前は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションnameが1文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['name' => str_repeat('あ', 1)]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.name')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションnameが20文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['name' => str_repeat('あ', 40)]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.name')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションnameが21文字で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['name' => str_repeat('あ', 41)]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.name')
            ->where('errors.name', '名前は、40文字以下で指定してください。')
            // ->dump()
        );
    }


    // 編集更新のcategory_idのバリデーションのテスト    
    /** @test */
    public function 備品編集更新バリデーションcategoryIdが最小値より小さい無効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item),  ['category_id' => $categories->min('id') - 1]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.category_id')
            ->where('errors.category_id', '選択されたカテゴリは正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションcategoryIdが最小の有効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item),  ['category_id' => $categories->min('id')]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.category_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションcategoryIdが最大の有効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item),  ['category_id' => $categories->max('id')]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.category_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションcategoryIdが最大値を超える無効値()
    {
        // 世界を構築
        $categories = Category::factory()->count(11)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // category_idを指定しないと$categories->max('id') + 1である12でアイテムが生成されて上手くいかない
        $item = Item::factory()->create([
            'category_id' => $categories->first()->id,
        ]);
        // dd($item);
        // dd($categories->max('id'));

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item),  ['category_id' => $categories->max('id') + 1]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.category_id')
            ->where('errors.category_id', '選択されたカテゴリは正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションstockが最小値より小さい無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['stock' => -1]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.stock')
            ->where('errors.stock', '在庫数には、0以上の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションstockが最小の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['stock' => 0]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.stock')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションstockが最大の有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['stock' => 200]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.stock')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションstockが最大値を超える無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['stock' => 201]);
        $response->assertRedirect('items/'.$item->id.'/edit'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.stock')
            ->where('errors.stock', '在庫数には、200以下の数字を指定してください。')
            // ->dump()
        );
    }


     // カテゴリが消耗品とそれ以外の時のminimum_stockの保存をテスト
    /** @test */
    public function 備品編集更新バリデーションカテゴリが消耗品の時はminimum_stockの値は保存される()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 世界を構築
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

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'minimum_stock' => null
        ]);

        // 適切に保存されるデータでカテゴリが消耗品(catgery_id=1)の時
        // 更新リクエストを送信
        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
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
            'storage_location_id' => $locations->first()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-03',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
            'qrcode' => null,
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20',
            'edit_reason_id' => $edit_reasons->first()->id,
            'edit_reason_text' => '詳細理由です',
        ]);

        // Show画面にリダイレクトする
        $response->assertRedirect(route('items.show', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $this->assertDatabaseHas('items', [
            'category_id' => 1,
            'minimum_stock' => 2
        ]);
    }

    /** @test */
    public function 備品編集更新バリデーションカテゴリが消耗品以外の時は値がminimum_stockはnullで保存される()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 世界を構築
        // $categories = Category::factory()->count(11)->create();
        $category1 = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        
        $category2 = Category::factory()->create([
            'id' => 2,
            'name' => 'IT機器'
        ]);
        $categories = Category::factory()->count(9)->create(); //残りのカテゴリ
        $categories = $categories->concat([$category1, $category2]); //全てのカテゴリ

        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        $edit_reasons = EditReason::factory()->count(5)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'category_id' => $category1->id,
            'minimum_stock' => 5
        ]);
        // dd($item);

        // 適切に保存されるデータでカテゴリが消耗品(catgery_id=1)の時
        // 更新リクエストを送信
        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
            'name' => 'ペーパータオル',
            'category_id' => $category2->id,
            'image1' => null,
            'stock' => 10,
            'unit_id' => $units->first()->id,
            'minimum_stock' => 2,
            'notification' => true,
            'usage_status_id' => $usage_statuses->first()->id,
            'end_user' => '山田',
            'location_of_use_id' => $locations->first()->id,
            'storage_location_id' => $locations->first()->id,
            'acquisition_method_id' => $aquisition_methods->first()->id,
            'acquisition_source' => 'Amazon',
            'price' => 500,
            'date_of_acquisition' => '2024-09-03',
            'manufacturer' => null,
            'product_number' => null,
            'remarks' => 'テストコードです',
            'qrcode' => null,
            'inspection_scheduled_date' => '2024-09-10',
            'disposal_scheduled_date' => '2024-09-20',
            'edit_reason_id' => $edit_reasons->first()->id,
            'edit_reason_text' => '詳細理由です',
        ]);

        // Show画面にリダイレクトする
        $response->assertRedirect(route('items.show', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $this->assertDatabaseHas('items', [
            'category_id' => $category2->id,
            'minimum_stock' => null
        ]);
    }


    /** @test */
    public function 備品編集更新バリデーションminimum_stockが最小値より小さい無効値()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $category = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $categories = Category::factory()->count(10)->create(); //残りのカテゴリ
        $categories = $categories->concat($category); //全てのカテゴリ

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'category_id' => $category->id,
            'minimum_stock' => 5
        ]);

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
            'category_id' => $category->id,
            'minimum_stock' => -1,
        ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.minimum_stock')
            ->where('errors.minimum_stock', '通知在庫数には、0以上の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションminimum_stockが最小の有効値()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $category = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $categories = Category::factory()->count(10)->create(); //残りのカテゴリ
        $categories = $categories->concat($category); //全てのカテゴリ

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'category_id' => $category->id,
            'minimum_stock' => 5
        ]);

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
            'category_id' => $category->id,
            'minimum_stock' => 0,
        ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.minimum_stock')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションminimum_stockが最大の有効値()
    {
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $category = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $categories = Category::factory()->count(10)->create(); //残りのカテゴリ
        $categories = $categories->concat($category); //全てのカテゴリ

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'category_id' => $category->id,
            'minimum_stock' => 5
        ]);

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
            'category_id' => $category->id,
            'minimum_stock' => 50,
        ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.minimum_stock')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションminimum_stockが最大値を超える無効値()
{
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $category = Category::factory()->create([
            'id' => 1,
            'name' => '消耗品'
        ]);
        $categories = Category::factory()->count(10)->create(); //残りのカテゴリ
        $categories = $categories->concat($category); //全てのカテゴリ

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create([
            'category_id' => $category->id,
            'minimum_stock' => 5
        ]);

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
            'category_id' => $category->id,
            'minimum_stock' => 51,
        ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.minimum_stock')
            ->where('errors.minimum_stock', '通知在庫数には、50以下の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションunit_idが最小値より小さい無効値()
    {   
        // categoriesテーブルをトランケートして連番をリセット
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('units')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 世界を構築
        $units = Unit::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['unit_id' => $units->min('id') - 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.unit_id')
            ->where('errors.unit_id', '選択された単位は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションunit_idが最小の有効値()
    {   
        // 世界を構築
        $units = Unit::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['unit_id' => $units->min('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.unit_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションunit_idが最大の有効値()
    {   
        // 世界を構築
        $units = Unit::factory()->count(10)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['unit_id' => $units->max('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.unit_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションunit_idが最大値を超える無効値()
    {   
         // 外部キー制約を無効にする
        // \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 世界を構築
        $units = Unit::factory()->count(10)->create();
        
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // $itemはunit_idを指定しないと、自動的に$units->max('id') + 1にインクリメントしてしまう
        // $units->first()->id、明確にDBに存在するidを指定しないと、クラス単位のテストでエラーになる
        $item = Item::factory()->create([
            'unit_id' => $units->first()->id
        ]);

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['unit_id' => $units->max('id') + 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.unit_id')
            ->where('errors.unit_id', '選択された単位は正しくありません。')
            // ->dump()
        );

        // 外部キー制約を有効に戻す
        // \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    // usage_status_idのバリデーションのテスト    
    /** @test */
    public function 備品編集更新バリデーションusage_status_idが最小値より小さい無効値()
    {   
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['usage_status_id' => $usage_statuses->min('id') - 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.usage_status_id')
            ->where('errors.usage_status_id', '選択された利用状況は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションusage_status_idが最小の有効値()
    {   
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['usage_status_id' => $usage_statuses->min('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.usage_status_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションusage_status_idが最大の有効値()
    {   
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['usage_status_id' => $usage_statuses->max('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.usage_status_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションusage_status_idが最大値を超える無効値()
    {   
        // 世界を構築
        $usage_statuses = UsageStatus::factory()->count(2)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // usage_status_idが$usage_statuses->max('id') + 1に自動でインクリメントされてしまうので指定する
        // $usage_statuses->first()->id、明確にDBに存在するidを指定しないと、クラス単位のテストでエラーになる
        $item = Item::factory()->create([
            'usage_status_id' => $usage_statuses->first()->id
        ]);

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['usage_status_id' => $usage_statuses->max('id') + 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.usage_status_id')
            ->where('errors.usage_status_id', '選択された利用状況は正しくありません。')
            // ->dump()
        );
    }

    // end_userのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションend_user空欄のまま保存できる()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['end_user' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.end_user')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションend_user10文字で保存できる()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['end_user' => str_repeat('あ', 10)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.end_user')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションend_user11文字で保存できない()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['end_user' => str_repeat('あ', 11)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.end_user')
            ->where('errors.end_user', '使用者は、10文字以下で指定してください。')
            // ->dump()
        );
    }


    // location_of_use_idのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションlocation_of_use_idが最小値より小さい無効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['location_of_use_id' => 0]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.location_of_use_id')
            ->where('errors.location_of_use_id', '選択された利用場所は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションlocation_of_use_idが最小の有効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['location_of_use_id' => Location::min('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.location_of_use_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションlocation_of_use_idが最大の有効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['location_of_use_id' => Location::max('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.location_of_use_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションlocation_of_use_idが最大値を超える無効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['location_of_use_id' => Location::max('id') + 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.location_of_use_id')
            ->where('errors.location_of_use_id', '選択された利用場所は正しくありません。')
            // ->dump()
        );
    }


    // storage_location_idのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションstorage_location_idが最小値より小さい無効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['storage_location_id' => 0]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.storage_location_id')
            ->where('errors.storage_location_id', '選択された保管場所は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションstorage_location_idが最小の有効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['storage_location_id' => Location::min('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.storage_location_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションstorage_location_idが最大の有効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['storage_location_id' => Location::max('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        // レスポンス内容を確認
        // dump($response->getContent());

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.storage_location_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションstorage_location_idが最大値を超える無効値()
    {   
        // 世界を構築
        $locations = Location::factory()->count(12)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['storage_location_id' => Location::max('id') + 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.storage_location_id')
            ->where('errors.storage_location_id', '選択された保管場所は正しくありません。')
            // ->dump()
        );
    }


    // acquisition_method_idのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションacquisition_method_idが最小値より小さい無効値()
    {   
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_method_id' => 0]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.acquisition_method_id')
            ->where('errors.acquisition_method_id', '選択された取得区分は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションacquisition_method_idが最小の有効値()
    {   
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_method_id' => AcquisitionMethod::min('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.acquisition_method_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションacquisition_method_idが最大の有効値()
    {   
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_method_id' => AcquisitionMethod::max('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.acquisition_method_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションacquisition_method_idが最大値を超える無効値()
    {   
        // 世界を構築
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_method_id' => AcquisitionMethod::max('id') + 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.acquisition_method_id')
            ->where('errors.acquisition_method_id', '選択された取得区分は正しくありません。')
            // ->dump()
        );
    }


    // acquisition_sourceのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションacquisition_sourceが空欄の無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_source' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.acquisition_source')
            ->where('errors.acquisition_source', '取得先は、1文字以上で指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションacquisition_sourceが1文字の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_source' => str_repeat('あ', 1)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.acquisition_source')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションacquisition_sourceが20文字の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_source' => str_repeat('あ', 20)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.acquisition_source')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションacquisition_sourceが21文字の無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['acquisition_source' => str_repeat('あ', 21)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.acquisition_source')
            ->where('errors.acquisition_source', '取得先は、20文字以下で指定してください。')
            // ->dump()
        );
    }


    // priceのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションpriceが空欄で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['price' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.price')
            ->where('errors.price', '価格は必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションpriceが文字列で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item),  ['price' => 'あ']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.price')
            ->where('errors.price', '価格は整数で指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションpriceがマイナスの数値の無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item),  ['price' => -1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.price')
            ->where('errors.price', '価格には、0以上の数字を指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションpriceが0で最小の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item),  ['price' => 0]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.price')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションpriceが100万で最大の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['price' => 1000000]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.price')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションpriceが100万1で最大を超える無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['price' => 1000001]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.price')
            ->where('errors.price', '価格には、1000000以下の数字を指定してください。')
            // ->dump()
        );
    }


    // date_of_acquisitionのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションdate_of_acquisitionが空で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['date_of_acquisition' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.date_of_acquisition')
            ->where('errors.date_of_acquisition', '取得年月日は必ず指定してください。')
            // ->dump()
        );
    }
    
    /** @test */
    public function 備品編集更新バリデーションdate_of_acquisitionが今日より未来の日付で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 今日より未来の日付を生成
        $tomorrow = Carbon::now()->addDays(1)->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['date_of_acquisition' => $tomorrow]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.date_of_acquisition')
            ->where('errors.date_of_acquisition', '取得年月日には、今日以前の日付をご利用ください。')
            // ->dump()
        );
    }
    
    /** @test */
    public function 備品編集更新バリデーションdate_of_acquisitionが今日当日で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 今日の日付
        $today = Carbon::now()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['date_of_acquisition' => $today]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.date_of_acquisition')
            // ->dump()
        );
    }
    
    /** @test */
    public function 備品編集更新バリデーションdate_of_acquisitionが今日より過去の日付で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 昨日の日付
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['date_of_acquisition' => $yesterday]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.date_of_acquisition')
            // ->dump()
        );
    }


    // manufacturerのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションmanufacturer空欄の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['manufacturer' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.manufacturer')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションmanufacturer1文字の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['manufacturer' => str_repeat('あ', 1)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.manufacturer')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションmanufacturer20文字で最大の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['manufacturer' => str_repeat('あ', 20)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.manufacturer')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションmanufacturer21文字で最大を超える無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['manufacturer' => str_repeat('あ', 21)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.manufacturer')
            ->where('errors.manufacturer', 'メーカーは、20文字以下で指定してください。')
            // ->dump()
        );
    }


    // product_numberのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションproduct_number空欄で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['product_number' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.product_number')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションproduct_number1文字の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['product_number' => str_repeat('あ', 1)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.product_number')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションproduct_number31文字で最大を超える無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['product_number' => str_repeat('あ', 31)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.product_number')
            ->where('errors.product_number', '製品番号は、30文字以下で指定してください。')
            // ->dump()
        );
    }


    // remarksのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションremarks空欄の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['remarks' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.remarks')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションremarks1文字の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['remarks' => str_repeat('あ', 1)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.remarks')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションremarks501文字で最大を超える無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['remarks' => str_repeat('あ', 501)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.remarks')
            ->where('errors.remarks', '備考は、500文字以下で指定してください。')
            // ->dump()
        );
    }


    // inspection_scheduled_dateのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションinspection_scheduled_dateが空で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['inspection_scheduled_date' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.inspection_scheduled_date')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションinspection_scheduled_dateが今日より過去の日付で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 昨日の日付
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'inspection_scheduled_date' => $yesterday
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.inspection_scheduled_date')
            ->where('errors.inspection_scheduled_date', '点検予定日は取得年月日以降の日付を入力してください')
            // ->dump()
        );
    }
    
    /** @test */
    public function 備品編集更新バリデーションinspection_scheduled_dateが今日当日で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 今日の日付
        $today = Carbon::now()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'inspection_scheduled_date' => $today
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.inspection_scheduled_date')
            // ->dump()
        );
    }
    
    /** @test */
    public function 備品編集更新バリデーションinspection_scheduled_dateがdate_of_acquisitionから3年以内で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年後の日付
        $threeYearsLater = Carbon::now()->addYears(3)->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'inspection_scheduled_date' => $threeYearsLater
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.inspection_scheduled_date')
            // ->dump()
        );
    }
    
    /** @test */
    public function 備品編集更新バリデーションinspection_scheduled_dateがdate_of_acquisitionから3年と1日後で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年と1日後の日付
        $threeYearsAndOneDayLater = Carbon::now()->addYears(3)->addDay()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'inspection_scheduled_date' => $threeYearsAndOneDayLater
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.inspection_scheduled_date')
            ->where('errors.inspection_scheduled_date', '点検予定日は取得年月日から3年以内の日付を入力してください')
            // ->dump()
        );
    }



    // disposal_scheduled_dateのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションdisposal_scheduled_dateが空で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['disposal_scheduled_date' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.disposal_scheduled_date')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションdisposal_scheduled_dateが今日より過去の日付で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 昨日の日付
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'disposal_scheduled_date' => $yesterday
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.disposal_scheduled_date')
            ->where('errors.disposal_scheduled_date', '廃棄予定日は取得年月日以降の日付を入力してください')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションdisposal_scheduled_dateが今日当日で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 今日の日付
        $today = Carbon::now()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'disposal_scheduled_date' => $today
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.disposal_scheduled_date')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションdisposal_scheduled_dateがdate_of_acquisitionから3年以内で有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年後の日付
        $threeYearsLater = Carbon::now()->addYears(3)->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'disposal_scheduled_date' => $threeYearsLater 
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.disposal_scheduled_date')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションdisposal_scheduled_dateがdate_of_acquisitionから3年と1日後で無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 取得年月日を設定
        $date_of_acquisition = Carbon::now()->format('Y-m-d');
        // 3年と1日後の日付
        $threeYearsAndOneDayLater = Carbon::now()->addYears(3)->addDay()->format('Y-m-d');

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), [
                'date_of_acquisition' => $date_of_acquisition,
                'disposal_scheduled_date' => $threeYearsAndOneDayLater 
            ]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.disposal_scheduled_date')
            ->where('errors.disposal_scheduled_date', '廃棄予定日は取得年月日から3年以内の日付を入力してください')
            // ->dump()
        );
    }



    // edit_reason_idのバリデーションのテスト
    /** @test */
    public function 備品編集更新バリデーションedit_reason_idが最小値より小さい無効値()
    {   
        // 世界を構築
        $edit_reasons = EditReason::factory()->count(5)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_id' => 0]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.edit_reason_id')
            ->where('errors.edit_reason_id', '選択された編集理由は正しくありません。')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションedit_reason_idが最小値の有効値()
    {   
        // 世界を構築
        $edit_reasons = EditReason::factory()->count(5)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_id' => $edit_reasons->min('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.edit_reason_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションedit_reason_idが最大値の有効値()
    {   
        // 世界を構築
        $edit_reasons = EditReason::factory()->count(5)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_id' => $edit_reasons->max('id')]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.edit_reason_id')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションedit_reason_idが最大値より大きい無効値()
    {   
        // 世界を構築
        $edit_reasons = EditReason::factory()->count(5)->create();

        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // EditReasonはItemのカラムでないので、自動インクリメントされない
        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_id' => $edit_reasons->max('id') + 1]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.edit_reason_id')
            ->where('errors.edit_reason_id', '選択された編集理由は正しくありません。')
            // ->dump()
        );
    }


    // edit_reason_textのバリデーションテスト
    /** @test */
    public function 備品編集更新バリデーションedit_reason_text空欄の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_text' => '']);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.edit_reason_text')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションedit_reason_text1文字の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_text' => str_repeat('あ', 1)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.edit_reason_text')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションedit_reason_text200文字の有効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_text' => str_repeat('あ', 200)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->missing('errors.edit_reason_text')
            // ->dump()
        );
    }

    /** @test */
    public function 備品編集更新バリデーションedit_reason_text201文字の無効値()
    {   
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->from('items/'.$item->id.'/edit')
            ->put(route('items.update', $item), ['edit_reason_text' => str_repeat('あ', 201)]);

        $response->assertRedirect(route('items.edit', ['item' => $item->id])); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Edit')
            ->has('errors.edit_reason_text')
            ->where('errors.edit_reason_text', '理由詳細は、200文字以下で指定してください。')
            // ->dump()
        );
    }
}
