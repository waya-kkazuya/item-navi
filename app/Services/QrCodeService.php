<?php

namespace App\Services;

use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Typography\FontFactory;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
  public static function upload($item)
  {
    Log::info('QrCodeService upload method called');

    try {
      // $item->idを元にQRコードを生成する
      $qrCode = QrCode::format('png')->size(300)->generate($item->id);
      $qrCodeNameToStore = 'QR-' . uniqid(rand().'_') . '.png';
      Storage::disk('public')->put('qrcode/'.$qrCodeNameToStore, $qrCode);
      $qrCodefilePath = Storage::disk('public')->path('qrcode/'.$qrCodeNameToStore);

      $qrManager = new ImageManager(new Driver());
      $qrImage = $qrManager->read($qrCodefilePath);

      $label = $qrManager->create(910, 550)->fill('fff'); //白地に画像と文字列を合成
      $label->place($qrImage, 'top-left', 40, 125);

      $label->text('管理ID'.$item->management_id, 370, 160, function(FontFactory $font) {
          $font->filename(public_path('storage/fonts/NotoSansJP-Medium.ttf'));
          $font->size(30);
          $font->color('#000');
      });
      $label->text('備品名 '.$item->name, 370, 230, function(FontFactory $font) {
          $font->filename(public_path('storage/fonts/NotoSansJP-Medium.ttf'));
          $font->size(30);
          $font->color('#000');
      });
      $label->text('カテゴリ'.$item->category->name, 370, 300, function(FontFactory $font) {
          $font->filename(public_path('storage/fonts/NotoSansJP-Medium.ttf'));
          $font->size(30);
          $font->color('#000');
      });

      $labelNameToStore = 'label-'.uniqid(rand().'_') .'.jpg';
      Storage::disk('public')->put('labels/'.$labelNameToStore, $label->encode(new JpegEncoder()));

      Log::info('QrCodeService upload method succeed');

      // トランザクション処理が失敗した時のロールバック用にQRコード画像名も返す
      return [
        'labelNameToStore' => $labelNameToStore,
        'qrCodeNameToStore' => $qrCodeNameToStore,
      ];
    } catch (\Exception $e) {
      Log::error('QrCodeService upload method failed', [
        'error' => $e->getMessage(),
        'stack' => $e->getTraceAsString(),
      ]);

      throw $e; // エラーを再スローしてコントローラでキャッチできるように
    }
  }
}
