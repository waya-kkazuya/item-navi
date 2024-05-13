<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;


class AnalysisController extends Controller
{
    public function index()
    {   
        $startDate = '2022-05-09';
        $endDate = '2024-05-12';

        // $subQuery = Edithistory::betweenDate($startDate, $endDate)
        //     ->select('edited_at', DB::raw('GROUP_CONCAT(edited_field SEPARATOR ", ") as edited_fields'))
        //     ->groupBy('edited_at')
        //     ->orderBy('edited_at')
        //     ->paginate(10);

        // $subQuery = Edithistory::betweenDate($startDate, $endDate)->get();
        // dd($subQuery);

        // $subQuery = Edithistory::betweenDate($startDate, $endDate)
        //     ->select('edited_at', DB::raw('GROUP_CONCAT(edited_field SEPARATOR ", ") as edited_fields'))
        //     ->groupBy('edited
        // 消耗品category_id=1のレコードを取ってくる
        $subQuery = Edithistory::betweenDate($startDate, $endDate)
        ->where('category_id', 1)
        ->where('edited_field', 'stocks')
        ->select('action_type', 'old_value', 'new_value','edited_at');

        // dd($subQuery);
        // サブクエリで作ったものをさらに整える
        $data = DB::table($subQuery)
        ->get();

        // dd($data);

        // dd($data);
        // $records = DB::table('your_table_name')
        // ->select('updated_at', DB::raw('GROUP_CONCAT(field SEPARATOR ", ") as fields'))
        // ->groupBy('updated_at')
        // ->get();
            
                
                // ->selectRawでSQL分を書ける

        return Inertia::render('Analysis');
    }
}
