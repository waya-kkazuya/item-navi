<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageMagickTest extends Controller
{
        public function createImage()
    {
        $image = new \Imagick();
        $image->newImage(100, 100, new \ImagickPixel('blue'));
        $image->writeImage(public_path('test_image.png'));

        return response()->json(['message' => 'Image created successfully']);
    }
}
