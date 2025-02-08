<?php

namespace App\Observers;

use App\Models\Edithistory;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ItemObserver
{
    // storeメソッドのItem::createの呼び出しが行われる前に保存される
    public function creating(Item $item) {}

    public function created(Item $item): void
    {
        // 注意 createdはseederやfactoryでダミーデータを作成した時も動く
        Edithistory::create([
            'edit_mode'      => 'normal',
            'operation_type' => 'store',
            'item_id'        => $item->id,
            'edited_field'   => null,
            'old_value'      => null,
            'new_value'      => null,
            'edit_user'      => Auth::user()->name ?? '',
        ]);
    }

    public function updated(Item $item): void
    {
        $changes = $item->getChanges();

        unset($changes['updated_at'], $changes['qrcode'], $changes['deleted_at']);

        $edit_reason_id   = Session::get('edit_reason_id');
        $edit_reason_text = Session::get('edit_reason_text');

        $edit_mode = 'normal'; // 仮置き

        foreach ($changes as $field => $newValue) {
            $oldValue = $item->getOriginal($field);

            if ($field == 'image1') {
                $oldValue = null;
                $newValue = null;
            }

            Edithistory::create([
                'edit_mode'        => $edit_mode,
                'operation_type'   => 'update',
                'item_id'          => $item->id,
                'edited_field'     => $field,
                'old_value'        => $oldValue,
                'new_value'        => $newValue,
                'edit_user'        => Auth::user()->name ?? '',
                'edit_reason_id'   => $edit_reason_id,
                'edit_reason_text' => $edit_reason_text,
            ]);
        }
    }

    public function deleted(Item $item): void {}

    public function restored(Item $item): void
    {
        // 備品復元時
        Edithistory::create([
            'edit_mode'        => 'normal',
            'operation_type'   => 'restore',
            'item_id'          => $item->id,
            'edited_field'     => null,
            'old_value'        => null,
            'new_value'        => null,
            'edit_user'        => Auth::user()->name ?? '',
            'edit_reason_id'   => null, //プルダウン
            'edit_reason_text' => null, //その他テキストエリア
        ]);
    }

    public function forceDeleted(Item $item): void
    {
        //
    }
}
