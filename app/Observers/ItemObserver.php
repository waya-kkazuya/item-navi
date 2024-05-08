<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\Edithistory;
use Illuminate\Support\Facades\Auth;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        // 注意
        //createdはseederやfactoryでダミーデータを作成した時も動く
        
        // 新規作成時はitem_idとedit_user,edited_atがあればいいのでは。
        Edithistory::create([
            'item_id' => $item->id,
            'edited_field' => '-',
            'old_value' => '-',
            'new_value' => '-',
            'edit_user' => Auth::user()->name ?? '',
            'edited_at' => now(),            
        ]);
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        $changes = $item->getChanges();

        foreach ($changes as $field => $newValue) {
            $oldValue = $item->getOriginal($field);

            Edithistory::create([
                'item_id' => $item->id,
                'edited_field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'edit_user' => Auth::user(),
                'edited_at' => now(),            
            ]);
        }
    }


    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        //
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        //
    }
}
