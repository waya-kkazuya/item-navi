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
        $edit_reason_id = Session::get('edit_reason_id');
        $edit_reason_text = Session::get('edit_reason_text');
        $operation_type = Session::get('operation_type');
       
        $edit_mode = 'normal'; //仮置き

        $oldValue = null;
        $newValue = $disposal->disposal_scheduled_date; 

        if($operation_type == 'update') {
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
     * Handle the Disposal "updated" event.
     */
    public function updated(Disposal $disposal): void
    {
        $changes = $disposal->getChanges();

        // セッションから編集理由を取得
        $edit_reason_id = Session::get('edit_reason_id');
        $edit_reason_text = Session::get('edit_reason_text');
        $operation_type = Session::get('operation_type');

        if (isset($changes['disposal_scheduled_date'])) {
            
            $edit_mode = 'normal'; //仮置き

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
                'edit_reason_id' => $edit_reason_id,
                'edit_reason_text' => $edit_reason_text,
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
