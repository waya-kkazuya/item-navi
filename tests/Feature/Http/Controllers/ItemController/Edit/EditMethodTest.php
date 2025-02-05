<?php

namespace Tests\Feature\Http\Controllers\ItemController\Edit;

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

class EditMethodTest extends TestCase
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
    function 備品編集画面を開く()
    {
        // 全ての権限で画面を開く
        $roles = [
            'admin' => 1,
            'staff' => 5,
            'user' => 9,
        ];

        $item = Item::factory()->create();

        foreach ($roles as $roleName => $role) {
            $user = User::factory()->role($role)->create();
            $this->actingAs($user);

            $response = $this->get(route('items.edit', $item));

            if ($role === 9) {
                $response->assertStatus(403);
            } else {
                $response->assertOk();
            }
        }
    }
}
