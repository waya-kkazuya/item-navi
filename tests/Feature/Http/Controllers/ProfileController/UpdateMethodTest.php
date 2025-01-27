<?php

namespace Tests\Feature\Http\Controllers\ProfileController;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UpdateMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    /** @test */
    public function プロフィール画面が表示される(): void
    {
        // adminユーザー
        $user = User::factory()->role(1)->create();

        $response = $this->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    /** @test */
    public function プロフィール情報を更新出来る(): void
    {
        // フェイクの画像ファイルを作成
        $this->fakeImage = UploadedFile::fake()->image('test_profile_image.jpg');

        // ImageServiceのモックを作成
        $this->imageService = Mockery::mock(ImageService::class);
        $this->imageService->shouldReceive('profileImageResizeUpload')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg instanceof UploadedFile && $arg->getClientOriginalName() === 'test_profile_image.jpg';
            }))
            ->andReturn('mocked_profile_image.jpg');
        // サービスコンテナにモックを登録
        $this->app->instance(ImageService::class, $this->imageService);

        $user = User::factory()->role(1)->create();

        $response = $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'profile_image_file' => $this->fakeImage
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'profile_image' => 'mocked_profile_image.jpg'
        ]);

        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('mocked_profile_image.jpg', $user->profile_image);
    }

    // /** @test */
    // function ProfileControllerでトランザクション処理が失敗する場合()
    // {

    //     // トランザクションの開始をモック
    //     // DB::shouldReceive('beginTransaction')->once();
    //     // DB::shouldReceive('commit')
    //     //     ->once()
    //     //     ->andThrow(\Exception::class, 'トランザクションのコミットに失敗しました');
    //     $partialMock = DB::partialMock();
    //     // $partialMock->shouldReceive('commit')
    //     //     ->andThrow(new \Exception('トランザクションのコミットに失敗しました'));
    //     $partialMock->shouldReceive('commit')->never();
    //     // DB::shouldReceive('rollback')->once();
    //     // $this->withoutExceptionHandling();

    //     // フェイクの画像ファイルを作成
    //     $this->fakeImage = UploadedFile::fake()->image('test_profile_image.jpg');

    //     // ImageServiceのモックを作成
    //     $this->imageService = Mockery::mock(ImageService::class);
    //     $this->imageService->shouldReceive('profileImageResizeUpload')
    //         ->once()
    //         ->with(Mockery::on(function ($arg) {
    //             return $arg instanceof UploadedFile && $arg->getClientOriginalName() === 'test_profile_image.jpg';
    //         }))
    //         ->andReturn('mocked_profile_image.jpg');
    //     // サービスコンテナにモックを登録
    //     $this->app->instance(ImageService::class, $this->imageService);

    //     $user = User::factory()->role(1)->create([
    //         'name' => 'BeforeTestUser',
    //         'profile_image' => 'before_profile_image.jpg'
    //     ]);

    //     Storage::fake('public');
    //     // 元々の画像ファイルをストレージに追加
    //     Storage::disk()->put('profile/before_profile_image.jpg', 'dummy content');

    //     try {
    //         $response = $this->actingAs($user)
    //             ->patch('/profile', [
    //                 'name' => 'TestUser',
    //                 'profile_image_file' => $this->fakeImage
    //             ]);

            
    //         // Mockeryのスパイを使ってメソッドが呼ばれたことを検証
    //         // Mockery::spy(User::class)->shouldHaveReceived('save')->once();   
    //         // dd($response);

    //         $response->assertStatus(500);

    //     // commitメソッドが呼ばれたことをスパイで検証
    //     // DB::shouldHaveReceived('commit')->once();

    //             // $response->assertSessionHasNoErrors()
    //             //     ->assertRedirect('/profile');


    //          // テストがここまで来るべきではないので失敗させる
    //         // $this->fail('Expected Exception not thrown.');
    //     } catch (\Exception $e) {
    //         // dd($e->getMessage());
    //         // 例外が発生したことを確認
    //         // $this->assertEquals('トランザクションの強制失敗', $e->getMessage());
        
    //     }

    //     // フラッシュメッセージを確認
    //     // $response->assertSessionHas('message', 'プロフィールの更新中にエラーが発生しました');
    //     // $response->assertSessionHas('status', 'danger');


    //     // dd(session('errors'));
    //     // レスポンスの内容をダンプして確認
    //     // dd($response->getContent());

    //     // 500エラーが返ることを確認
    //     // $response->assertStatus(500);

    //     // プロフィール画像の削除が行われたかを確認
    //     Storage::disk()->assertMissing('profile/mocked_profile_image.jpg');
    //     Storage::disk()->assertExists('profile/before_profile_image.jpg');
        
    //     $this->assertDatabaseHas('users', [
    //         'name' => 'BeforeTestUser',
    //         'profile_image' => 'before_profile_image.jpg'
    //     ]);

    //     // $user->refresh();
    //     // $this->assertSame('Test User', $user->name);
    //     // $this->assertSame('mocked_profile_image.jpg', $user->profile_image);

    //     Mockery::close();
    // }

    // /** @test */
    // function ProfileControllerでロールバックした時の処理のテスト()
    // {
    //     // Logファサードをモック
    //     $partialMock = Log::partialMock();
    //     $partialMock->shouldReceive('info')
    //         ->once()
    //         ->andThrow(new \Exception('Logging error for testing purposes'));

    //     // フェイクの画像ファイルを作成
    //     $this->fakeImage = UploadedFile::fake()->image('test_profile_image.jpg');

    //     // ImageServiceのモックを作成
    //     $this->imageService = Mockery::mock(ImageService::class);
    //     $this->imageService->shouldReceive('profileImageResizeUpload')
    //         ->once()
    //         ->with(Mockery::on(function ($arg) {
    //             return $arg instanceof UploadedFile && $arg->getClientOriginalName() === 'test_profile_image.jpg';
    //         }))
    //         ->andReturn('mocked_profile_image.jpg');
    //     // サービスコンテナにモックを登録
    //     $this->app->instance(ImageService::class, $this->imageService);

    //     $user = User::factory()->role(1)->create([
    //         'name' => 'BeforeTestUser',
    //         'profile_image' => null
    //     ]);

    //     $response = $this->actingAs($user)
    //         ->patch('/profile', [
    //             'name' => 'TestUser',
    //             'profile_image_file' => $this->fakeImage
    //         ]);

    //     $response->assertStatus(500);

    //     // フラッシュメッセージを確認
    //     $response->assertSessionHas('message', 'プロフィールの更新中にエラーが発生しました');
    //     $response->assertSessionHas('status', 'danger');


    //     // dd(session('errors'));
    //     // レスポンスの内容をダンプして確認
    //     // dd($response->getContent());

    //     $this->assertDatabaseHas('users', [
    //         'name' => 'BeforeTestUser',
    //         'profile_image' => 'before_profile_image.jpg'
    //     ]);

    //     // $user->refresh();
    //     // $this->assertSame('Test User', $user->name);
    //     // $this->assertSame('mocked_profile_image.jpg', $user->profile_image);
    // }





    // デフォルトで用意されていたテスト
    // public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    // {
    //     $user = User::factory()->create();

    //     $response = $this
    //         ->actingAs($user)
    //         ->patch('/profile', [
    //             'name' => 'Test User',
    //             'email' => $user->email,
    //         ]);

    //     $response
    //         ->assertSessionHasNoErrors()
    //         ->assertRedirect('/profile');

    //     $this->assertNotNull($user->refresh()->email_verified_at);
    // }

    // public function test_user_can_delete_their_account(): void
    // {
    //     $user = User::factory()->create();

    //     $response = $this
    //         ->actingAs($user)
    //         ->delete('/profile', [
    //             'password' => 'password',
    //         ]);

    //     $response
    //         ->assertSessionHasNoErrors()
    //         ->assertRedirect('/');

    //     $this->assertGuest();
    //     $this->assertNull($user->fresh());
    // }

    // public function test_correct_password_must_be_provided_to_delete_account(): void
    // {
    //     $user = User::factory()->create();

    //     $response = $this
    //         ->actingAs($user)
    //         ->from('/profile')
    //         ->delete('/profile', [
    //             'password' => 'wrong-password',
    //         ]);

    //     $response
    //         ->assertSessionHasErrors('password')
    //         ->assertRedirect('/profile');

    //     $this->assertNotNull($user->fresh());
    // }
}
