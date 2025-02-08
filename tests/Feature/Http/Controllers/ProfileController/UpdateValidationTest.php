<?php

namespace Tests\Feature\Http\Controllers\ProfileController;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UpdateValidationTest extends TestCase
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

    // nameのバリデーションのテスト
    /** @test */
    public function プロフィール編集更新バリデーションnameが空欄で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('profile')
            ->patch(route('profile.update'), ['name' => '']);
        $response->assertRedirect('profile'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Profile/Edit')
                ->has('errors.name')
                ->where('errors.name', '名前は必ず指定してください。')
                // ->dump()
        );
    }

    /** @test */
    public function プロフィール編集更新バリデーションnameが20文字で有効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('profile')
            ->patch(route('profile.update'), ['name' => str_repeat('あ', 20)]);
        $response->assertRedirect('profile'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Profile/Edit')
                ->missing('errors.name')
                // ->dump()
        );
    }

    /** @test */
    public function プロフィール編集更新バリデーションnameが21文字で無効値()
    {
        $user = User::factory()->role(1)->create();
        $this->actingAs($user);

        $response = $this->from('profile')
            ->patch(route('profile.update'), ['name' => str_repeat('あ', 21)]);
        $response->assertRedirect('profile'); //URLにリダイレクト
        $response->assertStatus(302);

        $response = $this->followRedirects($response);

        $response->assertInertia(fn(Assert $page) => $page
                ->component('Profile/Edit')
                ->has('errors.name')
                ->where('errors.name', '名前は、20文字以下で指定してください。')
                // ->dump()
        );
    }
}
