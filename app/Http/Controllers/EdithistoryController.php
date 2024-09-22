<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Edithistory;

class EdithistoryController extends Controller
{
    public function index(Request $request)
    {
        // mapで更新種類と更新フィールドを日本語に変換する必要がある
        // 新しいキーを作成するのが良い
         $edithistories = Edithistory::where('item_id', $request->item_id)
            ->with('editReason')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($edithistory) {
                // operation_typeの表示用の値を設定
                // operation_type=>更新種類　store(新規作成),update(編集更新),stock_in(入庫・在庫追加),stock_out(出庫・在庫消費),delete(廃棄),restore(復元)
                $edithistory->operation_type_for_display = match ($edithistory->operation_type) {
                    'store' => '新規登録',
                    'update' => '編集更新',
                    'stock_out' => '出庫',
                    'stock_in' => '入庫',
                    'soft_delete' => '廃棄',
                    'restore' => '復元',
                    default => '不明',
                };

                 // edited_fieldの表示用の値を設定
                $edithistory->edited_field_for_display = match ($edithistory->edited_field) {
                    'name' => '備品名',
                    'category_id' => 'カテゴリ',
                    'image1' => '画像',
                    'stock' => '在庫数',
                    'unit_id' => '単位',
                    'minimum_stock' => '通知在庫数',
                    'notification' => '通知オンオフ',
                    'usage_status_id' => '利用状況',
                    'end_user' => '利用者',
                    'location_of_use_id' => '利用場所',
                    'storage_location_id' => '保管場所',
                    'acquisition_method_id' => '取得区分',
                    'acquisition_source' => '取得先',
                    'price' => '取得価額',
                    'date_of_acquisition' => '取得年月日',
                    'manufacturer' => 'メーカー',
                    'product_number' => '製品番号',
                    'remarks' => '備考',
                    'inspection_scheduled_date' => '点検予定日',
                    'disposal_scheduled_date' => '廃棄予定日',
                    default => '不明',
                };

                return $edithistory;
            });

        return [
            'edithistories' => $edithistories
        ];
    }
}
