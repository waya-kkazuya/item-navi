<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    public static function resizeUpload($imageFile)
    {
        Log::info('ImageService resizeUpload method called');

        try {
            $fileName        = uniqid(rand() . '_');
            $extension       = $imageFile->extension();
            $fileNameToStore = $fileName . '.' . $extension;

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

            $resizedImage = $image->encode();

            Storage::disk()->put('items/' . $fileNameToStore, $resizedImage);

            Log::info('ImageService resizeUpload method succeed');

            return $fileNameToStore;
        } catch (\Exception $e) {
            Log::error('ImageService resizeUpload method failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            throw $e; // エラーを再スローしてコントローラでキャッチできるように
        }
    }

    public static function profileImageResizeUpload($imageFile)
    {
        $fileName        = uniqid(rand() . '_');
        $extension       = $imageFile->extension();
        $fileNameToStore = $fileName . '.' . $extension;

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

        Storage::disk()->put('profile/' . $fileNameToStore, $resizedImage);

        return $fileNameToStore;
    }

    public function setImagePathToObject($item)
    {
        $defaultDisk = Storage::disk();

        if (is_null($item->image1)) {
            $item->image_path1 = $defaultDisk->url('items/No_Image.jpg');
        } else {
            if ($defaultDisk->exists('items/' . $item->image1)) {
                $item->image_path1 = $defaultDisk->url('items/' . $item->image1);
            } else {
                $item->image_path1 = $defaultDisk->url('items/No_Image.jpg');
            }
        }
        return $item;
    }

    public function setImagePathInCollection($collection)
    {
        return $collection->transform(function ($record) {
            $defaultDisk = Storage::disk();

            if (is_null($record->item->image1)) {
                $record->item->image_path1 = $defaultDisk->url('items/No_Image.jpg');
            } else {
                if ($defaultDisk->exists('items/' . $record->item->image1)) {
                    $record->item->image_path1 = $defaultDisk->url('items/' . $record->item->image1);
                } else {
                    $record->item->image_path1 = $defaultDisk->url('items/No_Image.jpg');
                }
            }
            return $record;
        });
    }
}
