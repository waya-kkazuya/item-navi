<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use App\Models\Item;
use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // dd($request);
        Gate::authorize('staff-higher');

        // $items = Item::with('category')->get();
        // $itemsByCategory = Item::with('category')->get()->groupBy('category.name');

        // 1ステップ設ける 第二引数は初期値
        $type = $request->input('type', 1);

        // if文で条件分岐
        if ($type == 1) {
            // 備品をカテゴリでまとめたもの
            $items = Item::with('category')->get();
            $itemsByType = $items->groupBy(function ($item) {
                return $item->category->name;
            })->map(function ($group) {
                return [
                    'category_id' => $group->first()->category->id,
                    'items' => $group
                ];
            });
        } else {
            // 備品を利用場所でまとめたもの
            $items = Item::with('locationOfUse')->get();
            $itemsByType = $items->groupBy(function ($item) {
                return $item->locationOfUse->name;
            })->map(function ($group) {
                return [
                    'location_of_use_id' => $group->first()->locationOfUse->id,
                    'items' => $group
                ];
            });
        }

        // // 日付ごとにまとめる
        // $editHistories = DB::table('edithistories')
        // ->join('items', 'edithistories.item_id', '=', 'items.id')
            // ->select(
            //     DB::raw('DATE(edithistories.created_at) as date'), 
            //     DB::raw('DAYOFWEEK(edithistories.created_at) as day_of_week'),
            //     DB::raw('DATE_FORMAT(edithistories.created_at, "%H:%i") as time'), 
            //         'edithistories.id', 
            //         'edithistories.edit_mode', 
            //         'edithistories.operation_type', 
            //         'edithistories.item_id', 
            //         'edithistories.edited_field', 
            //         'edithistories.old_value', 
            //         'edithistories.new_value', 
            //         'edithistories.edit_user', 
            //         'edithistories.edit_reason_id', 
            //         'edithistories.edit_reason_text', 
            //         'edithistories.created_at',
            //         'items.management_id as item_management_id',
            //         'items.name as item_name'
            //     )
            // ->orderBy('edithistories.created_at', 'desc')
            // ->get()
            // ->groupBy('date');
        $editHistories = EditHistory::with(['item', 'editReason'])
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
            ->get()
            ->groupBy('date');

        $editHistories = $editHistories->map(function ($histories) {
            return $histories->map(function ($history) {
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
                    case 'delete':
                        $history->operation_description = '廃棄';
                        break;
                    case 'restore':
                        $history->operation_description = '復元';
                        break;
                    default:
                        $history->operation_description = '不明な操作';
                        break;
                }

                return $history;
            });
        });


        // 空でも送れる
        return Inertia::render('Dashboard', [
            'allItems' => $items,
            'itemsByType' => $itemsByType,
            'edithistories' => $editHistories,
            'type' => $type
        ]); 

    }
}
