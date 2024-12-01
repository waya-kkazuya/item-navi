<?php

namespace App\UseCases\Dashboard;

use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;

class GroupedEditHistoriesUseCase
{
  public function handle()
  {
    // 編集履歴を日付ごとにまとめる加工
    $editHistories = EditHistory::with(['item' => function ($query) {
                    $query->withTrashed();
                }, 'editReason'])
        ->select(
            DB::raw('DATE(created_at) as date'), 
            DB::raw('DAYOFWEEK(created_at) as day_of_week'),
            DB::raw('DATE_FORMAT(created_at, "%H:%i") as time'), 
                'id', 
                'edit_mode', 
                'operation_type', 
                'item_id', 
                'edited_field', 
                'old_value', 
                'new_value', 
                'edit_user', 
                'edit_reason_id', 
                'edit_reason_text', 
                'created_at',
            )
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get()
        ->groupBy('date'); //日付(年月)でまとめる

    $groupedEditHistories = $editHistories->map(function ($histories) {
        return $histories->map(function ($history) {
            // 日本語の曜日をセット
            $days = ['日', '月', '火', '水', '木', '金', '土'];
            $history->day_of_week = $days[$history->day_of_week - 1];

            $history->operation_description = match ($history->operation_type) {
                'store' => '新規登録',
                'update' => '編集',
                'soft_delete' => '廃棄',
                'restore' => '復元',
                default => '不明な操作を',
            };

            // edited_fieldの編集履歴に表示用の値を設定
            $history->edited_field_for_display = match ($history->edited_field) {
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
                default => null,
            };

            return $history;
        });
    });

    return $groupedEditHistories;
  }
}