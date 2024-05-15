<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $subQuery = Edithistory::betweenDate($request->startDate, $request->endDate)
        ->where('category_id', 1)
        ->where('edited_field', 'stocks')
        ->select('action_type', 'old_value', 'new_value','edited_at');

        // $data = DB::table($subQuery)
        // ->get();

        $data = DB::table($subQuery)
        ->select('action_type','old_value', 'new_value',
            DB::raw('CASE WHEN action_type = "入庫" THEN new_value - old_value ELSE 0 END as input'),
            DB::raw('CASE WHEN action_type = "出庫" THEN old_value - new_value ELSE 0 END as output'),
            'edited_at')
        ->orderBy('edited_at', 'desc')
        ->get();

        // LineChart用の昇順のデータ
        $forChart = DB::table($subQuery)
        ->select('new_value','edited_at')
        ->orderBy('edited_at', 'Asc')
        ->get();

        $labels = $forChart->pluck('edited_at');
        $stocks = $forChart->pluck('new_value');
        // reverse()では機能しない、->pluckは単なる配列ではない？
        // $labels = $data->pluck('edited_at')->reverse();
        // $stocks = $data->pluck('new_value')->reverse();


        // APIなのでJSON形式で返す
        return response()->json([
            'data' => $data,
            'labels' => $labels,
            'stocks' => $stocks,
        ], Response::HTTP_OK);
    }
}
