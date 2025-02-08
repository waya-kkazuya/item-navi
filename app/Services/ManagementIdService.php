<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ManagementIdService
{
    public static function generate($category_id)
    {
        Log::info('ManagementIdService generate method called');

        $prefix      = '';
        $category_id = (int) $category_id; // 整数型にキャスト

        $prefix = match ($category_id) {
            1 => 'CO',  // 消耗品 (consumables)
            2 => 'IT',  // IT機器 (IT equipment)
            3 => 'SA',  // ソフトウェアアカウント (software account)
            4 => 'EA',  // 電化製品 (electronic appliances)
            5 => 'DP',  // 防災用品 (disaster preparedness supplies)
            6 => 'OS',  // オフィス用品 (office supplies)
            7 => 'OF',  // オフィス家具 (office furniture)
            8 => 'TO',  // 作業道具 (tools)
            9 => 'CL',  // 清掃用具 (cleaning tools)
            10 => 'GR', // 食料品 (groceries)
            11 => 'OT', // その他 (others)
            default => throw ValidationException::withMessages(['category_id' => 'カテゴリが不正です']),
        };

        do {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $managementId = $prefix . '-' . $randomNumber;
        } while (Item::where('management_id', $managementId)->exists());

        Log::info('ManagementIdService generate method succeedes');

        return $managementId;
    }
}
