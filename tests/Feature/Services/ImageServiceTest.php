<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageService;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    // ItemControllerのstore,updateの画像アップロード
    /** @test */
    function resizeUploadのテスト()
    {
        Storage::fake('public');

        $image_file = UploadedFile::fake()->image('test_item_image.jpg');

        $fileNameToStore = ImageService::resizeUpload($image_file);

        dump($fileNameToStore);

        Storage::disk('public')->assertExists('items/'.$fileNameToStore);
    }

    /** @test */
    function profileImageResizeUploadのテスト()
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->image('test_profile_image.jpg');

        $fileNameToStore = ImageService::profileImageResizeUpload($imageFile);

        // dd($fileNameToStore);

        Storage::disk('public')->assertExists('profile/'.$fileNameToStore);
    }
}
