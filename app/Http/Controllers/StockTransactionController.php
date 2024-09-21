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


    // 入出庫履歴と同じ日付のグラフタブ用
    // ConsumableItemConrollerから移してきた、要改修
    public function history($id)
    {
        $item = Item::findOrFail($id);
        $item->image_path1 = asset('storage/items/' . $item->image_path1);

        // 今日の日付
        $endDate = Carbon::today();
        // 1週間前の日付
        $startDate = Carbon::today()->subWeek();

        $subQuery = Edithistory::betweenDate($startDate, $endDate)
        ->where('category_id', 1)
        ->where('item_id', $id)
        ->where('edited_field', 'stock')
        ->select('action_type', 'old_value', 'new_value','edited_at');
        

        // 入庫と出庫の場合でそれぞれ在庫数の差を取得している
        $data = DB::table($subQuery)
        ->select('action_type','old_value', 'new_value',
            DB::raw('CASE WHEN action_type = "入庫" THEN new_value - old_value ELSE 0 END as input'),
            DB::raw('CASE WHEN action_type = "出庫" THEN old_value - new_value ELSE 0 END as output'),
            'edited_at')
        ->orderBy('edited_at', 'desc')
        ->get();

        // LineChart用の昇順のデータ
        // reverse()では機能しないので、orderByで昇順に並べる
        // 'new_value'はその時に確定した在庫数
        $forChart = DB::table($subQuery)
        ->select('new_value','edited_at')
        ->orderBy('edited_at', 'Asc')
        ->get();

        $labels = $forChart->pluck('edited_at');
        $stocks = $forChart->pluck('new_value');

        // dd($data);

        return Inertia::render('ConsumableItems/History', [
            'data' => $data,
            'labels' => $labels,
            'stocks' => $stocks
        ]);
    }
}
