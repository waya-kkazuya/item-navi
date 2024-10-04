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

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /** @test */
    function ゲストはログイン後の画面にアクセスできない()
    {   
        $loginUrl = 'login';
        // $user = User::factory()->role(1)->create();
        // $this->actingAs($user);

        $item = Item::factory()->create();

        // ゲスト用のリダイレクトのアサ―ト
        $this->get('dashboard')->assertRedirect($loginUrl);
        $this->get('items')->assertRedirect($loginUrl);
        $this->get('items/create')->assertRedirect($loginUrl);
        $this->from('items/create')->post('items', [])->assertRedirect($loginUrl);
        $this->get('items/edit')->assertRedirect($loginUrl);
        $this->from('items/edit')->patch(route('items.update', $item->id), [])->assertRedirect($loginUrl);
            // ->assertForbidden();
        $this->from('items/show')->delete('items/' . $item->id)->assertRedirect($loginUrl);
        
        $this->get('consumable_items')->assertRedirect($loginUrl);
        $this->get('inspection-and-disposal-items')->assertRedirect($loginUrl);
        $this->get('item-requests')->assertRedirect($loginUrl);
        $this->get('notifications')->assertRedirect($loginUrl);
        $this->get('profile')->assertRedirect($loginUrl);

    }

}
