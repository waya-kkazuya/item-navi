<?php

namespace Tests\Feature\Http\Controllers\ItemController\Show;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use App\Models\User;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Location;
use App\Models\UsageStatus;
use App\Models\AcquisitionMethod;
use App\Models\Inspection;
use App\Models\Edithistory;
use App\Models\EditReason;
use App\Models\RequestStatus;
use Inertia\Testing\AssertableInertia as Assert;

class ShowMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }
    
    /** @test */
    function 備品の詳細画面を開く()
    {
        $categories = Category::factory()->count(11)->create();
        $units = Unit::factory()->count(10)->create();
        $usage_statuses = UsageStatus::factory()->count(2)->create();
        $locations = Location::factory()->count(12)->create();
        $aquisition_methods = AcquisitionMethod::factory()->count(6)->create();
        
        $item = Item::factory()->create([
            'management_id' => $this->faker->regexify('[A-Za-z0-9]{7}'),
            // 'name' => 'テストアイテム', // nameを上書きできる
            'category_id' => $categories->random()->id,
            'unit_id' => $units->random()->id,
            'usage_status_id' => $usage_statuses->random()->id,
            'location_of_use_id' => $locations->random()->id,
            'storage_location_id' => $locations->random()->id,
            'acquisition_method_id' => $aquisition_methods->random()->id
        ]);


        $uncompleted_inspection = Inspection::withoutEvents(function () use ($item) {
            return Inspection::factory()->create([
                'item_id' => $item->id,
                'status' => false, // 点検予定
            ]);
        });
        
        $last_completed_inspection = Inspection::withoutEvents(function () use ($item) {
            return Inspection::factory()->create([
                'item_id' => $item->id,
                'status' => true, // 点検実行済み
            ]);
        });
        
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        //  items/{item}部分にはidを有力
        $response = $this->get('/items/' . $item->id)
            ->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Items/Show') // Vueコンポーネント
            ->has('item', fn (Assert $page) => $page
                ->where('id', $item->id)
                ->etc()
            )
            ->where('uncompleted_inspection.inspection_date', $uncompleted_inspection->inspection_date->format('Y-m-d')) //日付は文字列の形式に変換、ダミーデータとの整合性
            ->where('uncompleted_inspection.inspection_scheduled_date', $uncompleted_inspection->inspection_scheduled_date->format('Y-m-d'))
            ->where('last_completed_inspection.inspection_date', $last_completed_inspection->inspection_date->format('Y-m-d'))
            ->where('last_completed_inspection.inspection_scheduled_date', $last_completed_inspection->inspection_scheduled_date->format('Y-m-d'))
            ->where('userName', $user->name)
        );
    }
}
