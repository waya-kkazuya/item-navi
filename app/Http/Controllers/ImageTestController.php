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

        $imageTests->map(function ($image_test) {
            $image_test->file_name = asset('/images/' . $image_test->file_name);
            return $image_test;
        });

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
        dd($request);
        
        Gate::authorize('staff-higher');

        // dd($request);

        $imageFile = $request->file_name;
        if(!is_null($imageFile) && $imageFile->isValid()){
            $fileNameToStore = ImageService::upload($imageFile, 'images');
        }

        ImageTest::create([
            'name' => $request->name,
            'file_name' => $fileNameToStore
        ]);

        // if(!is_null($imageFile) && $imageFile->isValid()){

        // }

        return to_route('image_tests.index')
        ->with([
            'message' => '画像を保存しました！！',
            'status' => 'success'
        ]);
        
    }

    public function edit($id)
    {

    }

    public function update($id)
    {

    }
}
