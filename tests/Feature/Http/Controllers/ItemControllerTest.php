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
    function 備品一覧が表示される()
    {
        // ダミーデータを生成、テストの世界を構築
        $items = Item::factory(10)->create();

        // 権限レベルが必要な値以上のユーザーを作成
        $user = User::factory()->create([
            'role' => '1',
        ]);

        $this->actingAs($user)->get('items')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Items/Index')
            ->has('items.data', function($items) {
                // ペジネーションのオブジェクトをアサートする
                $this->assertArrayHasKey('current_page', $items);
                $this->assertArrayHasKey('last_page', $items);
                $this->assertArrayHasKey('per_page', $items);
                $this->assertArrayHasKey('total', $items);
                $this->assertArrayHasKey('data', $items);
        
                // ペジネーションのオブジェクト内のデータをアサ―トする
                foreach ($items['data'] as $item) {
                    $this->assertArrayHasKey('name', $item);
                    $this->assertArrayHasKey('category_id', $item);
                    $this->assertArrayHasKey('image_path1', $item);
                    $this->assertArrayHasKey('image_path2', $item);
                    $this->assertArrayHasKey('image_path3', $item);
                    $this->assertArrayHasKey('stocks', $item);
                    $this->assertArrayHasKey('minimum_stock', $item);
                    $this->assertArrayHasKey('usage_status', $item);
                    $this->assertArrayHasKey('end_user', $item);
                    $this->assertArrayHasKey('location_of_use_id', $item);
                    $this->assertArrayHasKey('storage_location_id', $item);
                    $this->assertArrayHasKey('acquisition_category', $item);
                    $this->assertArrayHasKey('where_to_buy', $item);
                    $this->assertArrayHasKey('price', $item);
                    $this->assertArrayHasKey('manufacturer', $item);
                    $this->assertArrayHasKey('product_number', $item);
                    $this->assertArrayHasKey('date_of_acquisition', $item);
                    $this->assertArrayHasKey('inspection_schedule', $item);
                    $this->assertArrayHasKey('disposal_schedule', $item);
                    $this->assertArrayHasKey('remarks', $item);
                    $this->assertArrayHasKey('qrcode_path', $item);
                }
        
                return true;
            })
            ->has('categories')
            ->has('locations')
            ->has('search')
            ->has('sortOrder')
            ->has('category_id')
            ->has('location_of_use_id')
            ->has('storage_location_id'));

            
    }
}
