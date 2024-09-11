<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ConsumableItemsController extends Controller
{
    public function history(Request $request)
    { 
        Log::info('api.history');

        if($request->type === 'month'){
            // 今日の日付
            $endDate = Carbon::today();
            // 1ヵ月前の日付
            $startDate = Carbon::today()->subMonth();
        } else {
            // 今日の日付
            $endDate = Carbon::today();
            // 1週間前の日付
            $startDate = Carbon::today()->subWeek();
        }


        // 消耗品(category_id'が1)かつstocks
        $subQuery = Edithistory::betweenDate($startDate, $endDate)
        ->where('category_id', 1)
        ->where('item_id', $request->id)
        ->where('edited_field', 'stocks')
        ->select('action_type', 'old_value', 'new_value','edited_at');

        $data = DB::table($subQuery)
        ->select('action_type','old_value', 'new_value',
            DB::raw('CASE WHEN action_type = "入庫" THEN new_value - old_value ELSE 0 END as input'),
            DB::raw('CASE WHEN action_type = "出庫" THEN old_value - new_value ELSE 0 END as output'),
            'edited_at')
        ->orderBy('edited_at', 'desc')
        ->get();

        // LineChart用の昇順のデータ
        // reverse()では機能しないので、orderByで昇順に並べる
        $forChart = DB::table($subQuery)
        ->select('new_value','edited_at')
        ->orderBy('edited_at', 'Asc')
        ->get();

        $labels = $forChart->pluck('edited_at');
        $stocks = $forChart->pluck('new_value');

        // APIなのでJSON形式で返す
        return response()->json([
            'data' => $data,
            'labels' => $labels,
            'stocks' => $stocks,
        ], Response::HTTP_OK);
    }
}
