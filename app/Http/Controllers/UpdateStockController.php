<?php

namespace App\Http\Controllers;

use App\Events\LowStockDetectEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Http\Requests\UpdateStockRequest;

class UpdateStockController extends Controller
{
    public function updateStock(UpdateStockRequest $request, Item $item)
    {
        // Itemモデルのインスタンスを取得
        // dd($id);
        $item = Item::find($id);
        // dd($item);
        $stockValue = $request->input('stockValue');
        $action = $request->input('action');

        // dd($stockValue, $action);

        // 念のため、サーバーサイドでもstockValue=0対策をする
        if($stockValue === 0){
            return to_route('consumable_items')
            ->with([
                'message' => '入出庫数を入力してください',
                'status' => 'danger'
            ]);
        }

        // stocksカラムの値を更新
        // 出庫の場合、$stockValueを引いたあと、0より大きい場合は実行
        // 0より小さい場合(マイナス)の場合、在庫数が更新できませんでしたのバリデーションエラーか
        // 入庫の場合は増えるだけなので

        if ($action === 'out' && $item->stocks < $stockValue) {
            // return response()->json(['error' => '在庫が足りません'], 400);
            return to_route('consumable_items')
            ->with([
                'message' => '在庫数が足りません',
                'status' => 'danger'
            ]);
        }
    
        if ($action === 'in') {
            $item->stocks += $stockValue;
        } else if ($action === 'out') {
            $item->stocks -= $stockValue;
        }
        
        // データベースに保存
        $item->save();


        // イベント発火
        // テスト
        event(new LowStockDetectEvent($item));
        // event(new LowStockDetectEvent('こんにちは！'));



        return to_route('consumable_items')
        ->with([
            'message' => '在庫数を更新しました。',
            'status' => 'success'
        ]);

    }
}
