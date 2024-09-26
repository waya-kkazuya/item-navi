<?php

namespace App\Services;

use Illuminate\Validation\Rules\ImageFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;

class ImageService
{
  public static function resizeUpload($imageFile){
    
    // Storage::putFile('public/items', $imageFile); //リサイズ無しの場合、名前も付けてくれる
    $fileName = uniqid(rand().'_'); // ランダムな名前
    $extension = $imageFile->extension();
    $fileNameToStore = $fileName. '.' . $extension;

    // create image manager with desired driver
    $manager = new ImageManager(new Driver());

    $image = $manager->read($imageFile->getPathname());

    // dd($image);

    // 画像のアスペクト比を取得
    $aspectRatio = $image->width() / $image->height();

    // 800:600の比率より縦長ならheightを600にscaleし、横長ならwidthを800にscale
    if ($aspectRatio >= (800 / 600)) {
        $image->scale(width: 800);
    } else {
        $image->scale(height: 600);
    }

    // 画像をリサイズ、resizeDonwは元の大きさは超えない
    // $image->resize(800, 600);

    // 画像をトリミング、縦に長い画像には左右に白地の余白が入る
    $image->crop(800, 600, 0, 0, 'ffffff', 'center');
    // ->crop()の代わりに->resize()を使用する
    // $image->resizeCanvas(800, 600, 'center', false, [0, 0, 0, 0]);
    // $image->pad(800, 600, [0, 0, 0, 0]);

    $resizedImage = $image->encode();

    Storage::put('public/items/' . $fileNameToStore, $resizedImage);

    return $fileNameToStore;
  }

  public static function profileImageResizeUpload($imageFile){
    
    // Storage::putFile('public/items', $imageFile); //リサイズ無しの場合、名前も付けてくれる
    $fileName = uniqid(rand().'_'); // ランダムな名前
    $extension = $imageFile->extension();
    $fileNameToStore = $fileName. '.' . $extension;

    // create image manager with desired driver
    $manager = new ImageManager(new Driver());

    $image = $manager->read($imageFile->getPathname());

    // dd($image);

    // 画像のアスペクト比を取得
    $aspectRatio = $image->width() / $image->height();

    // 800:600の比率より縦長ならheightを600にscaleし、横長ならwidthを800にscale
    if ($aspectRatio >= 1) {
      $image->scaleDown(width: 800);
    } else {
      $image->scaleDown(height: 800);
    }

    // 画像をトリミング、縦に長い画像には左右に白地の余白が入る
    $image->crop(800, 600, 0, 0, 'ffffff', 'center');

    $resizedImage = $image->encode();

    Storage::put('public/profile/' . $fileNameToStore, $resizedImage);

    return $fileNameToStore;
  }



  public function setImagePath($collection)
  {
    return $collection->transform(function ($inspection) {
      if (is_null($inspection->item->image1)) {
        $inspection->item->image_path1 = asset('storage/items/No_Image.jpg');
      } else {
          if (Storage::disk('public')->exists('items/' . $inspection->item->image1)) {
              $inspection->item->image_path1 = asset('storage/items/' . $inspection->item->image1);
          } else {
              $inspection->item->image_path1 = asset('storage/items/No_Image.jpg');
          }
      }
      return $inspection;
    });
  }
}