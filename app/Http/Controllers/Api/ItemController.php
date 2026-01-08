<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function restore($id)
    {
        Log::info('Api/ItemController restore method called');

        $item = Item::withTrashed()->find($id);
        if ($item) {
            $item->restore();

            Log::info('Api/ItemController restore method succeeded');

            return response()->json([
                'message' => '備品を復元しました',
                'status'  => 'success',
                'item'    => $item, // 復元したアイテムの情報を返す
            ]);
        }

        Log::warning('Api/ItemController restore method failed');

        return response()->json([
            'message' => '該当の備品が存在しませんでした',
            'status'  => 'danger',
        ], 404);
    }
}
