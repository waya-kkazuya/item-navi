<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInspectionRequest;
use App\Models\Edithistory;
use App\Models\Inspection;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InspectionController extends Controller
{
    public function inspectItem(StoreInspectionRequest $request, Item $item)
    {
        DB::beginTransaction();

        Log::info('InspectionController inspectItem method called');

        try {
            // 処理１、Inspectionテーブルのstatusがfalseの登録日が一番古い日付のレコードを取得
            $inspection = Inspection::where('item_id', $item->id)
                ->where('status', false)
                ->orderBy('inspection_scheduled_date', 'asc')
                ->first();

            // 仕様上は$inspectionのレコードは1件しか存在しない
            // 処理２，予定日を保存していない場合はレコードが返らずnullとなるので新規作成
            if (is_null($inspection)) {
                // 新しいレコードを作成
                $inspection          = new Inspection();
                $inspection->item_id = $item->id;
                $inspection->status  = false;
            }

            // 処理３，Inspectionテーブルのレコードに値を保存
            // $inspection->scheduled_date = null; // 廃棄の時のようにレコードを使いまわさず、記録として残す
            $inspection->inspection_date   = $request->inspection_date;
            $inspection->inspection_person = $request->inspection_person;
            $inspection->details           = $request->details;
            $inspection->status            = true; // 点検実行済みとしてstatusを変更

            // InspectionObserverを一時的に無効にして保存
            Inspection::withoutEvents(function () use ($inspection) {
                $inspection->save();
            });

            // 点検をしたというoperation_typeのみをDBに保存する
            Edithistory::create([
                'edit_mode'        => 'normal',
                'operation_type'   => 'inspection',
                'item_id'          => $inspection->item_id,
                'edited_field'     => null,
                'old_value'        => null,
                'new_value'        => null,
                'edit_user'        => Auth::user()->name ?? '',
                'edit_reason_id'   => null, //プルダウン
                'edit_reason_text' => null, //その他テキストエリア
            ]);

            DB::commit();

            Log::info('InspectionController inspectItem method succeeded');

            return to_route('items.show', ['item' => $item->id])
                ->with([
                    'message' => '点検を実施しました。',
                    'status'  => 'success',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('InspectionController inspectItem method Transaction failed', [
                'error'   => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->back()
                ->with([
                    'message' => '登録中にエラーが発生しました',
                    'status'  => 'danger',
                ]);
        }
    }
}
