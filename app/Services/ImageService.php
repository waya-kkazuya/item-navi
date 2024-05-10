<?php

namespace App\Services;

use Illuminate\Validation\Rules\ImageFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
  public static function upload($imageFile, $folderName){
    
    // InventionImageで保存する場合
    // dd($imageFile);
    if(is_array($imageFile))
    {
      $file = $imageFile['file_name'];
      // dd($file);
    } else {
      $file = $imageFile;
    }

    $fileName = uniqid(rand().'_');
    $extension = $file->extension();
    $fileNameToStore = $fileName. '.' . $extension;

    // 希望するドライバーで新しいマネージャー インスタンスを作成する
    $manager = new ImageManager(new Driver());

    // 画像ファイルを読み込む
    $image = $manager->read($file);

    // 画像をリサイズする
    $image->resize(width: 1920, height: 1080);

    // リサイズした画像を保存する(Storageいらず)
    $image->save(public_path('/'. $folderName . '/' . $fileNameToStore));

    // ver2.0の書き方
    // $resizedImage = Image::make($imageFile)->resize(1920, 1080)->encode();

    // Storageは不要になった
    // putはパスを作る必要あり
    // Storage::put('public/images/' . $fileNameToStore, $resizedImage );
    // putFileは自動でファイル名を生成
    // Storage::putFile('public/items', $imageFile);

    return $fileNameToStore;
  }
}