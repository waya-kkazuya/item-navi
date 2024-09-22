<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\Edithistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;

class ItemObserver
{
    // storeメソッドのItem::createの呼び出しが行われる前に保存される
    // public function creating(Item $item)
    // {
    //     
    // }

    public function created(Item $item): void
    {
        // 注意
        //createdはseederやfactoryでダミーデータを作成した時も動く

        // 新規作成時はitem_idとedit_user,edited_atがあればいいのでは。
        // category_idはitemにあるし、category_id(カテゴリ)も変化する可能性がある
        Edithistory::create([
            'edit_mode' => 'normal' ,
            'operation_type' => 'store',
            'item_id' => $item->id,
            'edited_field' => null,
            'old_value' => null,
            'new_value' => null,
            'edit_user' => Auth::user()->name ?? '',      
        ]);
    }


    public function updated(Item $item): void
    {
        $changes = $item->getChanges();

        unset($changes['updated_at']);
        // unset($changes['softdeletes']);


        // セッションから編集理由を取得
        $edit_reason_id = Session::get('edit_reeason_id');
        $edit_reason_text = Session::get('edit_reason_text');

        // dd($edit_reason_id, $edit_reason_text);

        // 仮置き
        $edit_mode = 'normal';

        // 備品編集updateを行った際、セッションに編集理由を保存
        // □UpdateItemRequestのリクエストファイルにバージョンルールを記載する

        foreach ($changes as $field => $newValue) {
            $oldValue = $item->getOriginal($field);

            Edithistory::create([
                'edit_mode' => $edit_mode,
                'operation_type' => 'update',
                'item_id' => $item->id,
                'edited_field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'edit_user' => Auth::user()->name ?? '',
                'edit_reason_id' => $edit_reason_id, //プルダウン
                'edit_reason_text' => $edit_reason_text, //その他テキストエリア
            ]);
        }

        // ココの部分は通常時の分だけでも作ってしまった方が良い
        // 棚卸時のURLを作成したら、Request::url()で$edit_typeを分ける
        // $url = Request::url();
        // if (strpos($url, 'normal-edit-url') !== false) {
        //     $model->edithistory()->create(['edit_type' => '通常時']);
        // } elseif (strpos($url, 'inventory-edit-url') !== false) {
        //     $model->edithistory()->create(['edit_type' => '棚卸時']);
        // }
    }


    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        // ソフトデリート
        Edithistory::create([
            'edit_mode' => 'normal' ,
            'operation_type' => 'soft_delete',
            'item_id' => $item->id,
            'edited_field' => null,
            'old_value' => null,
            'new_value' => null,
            'edit_user' => Auth::user()->name ?? '',        
        ]);
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        // 備品復元時
        Edithistory::create([
            'edit_mode' => 'normal' ,
            'operation_type' => 'restore',
            'item_id' => $item->id,
            'edited_field' => null,
            'old_value' => null,
            'new_value' => null,       
        ]);
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        //
    }
}
