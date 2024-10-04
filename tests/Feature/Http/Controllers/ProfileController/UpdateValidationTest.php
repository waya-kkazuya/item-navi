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
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
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

        $response->assertInertia(fn (Assert $page) => $page
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

        $response->assertInertia(fn (Assert $page) => $page
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

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Edit')
            ->has('errors.name')
            ->where('errors.name', '名前は、20文字以下で指定してください。')
            // ->dump()
        );
    }
}
