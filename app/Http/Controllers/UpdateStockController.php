<?php

namespace App\Http\Controllers;

use App\Events\LowStockDetectEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Http\Requests\DecreaseStockRequest;
use App\Http\Requests\IncreaseStockRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateStockController extends Controller
{
    public function decreaseStock(DecreaseStockRequest $request, Item $item)
    {
        Gate::authorize('user-higher');
        // dd($request);
        // dd($item);
        DB::beginTransaction();

        try {

            // バリデーションルールで'min:1'
            // 念のため、サーバーサイドでもquantitiy=0対策をすpる
            // 在庫数より大きいquantityは出庫出来ない
            if($request->quantity <= 0 || $request->quantity > $item->stock){
                return to_route('consumable_items')
                ->with([
                    'message' => '出庫数に正しい値を入力してください',
                    'status' => 'danger'
                ]);
            }
            
            // stock_transactionsテーブルに保存し、Item->stockを更新する
            // 新しいレコードを作成
            $stockTransaction = new StockTransaction();
            $stockTransaction->item_id = $item->id;
            $stockTransaction->transaction_type = $request->transaction_type;
            $stockTransaction->quantity = $request->quantity;
            $stockTransaction->operator_name = $request->operator_name;
            $stockTransaction->transaction_date = $request->transaction_date;
            $stockTransaction->save();

            // itemsテーブルのstockカラムの値を更新
            $item->stock -= $request->quantity;
            $item->save();

            // LowStockDetectEventのイベント発火
            // 通知がオンになっている、かつ、在庫数が通知在庫数を下回ったら通知を送る
            if($item->notification && $item->stock <= $item->minimum_stock) {
                event(new LowStockDetectEvent($item));
            }
            
            DB::commit();

            return to_route('consumable_items')
            ->with([
                'message' => '在庫数を更新しました。',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
            ->with([
                'message' => '登録中にエラーが発生しました',
                'status' => 'danger'
            ]);
        }
    }



    // Increase用のRequestファイルが必要
    public function increaseStock(IncreaseStockRequest $request, Item $item)
    {
        Gate::authorize('user-higher');
        // dd($request);
        // dd($item);
        DB::beginTransaction();

        try {

            // バリデーションルールで'min:1'
            // 念のため、サーバーサイドでもquantitiy=0対策をする
            // 在庫数より大きいquantityは出庫出来ない
            if($request->quantity <= 0){
                return to_route('consumable_items')
                ->with([
                    'message' => '出庫数に正しい値を入力してください',
                    'status' => 'danger'
                ]);
            }
            
            // stock_transactionsテーブルに保存し、Item->stockを更新する
            // 新しいレコードを作成
            $stockTransaction = new StockTransaction();
            $stockTransaction->item_id = $item->id;
            $stockTransaction->transaction_type = $request->transaction_type;
            $stockTransaction->quantity = $request->quantity;
            $stockTransaction->operator_name = $request->operator_name;
            $stockTransaction->transaction_date = $request->transaction_date;
            $stockTransaction->save();

            // itemsテーブルのstockカラムの値を更新
            $item->stock += $request->quantity;
            $item->save();


            // 在庫数が通知在庫数以下になったときにイベントを発火
            // LowStockDetectEventのイベント発火
            // event(new LowStockDetectEvent($item));
            // // event(new LowStockDetectEvent('こんにちは！'));
            
            DB::commit();

            return to_route('consumable_items')
            ->with([
                'message' => '在庫数を更新しました。',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
            ->with([
                'message' => '登録中にエラーが発生しました',
                'status' => 'danger'
            ]);
        }
   
    }
}
