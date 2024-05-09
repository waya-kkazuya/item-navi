<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImageTest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;

class ImageTestController extends Controller
{
    public function index()
    {
        // phpinfo();

        $imageTests = ImageTest::select(
            'name',
            'file_name',
        )->get();

        return Inertia::render('ImageTests/Index', [
            'imageTests' => $imageTests,
            // 'sort' => $sortDirection
        ]);

    }

    public function create()
    {
        return Inertia::render('ImageTests/Create');
    }

    public function store(UploadImageRequest $request)
    {
        Gate::authorize('staff-higher');

        // dd($request);

        $imageFile = $request->file_name;
        if(!is_null($imageFile) && $imageFile->isValid()){
            ImageService::upload($imageFile, 'images');
        }


        return to_route('image_tests.index')
        ->with([
            'message' => '登録しました。',
            'status' => 'success'
        ]);
        
    }
}
