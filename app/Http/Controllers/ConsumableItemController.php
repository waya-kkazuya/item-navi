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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ConsumableItemController extends Controller
{
    const CONSUMABLE_ITEM_CATEGORY_ID = 1;

    public function index(Request $request, $item_id = null)
    {
        Gate::authorize('user-higher');

        Log::info('$item_id');
        Log::info($item_id);

        $search = $request->query('search', '');

        // 作成日でソートの値、初期値はdesc
        $sortOrder = $request->query('sortOrder', 'desc');

        // プルダウンの数値、第2引数は初期値で0
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
                if(Storage::disk('public')->exists('items/' . $consumableItem->image1)) {
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

        // Notification.vueのリンククリックで送られてきたItemのid
        $linkedItem = Item::find($item_id);
        Log::info('$linkedItem');
        Log::info($linkedItem);
        // dd($linkedItem);

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
            'userName' => $user->name,
            'linkedItem' => $linkedItem
        ]);
    }
}
