<?php

namespace Tests\Feature\Http\Controllers\ItemController\Create;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateMethodTest extends TestCase
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
    public function 備品新規登録画面を開く()
    {
        // 全ての権限で画面を開く
        $roles = [
            'admin' => 1,
            'staff' => 5,
            'user'  => 9,
        ];

        foreach ($roles as $roleName => $role) {
            $user = User::factory()->role($role)->create();
            $this->actingAs($user);

            $response = $this->get('items/create');

            if ($role === 9) {
                $response->assertStatus(403);
            } else {
                $response->assertOk();
            }
        }
    }
}
