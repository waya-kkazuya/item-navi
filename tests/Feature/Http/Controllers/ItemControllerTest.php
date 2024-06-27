<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class ItemControllerTest extends TestCase
{
    /** @test */
    function 備品一覧_paginateオブジェクトを渡す()
    {
        // テストデータを生成、テストの世界を構築
        $items = Item::factory(20)->create();

        // 権限レベルが必要な値以上のユーザーを作成
        $user = User::factory()->create([
            'role' => '1',
        ]);

        $this->actingAs($user)->get('items')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Items/Index')
            ->has('items.data', 20) // items.dataの数が20であることを確認
            ->has('items.data', fn ($data) => $data->each(fn ($item) => $item->hasAll([
                'id',
                'name',
                'category_id',
                'image_path1',
                'image_path2',
                'image_path3',
                'stocks',
                'minimum_stock',
                'usage_status',
                'end_user',
                'location_of_use_id',
                'storage_location_id',
                'acquisition_category',
                'where_to_buy',
                'price',
                'date_of_acquisition',
                'inspection_schedule',
                'disposal_schedule',
                'manufacturer',
                'product_number',
                'remarks',
                'qrcode_path',
                'created_at'
            ])
            ->has('category', fn ($category) => $category->hasAll(['id', 'name', 'created_at', 'updated_at']))  // categoryオブジェクトがid属性を持っていることを確認
            ->has('location_of_use', fn ($location) => $location->hasAll(['id', 'name', 'created_at', 'updated_at']))  // location_of_useオブジェクトがid属性を持っていることを確認
            ->has('storage_location', fn ($location) => $location->hasAll(['id', 'name', 'created_at', 'updated_at']))  // storage_locationオブジェクトがid属性を持っていることを確認
        )));         
    }
}
