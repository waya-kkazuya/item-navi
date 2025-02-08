<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisposalRequest;
use App\Models\Disposal;
use App\Models\Edithistory;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisposalController extends Controller
{
    public function disposeItem(StoreDisposalRequest $request, Item $item)
    {
        DB::beginTransaction();

        Log::info('DisposalController disposeItem method called');

        try {
            $disposal = Disposal::where('item_id', $item->id)->first();

            if (is_null($disposal)) {
                // 新しいレコードを作成
                $disposal          = new Disposal();
                $disposal->item_id = $item->id;
            }

            // 廃棄が実施されたので、予定日は初期化にする
            $disposal->disposal_scheduled_date = null;
            $disposal->disposal_date           = $request->disposal_date;
            $disposal->disposal_person         = $request->disposal_person;
            $disposal->details                 = $request->details;

            // DisposalObserverを一時的に無効にして保存
            Disposal::withoutEvents(function () use ($disposal) {
                $disposal->save();
            });

            // 廃棄なのでソフトデリート、ItemObserverが動く
            $item->delete();

            // 廃棄したというoperation_typeのみをDBに保存する
            Edithistory::create([
                'edit_mode'      => 'normal',
                'operation_type' => 'soft_delete',
                'item_id'        => $item->id,
                'edited_field'   => null,
                'old_value'      => null,
                'new_value'      => null,
                'edit_user'      => Auth::user()->name ?? '',
            ]);

            DB::commit();

            Log::info('DisposalController disposeItem method succeeded');

            return to_route('items.index')
                ->with([
                    'message' => '廃棄しました。',
                    'status'  => 'danger',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('DisposalController disposeItem method Transaction failed', [
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
