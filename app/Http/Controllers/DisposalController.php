<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisposalRequest;
use App\Models\Disposal;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class DisposalController extends Controller
{
    public function disposeItem(StoreDisposalRequest $request, Item $item)
    {
        DB::beginTransaction();

        try {
        
            $disposal = Disposal::where('item_id', $item->id)->first();
            // dd($disposal);

            if (is_null($disposal)) {
                // 新しいレコードを作成
                $disposal = new Disposal();
                $disposal->item_id = $item->id;
            }

            // 廃棄が実施されたので、予定日は初期化にする
            $disposal->disposal_scheduled_date = null;
            $disposal->disposal_date = $request->disposal_date;
            $disposal->disposal_person = $request->disposal_person;
            $disposal->details = $request->details;
            $disposal->save();

            // ソフトデリート
            $item->delete();

            DB::commit();

            return to_route('items.index')
            ->with([
                'message' => '廃棄しました。',
                'status' => 'danger'
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
