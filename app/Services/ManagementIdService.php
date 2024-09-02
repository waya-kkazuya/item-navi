<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Validation\ValidationException;

class ManagementIdService
{
    public static function generate($category_id)
    {
        $prefix = '';

        switch ($category_id) {
            case 1: // 消耗品
                $prefix = 'CO';
                break;
            case 2 : // IT機器
                $prefix = 'IT';
                break;
            case 3: // ソフトウェアアカウント
                $prefix = 'SA';
                break;
            case 4: // 電化製品
                $prefix = 'EA';
                break;
            case 5: // 防災用品
                $prefix = 'DP';
                break;
            case 6: // オフィス用品
                $prefix = 'OS';
                break;
            case 7: // オフィス家具
                $prefix = 'OF';
                break;
            case 8: // 作業道具
                $prefix = 'TO';
                break;
            case 9: // 清掃用具
                $prefix = 'CL';
                break;
            case 10: // その他
                $prefix = 'OT';
                break;
            default:
                // カテゴリが無効な場合に例外をスロー
                throw ValidationException::withMessages(['category_id' => 'カテゴリが不正です']);
        }

        do {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $managementId = $prefix . '-' . $randomNumber;
        } while (Item::where('management_id', $managementId)->exists());

        return $managementId;
    }
}