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
            // Storage::putの場合
            $fileName = uniqid(rand().'_');
            $extension = $imageFile->extension();
            $fileNameToStore = $fileName. '.' . $extension;

            // 希望するドライバーで新しいマネージャー インスタンスを作成する
            $manager = new ImageManager(new Driver());

            // 画像ファイルを読み込む
            $image = $manager->read($imageFile);

            // 画像をリサイズする
            $image->resize(width: 1920, height: 1080);

            // リサイズした画像を保存する(Storageいらず)
            $image->save(public_path('/images/' . $fileNameToStore));

            // ver2.0
            // $resizedImage = Image::make($imageFile)->resize(1920, 1080)->encode();

            // Storage::put('public/images/' . $fileNameToStore, $resizedImage );
            // dd($imageFile, $image);

            // putFileは自動でファイル名を生成
            // リサイズなしの場合
            // Storage::putFile('public/items', $imageFile);
        }


        return to_route('image_tests.index')
        ->with([
            'message' => '登録しました。',
            'status' => 'success'
        ]);
        
    }
}
