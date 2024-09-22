<?php

namespace App\Observers;

use App\Models\Inspection;
use App\Models\Edithistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InspectionObserver
{
    /**
     * Handle the Inspection "created" event.
     */
    public function created(Inspection $inspection): void
    {
        //
    }

    /**
     * Handle the Inspection "updated" event.
     */
    public function updated(Inspection $inspection): void
    {
        // updatedメソッドが呼び出されるのは
        // 1,備品編集時->Edithistoryに保存
        // 2,点検実行時にフォーム入力で保存
        // 点検実行時はInspectionsテーブルの各項目にデータを保存して
        // statusはtrueになり、そのレコードはもう備品詳細画面には表示されない
        // where('status', false)->first()がnullになる
        $changes = $inspection->getChanges();

        // セッションから編集理由を取得
        $edit_reason_id = Session::get('edit_reeason_id');
        $edit_reason_text = Session::get('edit_reason_text');

        // dd($changes);
        // dd($changes['scheduled_date']);

        // scheduled_dateカラムのみを追跡
        if (isset($changes['inspection_scheduled_date'])) {

            // 仮置き
            $edit_mode = 'normal';

            $oldValue = $inspection->getOriginal('inspection_scheduled_date');
            $newValue = $changes['inspection_scheduled_date'];
            // dd($oldValue, $newValue);

            Edithistory::create([
                'edit_mode' => $edit_mode,
                'operation_type' => 'update',
                'item_id' => $inspection->item_id,
                'edited_field' => 'inspection_scheduled_date',
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'edit_user' => Auth::user()->name ?? '',
                'edit_reason_id' => $edit_reason_id, //プルダウン
                'edit_reason_text' => $edit_reason_text, //その他テキストエリア  
            ]);
        }
    }

    /**
     * Handle the Inspection "deleted" event.
     */
    public function deleted(Inspection $inspection): void
    {
        //
    }

    /**
     * Handle the Inspection "restored" event.
     */
    public function restored(Inspection $inspection): void
    {
        //
    }

    /**
     * Handle the Inspection "force deleted" event.
     */
    public function forceDeleted(Inspection $inspection): void
    {
        //
    }
}
