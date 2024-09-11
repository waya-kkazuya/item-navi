<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            $qrCodes[] = storage_path('app/labels/QRCodeTest_label.jpg');
        }

        // ビューを作成してPDFを生成
        $pdf = PDF::loadView('pdf.qrcodes', compact('qrCodes'));
        return $pdf->download('qrcodes.pdf');
    }
}
