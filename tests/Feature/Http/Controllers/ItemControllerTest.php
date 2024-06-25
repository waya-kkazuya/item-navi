<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ItemControllerTest extends TestCase
{
    /** @test */
    function 備品一覧が表示される()
    {
        // 権限レベルが必要な値以上のユーザーを作成
        $user = User::factory()->create([
            'role' => '1',
        ]);

        $this->actingAs($user)->get('items')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Items/Index')
            ->has('items'));


            
    }
}
