<?php

namespace App\Services;

use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Typography\FontFactory;

class QrCodeService
{
  public static function upload($item) {
    

    // QRコード画像の名前をUUIDか、管理IDに依存するかで引数が変わる
    // png生成にはImagickが必要
    
    // $item->id
    // QRコードに備品情報のidを込める
    $item_id = 1;
    // $item->idでQRコードを生成する
    $qrCode = QrCode::format('png')->size(300)->generate($item->id);
    // QRコードの名前はQR-とランダムな文字列
    $qrCodeName = 'QR-' . uniqid(rand().'_') . 'png';
    Storage::put('public/qrcode/' . $qrCodeName, $qrCode);
    
    // 保存したファイルのパスを取得
    $qrCodefilePath = Storage::path('public/qrcode/' . $qrCodeName);

    $qrManager = new ImageManager(new Driver());
    $qrImage = $qrManager->read($qrCodefilePath);
    // $qrImage = $qrManager->read($qrCode, 'raw')->resize(30, 30);

    $label = $qrManager->create(910, 550)->fill('fff');
    $label->place($qrImage, 'top-left', 80, 125);

    // 白地に文字を追加
    $label->text('管理ID '.$item->management_id, 450, 160, function(FontFactory $font) {
        $font->filename(resource_path('fonts/NotoSansJP-Medium.ttf'));
        $font->size(30);
        $font->color('#000');
    });

    $label->text('備品名 '.$item->name, 450, 230, function(FontFactory $font) {
        $font->filename(resource_path('fonts/NotoSansJP-Medium.ttf'));
        $font->size(30);
        $font->color('#000');
    });
    // リレーションは使えるか
    $label->text('カテゴリ '.$item->category->name, 450, 300, function(FontFactory $font) {
        $font->filename(resource_path('fonts/NotoSansJP-Medium.ttf'));
        $font->size(30);
        $font->color('#000');
    });

    $labelNameToStore = 'label-' . uniqid(rand().'_') .'.jpg';
    // $labelName = $item->id . '_label.jpg';
    // JpegEncoderでエンコードする、use分も書く
    // 画像をStorage/public/qrcodesに保存する
    Storage::put('labels/' . $labelNameToStore, $label->encode(new JpegEncoder()));
    
    // ※注意
    // 保存したあと、items->update()で部分的にqrcodeの名前を変更する
    return $labelNameToStore;
  }
}
