<?php

namespace Tests\Feature\Http\Controllers\ItemRequestController\Store;

use App\Models\Category;
use App\Models\Location;
use App\Models\RequestStatus;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoreMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('request_statuses')->truncate();
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function リクエストの登録が出来る()
    {
        // テーブルのデータとIDをリセット
        //世界の構築
        $categories      = Category::factory()->count(11)->create();
        $locations       = Location::factory()->count(12)->create();
        $requet_statuses = RequestStatus::factory(4)->create();

        // adminユーザーを作成
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // ※注意
        // フロントから送られてくるデータを適切に模倣しないといけいない
        $validData = [
            'name'                   => 'ボールペン',
            'category_id'            => $categories->first()->id,
            'location_of_use_id'     => $locations->first()->id,
            'requestor'              => '山田',
            'remarks_from_requestor' => '申請理由です',
            'request_status_id'      => $requet_statuses->first()->id,
            'manufacturer'           => 'ボールペン工房',
            'reference'              => '参考サイト',
            'price'                  => 100,
        ];

        $response = $this->from('item_requests/create')
            ->post(route('item_requests.store'), $validData);

        // フラッシュメッセージの確認
        $response->assertSessionHas('message', 'リクエストしました。');
        $response->assertSessionHas('status', 'success');

        $response->assertRedirect(route('item_requests.index'));

        $this->assertDatabaseHas('item_requests', [
            'name'                   => 'ボールペン',
            'category_id'            => $categories->first()->id,
            'location_of_use_id'     => $locations->first()->id,
            'requestor'              => '山田',
            'remarks_from_requestor' => '申請理由です',
            'request_status_id'      => $requet_statuses->first()->id,
            'manufacturer'           => 'ボールペン工房',
            'reference'              => '参考サイト',
            'price'                  => 100,
        ]);
    }
}
