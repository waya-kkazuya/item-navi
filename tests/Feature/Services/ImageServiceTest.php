<?php

namespace Tests\Feature\Services;

use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    // ItemControllerのstore,updateの画像アップロード
    /** @test */
    public function resizeUploadのテスト()
    {
        Storage::fake('public');

        $image_file = UploadedFile::fake()->image('test_item_image.jpg');

        $fileNameToStore = ImageService::resizeUpload($image_file);

        dump($fileNameToStore);

        Storage::disk()->assertExists('items/' . $fileNameToStore);
    }

    /** @test */
    public function profileImageResizeUploadのテスト()
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->image('test_profile_image.jpg');

        $fileNameToStore = ImageService::profileImageResizeUpload($imageFile);

        // dd($fileNameToStore);

        Storage::disk()->assertExists('profile/' . $fileNameToStore);
    }

    /** @test */
    public function InterventionImageテスト()
    {
        // テスト用の画像ファイルを準備
        Storage::fake('public');
        $image = UploadedFile::fake()->image('test_image.jpg');

        try {
            // 画像を処理するコード
            $manager = new ImageManager(new Driver());
            $image   = $manager->read($image->getPathname());
        } catch (\Intervention\Image\Exception\NotReadableException $e) {
            Log::error('Image not readable: ' . $e->getMessage());
            $this->fail('Image processing failed: ' . $e->getMessage());
        }

        $this->assertTrue(true);
    }
}
