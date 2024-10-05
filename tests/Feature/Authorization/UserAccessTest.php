<?php

namespace Tests\Feature\Authorization;

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

class UserAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    // Userカラムのroleが9のuserのアクセスをテスト

    // User権限で出来ないことをテスト
    // ページへのアクセス
    // １，アクセスできるページは消耗費管理の入出庫モーダルの出庫タブのみv-ifで設定
    // ２、リクエスト一覧画面、
    // ３，ダッシュボードでは、表示できる情報のみか、消耗品管理画面へリダイレクト
    /** @test */
    function Userが権限のない画面にアクセスできない()
    {
        // roleが9の場合のuser
        $user = User::factory()->role(9)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        // 表示できない場合はリダイレクトではなく、403Forbiddenにしておく
        $this->get('items')->assertStatus(403);
        $this->get('items/create')->assertStatus(403);
        $this->from('items/create')->post('items', [])->assertStatus(403);
        $this->get('items/'.$item->id.'/edit')->assertStatus(403);
        $this->from('items/'.$item->id.'/edit')->patch(route('items.update', $item->id), [])->assertStatus(403);
        $this->from('items/'.$item->id)->delete('items/'.$item->id)->assertStatus(403);
        
        $this->get('inspection-and-disposal-items')->assertStatus(403);
        $this->get('notifications')->assertStatus(403);
        
        // Dashboardはアプリアイコンにリンクが設定されていて、
        // ログイン後にアクセスすると403になってしまうので、消耗品管理画面にリダイレクトする
        $this->get('dashboard')->assertRedirect('consumable_items');

    }

    /** @test */
    function Userが権限のある画面にアクセスできる()
    {
        $loginUrl = 'login';

        // roleが9の場合のuser
        $user = User::factory()->role(9)->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $this->get('consumable_items')->assertOk();
        $this->get('item-requests')->assertOk();
        $this->get('profile')->assertOk();

    }
}
