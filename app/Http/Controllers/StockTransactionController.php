<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockTransaction ;
use Illuminate\Support\Facades\Log;

class StockTransactionController extends Controller
{
    public function stockTransaction(Request $request)
    {
        $itemId = $request->item_id;
        Log::info('item_id');
        Log::info($itemId);

        $item = Item::find($itemId);

        // 最新の在庫数を取得
        $current_stock = $item->stock;
        log::info('current_stock');
        log::info($current_stock);

        // stock_transactionsを取得 withを使うとitemの情報すべてが取れてくる
        $stock_transactions = StockTransaction::where('item_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        Log::info('stock_transactions');
        Log::info($stock_transactions);

        // $current_stockはクロージャ―の外で定義されており、&を付けることで参照渡ししている
        // よって、値を引き継いだままループをする 1つずつズレる
        $stock_transactions->each(function ($transaction) use (&$current_stock) {
            // 現在のレコードの在庫数を設定
            $transaction->current_stock = $current_stock;
        
            // 次のレコードの在庫数を計算、次に持ち越し
            if ($transaction->transaction_type === '出庫') {
                $current_stock += $transaction->quantity;
            } elseif ($transaction->transaction_type === '入庫') {
                $current_stock -= $transaction->quantity;
            }
        });

        return [
            'stockTransactions' => $stock_transactions
        ];
    }
}
