<?php

namespace App\Http\Controllers;

use App\Events\LowStockDetectEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\DecreaseStockRequest;
use App\Http\Requests\IncreaseStockRequest;
use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStockController extends Controller
{
    public function decreaseStock(DecreaseStockRequest $request, Item $item)
    {
        Log::info('UpdateStockController decreaseStock method called');

        DB::beginTransaction();

        try {
            // 念のため、サーバーサイドでもバリデーション
            if ($request->quantity <= 0 || $request->quantity > $item->stock) {
                Log::warning('UpdateStockController decreaseStock method failed');

                return to_route('consumable_items')
                    ->with([
                        'message' => '出庫数に正しい値を入力してください',
                        'status' => 'danger'
                    ]);
            }

            // stock_transactionsテーブルの新しいレコードを作成
            $stockTransaction = new StockTransaction();
            $stockTransaction->item_id = $item->id;
            $stockTransaction->transaction_type = $request->transaction_type;
            $stockTransaction->quantity = -$request->quantity; //出庫なので数量をマイナスにして保存
            $stockTransaction->operator_name = $request->operator_name;
            $stockTransaction->save();

            // ItemObserverを無効化して在庫数を更新
            Item::withoutEvents(function () use ($item, $request) {
                $item->stock -= $request->quantity;
                $item->save();
            });

            // 通知がオンになっている、かつ、在庫数が通知在庫数以下になったら通知を送る
            if ($item->notification && $item->stock <= $item->minimum_stock) {
                event(new LowStockDetectEvent($item));
            }

            DB::commit();

            Log::info('UpdateStockController decreaseStock method succeeded');

            return to_route('consumable_items')
                ->with([
                    'message' => '在庫数を更新しました。',
                    'status' => 'success'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('UpdateStockController decreaseStock method Transaction failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()->back()
                ->with([
                    'message' => '登録中にエラーが発生しました',
                    'status' => 'danger'
                ]);
        }
    }

    public function increaseStock(IncreaseStockRequest $request, Item $item)
    {
        Log::info('UpdateStockController increaseStock method called');

        DB::beginTransaction();

        try {
            // 念のため、サーバーサイドでもバリデーション
            if ($request->quantity <= 0) {
                Log::warning('UpdateStockController increaseStock method failed');

                return to_route('consumable_items')
                    ->with([
                        'message' => '出庫数に正しい値を入力してください',
                        'status' => 'danger'
                    ]);
            }

            $stockTransaction = new StockTransaction();
            $stockTransaction->item_id = $item->id;
            $stockTransaction->transaction_type = $request->transaction_type;
            $stockTransaction->quantity = $request->quantity;
            $stockTransaction->operator_name = $request->operator_name;
            $stockTransaction->save();

            // ItemObserverを無効化して在庫数を更新
            Item::withoutEvents(function () use ($item, $request) {
                $item->stock += $request->quantity;
                $item->save();
            });

            DB::commit();

            Log::info('UpdateStockController increaseStock method succeeded');

            return to_route('consumable_items')
                ->with([
                    'message' => '在庫数を更新しました。',
                    'status' => 'success'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('UpdateStockController increaseStock method Transaction failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()->back()
                ->with([
                    'message' => '登録中にエラーが発生しました',
                    'status' => 'danger'
                ]);
        }
    }
}
