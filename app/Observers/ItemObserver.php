<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\Edithistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ItemObserver
{
    // storeメソッドのItem::createの呼び出しが行われる前に保存される
    public function creating(Item $item)
    {
        $item->management_id = $this->generateManagementId($item->category_id);
    }

    private function generateManagementId($category_id)
    {
        $prefix = '';

        switch ($category_id) {
            case 1: // 消耗品
                $prefix = 'CO';
                break;
            case 2 : // IT機器
                $prefix = 'IT';
                break;
            case 3: // ソフトウェアアカウント
                $prefix = 'SA';
                break;
            case 4: // 電化製品
                $prefix = 'EA';
                break;
            case 5: // 防災用品
                $prefix = 'DP';
                break;
            case 6: // オフィス用品
                $prefix = 'OS';
                break;
            case 7: // オフィス家具
                $prefix = 'OF';
                break;
            case 8: // 作業道具
                $prefix = 'TO';
                break;
            case 9: // 清掃用具
                $prefix = 'CL';
                break;
            case 10: // その他
                $prefix = 'OT';
                break;
            default:
                // カテゴリが無効な場合に例外をスロー
                throw ValidationException::withMessages(['category_id' => 'カテゴリが不正です']);
        }

        do {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $managementId = $prefix . '-' . $randomNumber;
        } while (Item::where('management_id', $managementId)->exists());

        return $managementId;
    }


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
        // ソフとデリートを除外する必要あるか
        // unset($changes['softdeletes']);

        // 仮置き
        $edit_mode = 'normal';

        // ココの部分は通常時の分だけでも作ってしまった方が良い
        // 棚卸時のURLを作成したら、Request::url()で$edit_typeを分ける
        // $url = Request::url();
        // if (strpos($url, 'normal-edit-url') !== false) {
        //     $model->edithistory()->create(['edit_type' => '通常時']);
        // } elseif (strpos($url, 'inventory-edit-url') !== false) {
        //     $model->edithistory()->create(['edit_type' => '棚卸時']);
        // }


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
            ]);
        }
    }


    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        // ソフトデリート
        Edithistory::create([
            'edit_mode' => 'normal' ,
            'operation_type' => 'delete',
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
