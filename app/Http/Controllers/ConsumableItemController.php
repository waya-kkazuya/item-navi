<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Item;
use App\Models\Location;
use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;


class ConsumableItemController extends Controller
{
    const CONSUMABLE_ITEM_CATEGORY_ID = 1;

    public function index(Request $request)
    {
        Gate::authorize('staff-higher');

        $search = $request->query('search', '');

        // 作成日でソートの値、初期値はasc
        $sortOrder = $request->query('sortOrder', 'asc');

        // プルダウンの数値、第2引数は初期値で0
        // カテゴリのプルダウンは必要ない
        // $category_id = $request->query('category_id', 0);
        $location_of_use_id = $request->query('locationOfUseId', 0);
        $storage_location_id = $request->query('storageLocationId', 0);


        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod', 'inspections', 'disposal'];
        $selectFields = [
            'id',
            'management_id',
            'name',
            'category_id',
            'image1',
            'stock',
            'unit_id',
            'minimum_stock',
            'notification',
            'usage_status_id',
            'end_user',
            'location_of_use_id',
            'storage_location_id',
            'acquisition_method_id',
            'acquisition_source',
            'price',
            'date_of_acquisition',
            'manufacturer',
            'product_number',
            'remarks',
            'qrcode',
            'deleted_at',
            'created_at'
        ];

        // withによるeagerローディングではリレーションを使用する
        $query = Item::with($withRelations)
        ->where('category_id', self::CONSUMABLE_ITEM_CATEGORY_ID)
        ->searchItems($search)
        ->select($selectFields)
        ->orderBy('created_at', $sortOrder);

        // dd($query->get());

        if (Location::where('id', $location_of_use_id)->exists()) {
            $query->where('location_of_use_id', $location_of_use_id);
        }

        if (Location::where('id', $storage_location_id)->exists()) {
            $query->where('storage_location_id', $storage_location_id);
        }

        // 消耗品の合計件数
        $total_count = $query->count();

        $consumableItems = $query->paginate(20);


        // 表示する画像のパスを条件に応じてasset関数で生成、->image_path1に格納
        // map関数を使用するとpaginateオブジェクトの構造が変わり、ペジネーションが使えなくなる
        // コレクションを取得して変換
        $consumableItems->getCollection()->transform(function ($consumableItem) {
            // image1カラムがnullかチェック
            if(is_null($consumableItem->image1)) {
                $consumableItem->image_path1 = asset('storage/items/No_Image.jpg');
            } else {
                // image1の画像名のファイルが存在するかチェックする
                if(Storage::exists('public/items/' . $consumableItem->image1)) {
                    // 画像ファイルが存在する場合
                    $consumableItem->image_path1 = asset('storage/items/' . $consumableItem->image1);
                } else {
                    // 画像ファイルが存在しない場合
                    $consumableItem->image_path1 = asset('storage/items/No_Image.jpg');
                }
            }
            return $consumableItem;
        });

        // プルダウン用データ
        $locations = Location::all();

        $user = auth()->user();
        // dd($consumableItems);


        
        // dd('API');
        // APIのとき
        if ($request->has('reload')) {
            return [
                'items' => $consumableItems,
                'total_count' => $total_count
            ];
        }

        // Inertiaもajax通信なので、$request->ajax()では不十分
        return Inertia::render('ConsumableItems/Index', [
            'consumableItems' => $consumableItems,
            'locations' => $locations,
            'search' => $search,
            'sortOrder' => $sortOrder,
            'locationOfUseId' => $location_of_use_id,
            'storageLocationId' => $storage_location_id,
            'totalCount' => $total_count,
            'userName' => $user->name
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
