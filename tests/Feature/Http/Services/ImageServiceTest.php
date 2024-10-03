<?php

namespace Tests\Feature\Http\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageService;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function ImageServiceのテスト()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('test_image.jpg');

        $fileNameToStore = ImageService::resizeUpload($image);

        dump($fileNameToStore);

        Storage::disk('public')->assertExists('items/' . $fileNameToStore);
    }
}
