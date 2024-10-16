<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// use PDF;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF; // snappy pdfを使用する

class PDFController extends Controller
{
    const CATEGORY_ID_FOR_CONSUMABLE_ITME = 1;

    public function generatePDF()
    {
        // 消耗品のQRコードをすべて取得する（現在カテゴリが消耗品のもの、変更されている可能性もある）
        $consumableItems = Item::whereNull('deleted_at')
            ->where('category_id', self::CATEGORY_ID_FOR_CONSUMABLE_ITME)
            ->get();

        // QRラベル画像のパスの配列を取得
        $qrCodes = [];
        foreach($consumableItems as $consumableItem) {
            $qrCodes[] = Storage::path('labels/'.$consumableItem->qrcode);
        }

        \Log::info("qrCodes",$qrCodes);

        if (empty($qrCodes)) {
            return to_route('consumable_items')
            ->with([
                'message' => 'QRラベルが存在しません',
                'status' => 'danger'
            ]);
        }

        // ビューを作成してPDFを生成
        $pdf = PDF::loadView('pdf.pdf-preview-table', compact('qrCodes'))
            ->setPaper('A4')
            ->setOption('margin-left', '16.5mm')
            ->setOption('margin-right', '16.5mm')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-center', '[page] / [topage]') // フッター中央に現在のページ番号と総ページ数を表示
            ->setOption('footer-font-size', 10);

        // return $pdf->download('消耗品QRコード.pdf');　//直にダウンロードする場合
        return $pdf->inline('消耗品QRコード.pdf');
    }

    public function designPDF()
    {
        //デザイン調整用 
        $qrCodes = [];
        for ($i = 1; $i <= 11; $i++) {
            $qrCodes[] = Storage::url('public/labels/QRCodeTest_label.jpg');
        }

        // blade側でchunkする
        return view('pdf.pdf-preview-table', compact('qrCodes'));
    }
}
