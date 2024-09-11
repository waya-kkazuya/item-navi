<?php

namespace App\Observers;

use App\Models\Disposal;
use App\Models\Edithistory;
use Illuminate\Support\Facades\Auth;

class DisposalObserver
{
    /**
     * Handle the Disposal "created" event.
     */
    public function created(Disposal $disposal): void
    {
        //
    }

    /**
     * Handle the Disposal "updated" event.
     */
    public function updated(Disposal $disposal): void
    {
        // updatedメソッドが呼び出されるのは
        // 1,備品編集時->Edithistoryに保存
        // 2,廃棄実行時にフォーム入力で保存
        // 廃棄実行時はDisposalsテーブルの各項目にデータを保存して
        // scheduled_dateはnullにする
        // 備品はソフトデリートされる、備品は廃棄済み備品一覧で確認可能
        // またretoreで復元できる
        $changes = $disposal->getChanges();


        // dd($changes);
        // dd($changes['scheduled_date']);

        // scheduled_dateカラムのみを追跡
        if (isset($changes['scheduled_date'])) {

            // 仮置き
            $edit_mode = 'normal';

            $oldValue = $disposal->getOriginal('scheduled_date');
            $newValue = $changes['scheduled_date'];
            // dd($oldValue, $newValue);

            Edithistory::create([
                'edit_mode' => $edit_mode,
                'operation_type' => 'update',
                'item_id' => $disposal->item_id,
                'edited_field' => 'disposal_scheduled_date',
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'edit_user' => Auth::user()->name ?? '',       
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
