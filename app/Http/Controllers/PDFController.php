<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDFController extends Controller
{
    const CATEGORY_ID_FOR_CONSUMABLE_ITME = 1;

    public function generatePDF()
    {
        Gate::authorize('user-higher');

        Log::info('PDFController generatePDF method called');

        // 消耗品のQRコードをすべて取得する（現在カテゴリが消耗品のもの、変更されている可能性もある）
        $consumableItems = Item::whereNull('deleted_at')
            ->where('category_id', self::CATEGORY_ID_FOR_CONSUMABLE_ITME)
            ->get();

        // QRラベル画像のパスの配列を取得
        $qrCodes = [];
        foreach ($consumableItems as $consumableItem) {
            $qrCodes[] = config('filesystems.default') === 's3' 
                ? Storage::disk()->url('labels/' . $consumableItem->qrcode)
                : Storage::disk()->path('labels/' . $consumableItem->qrcode);
        }

        if (empty($qrCodes)) {
            Log::warning('PDFController generatePDF method failed because QRLabel does not exist');

            return to_route('consumable_items')
                ->with([
                    'message' => 'QRラベルが存在しません',
                    'status'  => 'danger',
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
            ->setOption('footer-font-size', 10)
            ->setOption('enable-local-file-access', true);

        Log::info('PDFController generatePDF method succeeded');

        return $pdf->inline('消耗品QRコード.pdf');
    }
}
