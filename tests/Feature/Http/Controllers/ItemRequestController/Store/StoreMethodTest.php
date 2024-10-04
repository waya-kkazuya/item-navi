<?php

namespace Tests\Feature\Http\Controllers\ItemRequestController\Store;

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


class StoreMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function リクエストの登録が出来る()
    {
        //世界の構築
        $categories = Category::factory()->count(11)->create();
        $locations = Location::factory()->count(12)->create();
        $requet_statuses = RequestStatus::factory(4)->create();
        

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);   

        // ※注意
        // フロントから送られてくるデータを適切に模倣しないといけいない
        $validData = [
            'name' => 'ボールペン',
            'category_id' => $categories->first()->id,
            'location_of_use_id' => $locations->first()->id,
            'requestor' => '山田',
            'remarks_from_requestor' => '申請理由です',
            'request_status_id' => $requet_statuses->first()->id,
            'manufacturer' => 'ボールペン工房',
            'reference' => '参考サイト',
            'price' => 100,
        ];
    
        $response = $this->from('item_requests/create')
            ->post(route('item_requests.store'), $validData);

        $response->assertRedirect('item-requests');

        $this->assertDatabaseHas('item_requests', [
            'name' => 'ボールペン',
            'category_id' => $categories->first()->id,
            'location_of_use_id' => $locations->first()->id,
            'requestor' => '山田',
            'remarks_from_requestor' => '申請理由です',
            'request_status_id' => $requet_statuses->first()->id,
            'manufacturer' => 'ボールペン工房',
            'reference' => '参考サイト',
            'price' => 100,
        ]);
    }


}
