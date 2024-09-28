<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class PDFController extends Controller
{
    public function generatePDF()
    {
        // QRコード画像のパスを取得
        $qrCodes = [];
        for ($i = 1; $i <= 10; $i++) {
            // とりあえず1種類でを10枚で試してみる
            // $qrCodes[] = storage_path('app/public/qrcode/qrcode_' . $i . '.png');
            // $qrCodes[] = storage_path('app/labels/QRCodeTest_label.jpg');
            $qrCodes[] = Storage::path('public/labels/QRCodeTest_label.jpg');
        }

        // dd($qrCodes);
        \Log::info("qrCodes",$qrCodes);
        \Log::info("qrCodes");

        // ビューを作成してPDFを生成
        $pdf = PDF::loadView('pdf.qrcodes', compact('qrCodes'));
        return $pdf->download('消耗品QRコード.pdf');
    }

    public function designPDF()
    {
        $qrCodes = [];
        for ($i = 1; $i <= 10; $i++) {
            // $qrCodes[] = storage_path('app/public/qrcode/qrcode_' . $i . '.png');
            $qrCodes[] = Storage::path('public/labels/QRCodeTest_label.jpg');
        }

        // 配列を2つずつのチャンクに分割
        $qrCodePairs = array_chunk($qrCodes, 2);

        return view('pdf.pdf-preview', compact('qrCodePairs'));
    }

}
