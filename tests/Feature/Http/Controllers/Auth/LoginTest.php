<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\User;

use function Laravel\Prompts\error;
use function Laravel\Prompts\password;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create(); 
    }

    protected function tearDown(): void
    {
        // 子クラスでのクリーンアップ処理
        parent::tearDown();
    }

    /** @test */
    public function ログイン画面が表示出来る()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function 間違ったEメールとパスワードではログインできない()
    {
        $response = $this->from('/login')
            ->post('/login', [
                'email' => 'invalid@example.com',
                'password' => 'invalidpassword',
            ]);

        // dd(session()->all());
        // dd($response->json());

        $response->assertSessionHasErrors(['email']);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $response = $this->followRedirects($response);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->has('errors.email')
            ->where('errors.email', 'ログイン情報が存在しません。')
            // ->dump()
        );
    }

     /** @test */
     public function Eメールとパスワードが空ではログインできない()
     {
         $response = $this->from('/login')
            ->post('/login', [
                'email' => '',
                'password' => '',
            ]);
 
        $response->assertSessionHasErrors(['email']);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $response = $this->followRedirects($response);

        // 実際はVue側でリアルタイムにバリデーションが行われるので
        // 開発ツールなどで入力しない限りは表示されない
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->has('errors.email')
            ->where('errors.email', 'メールアドレスは必ず指定してください。')
            ->has('errors.password')
            ->where('errors.password', 'パスワードは必ず指定してください。')
            // ->dump()
        );
    }

    /** @test */
    public function 正しいEメールとパスワードでログイン出来る()
    {
        $user = User::factory()->role(1)->create([
            'password' => bcrypt($password = 'password123'),
        ]);

        $response = $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
            ]);

        $response->assertStatus(302);
        // ログイン後の遷移先はRouteServiceProviderの定数HOMEで設定する
        $response->assertRedirect('/items');
        $this->assertAuthenticatedAs($user);
    }
}
