<?php

namespace Tests\Feature\Http\Controllers\DisposalController\disposeItem;

use App\Models\Item;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class diposeItemMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function 備品詳細画面で廃棄モーダルで廃棄処理できる()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        // テスト用の備品を作成
        $item = Item::factory()->create();

        $validData = [
            'disposal_date'   => '2024-09-03',
            'disposal_person' => $user->name,
            'details'         => 'あいうえお',
        ];

        // 備品を廃棄処理
        $response = $this->from('items/' . $item->id)
            ->put(route('dispose_item.disposeItem', $item->id), $validData);
        $response->assertStatus(302); // リダイレクトを確認
        $response->assertRedirect('items');

        $this->assertDatabaseHas('disposals', [
            'disposal_date'   => '2024-09-03',
            'disposal_person' => $user->name,
            'details'         => 'あいうえお',
        ]);

        // ソフトデリートされたことを確認
        $this->assertSoftDeleted('items', ['id' => $item->id]);

        // ソフトデリートされた備品が取得できないことを確認
        $this->assertNull(Item::find($item->id));

        // ソフトデリートされた備品がwithTrashedで取得できることを確認
        $this->assertNotNull(Item::withTrashed()->find($item->id));

        // 廃棄した後ItemObserverによってeidithistorieテーブルに廃棄履歴が保存される
        $this->assertDatabaseHas('edithistories', [
            'edit_mode'      => 'normal',
            'operation_type' => 'soft_delete',
            'item_id'        => $item->id,
            'edited_field'   => null,
            'old_value'      => null,
            'new_value'      => null,
            'edit_user'      => Auth::user()->name,
        ]);
    }
}
