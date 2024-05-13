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

        $data = DB::table($subQuery)
        ->get();

        // APIなのでJSON形式で返す
        return response()->json([
            'data' => $data
        ], Response::HTTP_OK);
    }
}
