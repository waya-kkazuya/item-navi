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
                'update' => '更新',
                'soft_delete' => '廃棄',
                'restore' => '復元',
                default => '不明な操作を',
            };

            return $history;
        });
    });

    return $groupedEditHistories;
  }
}