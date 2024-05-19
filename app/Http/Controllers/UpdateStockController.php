<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;

class UpdateStockController extends Controller
{
    public function update($id, $newStockValue)
    {
        // Itemモデルのインスタンスを取得
        $item = Item::find($id);

        // stocksカラムの値を更新
        $item->stocks = $newStockValue;

        // データベースに保存
        $item->save();

    }
}
