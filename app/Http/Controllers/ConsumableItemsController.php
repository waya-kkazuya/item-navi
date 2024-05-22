<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Item;
use App\Models\Edithistory;


class ConsumableItemsController extends Controller
{
    public function index()
    {
        $consumableItems = Item::with('category')
        ->where('category_id', 1)
        ->select(
            'id',
            'name',
            'category_id',
            'image_path1',
            'image_path2',
            'image_path3',
            'stocks',
            'usage_status',
            'end_user',
            'location_of_use',
            'storage_location',
            'acquisition_category',
            'price',
            'date_of_acquisition',
            'inspection_schedule',
            'disposal_schedule',
            'manufacturer',
            'product_number',
            'vendor',
            'vendor_website_url',
            'remarks',
            'qrcode_path'
        )->paginate(20);

        $consumableItems->map(function ($item) {
            // publicフォルダ内のパスから、シンボリックリンクでstorage/app/publicのデータを読み込む
            // $item->file_name = asset('storage/images/' . $item->file_name);
            $item->image_path1 = asset('storage/items/' . $item->image_path1);
            $item->image_path2 = asset('storage/items/' . $item->image_path2);
            $item->image_path3 = asset('storage/items/' . $item->image_path3);
            return $item;
        });

        return Inertia::render('ConsumableItems/Index', [
            'consumableItems' => $consumableItems,
        ]);
    }


    // 在庫数遷移用メソッド
    public function history($id)
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

        // このままではAPI

        return Inertia::render('ConsumableItems/History', [

        ]);
    }
}
