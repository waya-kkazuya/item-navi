<?php

namespace App\Services;

use Illuminate\Validation\Rules\ImageFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;

class ImageService
{
  public static function resizeUpload($imageFile)
  {  
    $fileName = uniqid(rand().'_');
    $extension = $imageFile->extension();
    $fileNameToStore = $fileName. '.' . $extension;

    $manager = new ImageManager(new Driver());

    $image = $manager->read($imageFile->getPathname());

    // 画像のアスペクト比を取得
    $aspectRatio = $image->width() / $image->height();

    // 800:600の比率より縦長ならheightを600にscaleし、横長ならwidthを800にscale
    if ($aspectRatio >= (800 / 600)) {
        $image->scale(width: 800);
    } else {
        $image->scale(height: 600);
    }

    // 画像をトリミング、縦に長い画像には左右に白地の余白が入る
    $image->crop(800, 600, 0, 0, 'ffffff', 'center');
    // $image->resizeCanvas(800, 600, 'center', false, [0, 0, 0, 0]);

    $resizedImage = $image->encode();

    Storage::disk('public')->put('items/' . $fileNameToStore, $resizedImage);

    return $fileNameToStore;
  }

  public static function profileImageResizeUpload($imageFile)
  {
    $fileName = uniqid(rand().'_');
    $extension = $imageFile->extension();
    $fileNameToStore = $fileName. '.' . $extension;

    $manager = new ImageManager(new Driver());

    $image = $manager->read($imageFile->getPathname());

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

    Storage::disk('public')->put('profile/' . $fileNameToStore, $resizedImage);

    return $fileNameToStore;
  }


  public function setImagePath($collection)
  {
    return $collection->transform(function ($record) {
      if (is_null($record->item->image1)) {
        $record->item->image_path1 = asset('storage/items/No_Image.jpg');
      } else {
          if (Storage::disk('public')->exists('items/' . $record->item->image1)) {
              $record->item->image_path1 = asset('storage/items/' . $record->item->image1);
          } else {
              $record->item->image_path1 = asset('storage/items/No_Image.jpg');
          }
      }
      return $record;
    });
  }
}