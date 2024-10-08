<?php

namespace App\Observers;

use App\Models\Disposal;
use App\Models\Edithistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DisposalObserver
{
    /**
     * Handle the Disposal "created" event.
     */
    public function created(Disposal $disposal): void
    {
        // パターン
        // 1,ItemControllerのstoreメソッドでcreate
        // 2,ItemControllerのupdateメソッドでupdate レコードがある場合
        // 3,ItemControllerのupdateメソッドでcreate レコードがない場合

        $edit_reason_id = Session::get('edit_reeason_id');
        $edit_reason_text = Session::get('edit_reason_text');
        $operation_type = Session::get('operation_type');

        // 仮置き
        $edit_mode = 'normal';

        $oldValue = null;
        $newValue = $disposal->disposal_scheduled_date; 

        Edithistory::create([
            'edit_mode' => $edit_mode,
            'operation_type' => $operation_type ?? 'store',
            'item_id' => $disposal->item_id,
            'edited_field' => 'disposal_scheduled_date',
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'edit_user' => Auth::user()->name ?? '',
            'edit_reason_id' => $edit_reason_id ?? null, //プルダウン
            'edit_reason_text' => $edit_reason_text ?? null, //その他テキストエリア  
        ]);
    }

    /**
     * Handle the Disposal "updated" event.
     */
    public function updated(Disposal $disposal): void
    {
        // updatedメソッドが呼び出されるのは
        // 2,ItemControllerのupdateメソッドでupdate レコードがある場合
        // 4,廃棄実行時にフォーム入力でレコード更新(廃棄予定日がレコードに登録されている場合)
        // 廃棄実行時はDisposalsテーブルの各項目にデータを保存、disposal_scheduled_dateはnullにする
        $changes = $disposal->getChanges();

        // セッションから編集理由を取得
        $edit_reason_id = Session::get('edit_reeason_id');
        $edit_reason_text = Session::get('edit_reason_text');
        $operation_type = Session::get('operation_type');

        // scheduled_dateカラムのみを追跡
        if (isset($changes['disposal_scheduled_date'])) {

            // 仮置き
            $edit_mode = 'normal';

            $oldValue = $disposal->getOriginal('disposal_scheduled_date');
            $newValue = $changes['disposal_scheduled_date'];

            Edithistory::create([
                'edit_mode' => $edit_mode,
                'operation_type' => $operation_type,
                'item_id' => $disposal->item_id,
                'edited_field' => 'disposal_scheduled_date',
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'edit_user' => Auth::user()->name ?? '',
                'edit_reason_id' => $edit_reason_id, //プルダウン
                'edit_reason_text' => $edit_reason_text, //その他テキストエリア 
            ]);
        }        
    }

    /**
     * Handle the Disposal "deleted" event.
     */
    public function deleted(Disposal $disposal): void
    {
        //
    }

    /**
     * Handle the Disposal "restored" event.
     */
    public function restored(Disposal $disposal): void
    {
        //
    }

    /**
     * Handle the Disposal "force deleted" event.
     */
    public function forceDeleted(Disposal $disposal): void
    {
        //
    }
}
