<?php

namespace Tests\Feature\Http\Controllers;

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



class ItemControllerTest extends TestCase
{
    use RefreshDatabase;
    // use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();

        // // フェイクの画像ファイルを作成
        // $this->fakeImage = UploadedFile::fake()->image('test_image.jpg');

        // // ImageServiceのモックを作成
        // $this->imageService = Mockery::mock(ImageService::class);
        // $this->imageService->shouldReceive('resizeUpload')
        //     ->once()
        //     ->with(Mockery::on(function ($arg) {
        //         return $arg instanceof UploadedFile && $arg->getClientOriginalName() === 'test_image.jpg';
        //     }))
        //     ->andReturn('mocked_image.jpg');

        // // サービスコンテナにモックを登録
        // $this->app->instance(ImageService::class, $this->imageService);

    }


    // 編集理由部分にはツールチップで「更新の際は編集理由を入力してください」と表示する
    // 分かりづらいので



    // NotificationControllerのテスト
    // 在庫数が通知在庫数以下になったとき

    // 点検と廃棄の予定日が近づいたとき

    // リクエストされたとき

}
