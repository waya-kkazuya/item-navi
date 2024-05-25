<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Item;
use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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


    // 在庫数遷移画面用メソッド
    // 最初の当日から1週間前までの
    public function history($id)
    {
        $item = Item::findOrFail($id);
        $item->image_path1 = asset('storage/items/' . $item->image_path1);
        $item->image_path2 = asset('storage/items/' . $item->image_path2);
        $item->image_path3 = asset('storage/items/' . $item->image_path3);

        // 今日の日付
        $endDate = Carbon::today();
        // 1週間前の日付
        $startDate = Carbon::today()->subWeek();

        $subQuery = Edithistory::betweenDate($startDate, $endDate)
        ->where('category_id', 1)
        ->where('item_id', $id)
        ->where('edited_field', 'stocks')
        ->select('action_type', 'old_value', 'new_value','edited_at');


        // 入庫と出庫の場合でそれぞれ在庫数の差を取得している
        $data = DB::table($subQuery)
        ->select('action_type','old_value', 'new_value',
            DB::raw('CASE WHEN action_type = "入庫" THEN new_value - old_value ELSE 0 END as input'),
            DB::raw('CASE WHEN action_type = "出庫" THEN old_value - new_value ELSE 0 END as output'),
            'edited_at')
        ->orderBy('edited_at', 'desc')
        ->get();

        // LineChart用の昇順のデータ
        // reverse()では機能しないので、orderByで昇順に並べる
        // 'new_value'はその時に確定した在庫数
        $forChart = DB::table($subQuery)
        ->select('new_value','edited_at')
        ->orderBy('edited_at', 'Asc')
        ->get();

        $labels = $forChart->pluck('edited_at');
        $stocks = $forChart->pluck('new_value');

        // dd($data);

        return Inertia::render('ConsumableItems/History', [
            'data' => $data,
            'labels' => $labels,
            'stocks' => $stocks,
            'item' => $item
        ]);
    }
}
