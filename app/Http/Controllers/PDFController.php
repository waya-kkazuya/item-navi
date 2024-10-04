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
    public function generatePDF()
    {
        // 消耗品のQRコードをすべて取得する（現在カテゴリが消耗品のもの、変更されている可能性もある）
        // $consumableItems = Item::whereNull('deleted_at');

        // QRコード画像のパスを取得
        $qrCodes = [];
        for ($i = 1; $i <= 11; $i++) {
            // とりあえず1種類でを10枚で試してみる
            // $qrCodes[] = storage_path('app/public/qrcode/qrcode_' . $i . '.png');
            $qrCodes[] = Storage::path('public/labels/QRCodeTest_label.jpg');
        }

        // dd($qrCodes);
        \Log::info("qrCodes",$qrCodes);
        \Log::info("qrCodes");

        // ビューを作成してPDFを生成
        $pdf = PDF::loadView('pdf.pdf-preview-table', compact('qrCodes'))
            ->setPaper('A4')
            ->setOption('margin-left', '16.5mm')
            ->setOption('margin-right', '16.5mm')
            ->setOption('margin-top', '10mm')
            ->setOption('margin-bottom', '10mm')
            ->setOption('footer-center', '[page] / [topage]') // フッター中央に現在のページ番号と総ページ数を表示
            ->setOption('footer-font-size', 10);

        // return $pdf->download('消耗品QRコード.pdf');
        return $pdf->inline('消耗品QRコード.pdf');
    }

    public function designPDF()
    {
        $qrCodes = [];
        for ($i = 1; $i <= 11; $i++) {
            // $qrCodes[] = storage_path('app/public/qrcode/qrcode_' . $i . '.png');
            $qrCodes[] = Storage::url('public/labels/QRCodeTest_label.jpg');
        }

        // 配列を2つずつのチャンクに分割
        // $qrCodePairs = array_chunk($qrCodes, 2);

        // blade側でチャンクする
        return view('pdf.pdf-preview-table', compact('qrCodes'));
    }

    public function snappyPDF()
    {
        // QRコード画像のパスを取得
        $qrCodes = [];
        for ($i = 1; $i <= 10; $i++) {
            // とりあえず1種類でを10枚で試してみる
            // $qrCodes[] = storage_path('app/public/qrcode/qrcode_' . $i . '.png');
            $qrCodes[] = Storage::path('public/labels/QRCodeTest_label.jpg');
        }

        // dd($qrCodes);
        \Log::info("qrCodes",$qrCodes);
        \Log::info("qrCodes");

        // $qrCodePairs = array_chunk($qrCodes, 2);

        // ビューを作成してPDFを生成
        // $pdf = PDF::loadView('pdf.qrcodes', compact('qrCodePairs'));
        $pdf = PDF::loadView('pdf.pdf-snappy', compact('qrCodes'));
            // ->setPaper('A4','portrait')
            // ->setOption('margin-top', 0)
            // ->setOption('margin-bottom', 0)
            // ->setOption('margin-left', 0)
            // ->setOption('margin-right', 0);

        // return $pdf->download('消耗品QRコード.pdf');
        return $pdf->setPaper('a4')->inline('document.pdf');
    }

}
