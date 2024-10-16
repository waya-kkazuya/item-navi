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
        ->groupBy('date');

    $groupedEditHistories = $editHistories->map(function ($histories) {
        return $histories->map(function ($history) {
            // 日本語の曜日をセット
            $days = ['日', '月', '火', '水', '木', '金', '土'];
            $history->day_of_week = $days[$history->day_of_week - 1];

            // operation_typeに応じて文章を追加
            switch ($history->operation_type) {
                case 'store':
                    $history->operation_description = '新規登録';
                    break;
                case 'update':
                    $history->operation_description = '更新';
                    break;
                case 'stock_out':
                    $history->operation_description = 'の在庫を出庫';
                    break;
                case 'stock_in':
                    $history->operation_description = 'の在庫を入庫';
                    break;
                case 'soft_delete':
                    $history->operation_description = '廃棄';
                    break;
                case 'restore':
                    $history->operation_description = '復元';
                    break;
                default:
                    $history->operation_description = '不明な操作を';
                    break;
            }

            return $history;
        });
    });

    return $groupedEditHistories;
  }
}