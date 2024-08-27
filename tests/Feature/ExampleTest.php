<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // public $user;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->user = 'hoge';
    // }p

    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // 準備
        // 1件ユーザーを作成
        $user = User::factory()->create();

        dump($user->id);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response2(): void
    {
        // 準備
        $user = User::factory()->create();

        dump($user->id);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
