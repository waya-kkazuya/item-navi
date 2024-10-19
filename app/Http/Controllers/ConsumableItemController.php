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
use App\Services\ImageService;


class ConsumableItemController extends Controller
{
    const CONSUMABLE_ITEM_CATEGORY_ID = 1;
    protected $imageService;

    public function __construct(ImageService $imageService) {
        $this->imageService = $imageService;
    }

    public function index(Request $request, $item_id = null)
    {
        Gate::authorize('user-higher');

        try {
            $search = $request->query('search', '');

            $sortOrder = $request->query('sortOrder', 'desc');

            // プルダウンの数値、第2引数は初期値で0
            $location_of_use_id = $request->query('locationOfUseId', 0);
            $storage_location_id = $request->query('storageLocationId', 0);

            $withRelations = [
                'category', 
                'unit', 
                'usageStatus', 
                'locationOfUse', 
                'storageLocation', 
                'acquisitionMethod', 
                'inspections', 
                'disposal'
            ];
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

            if (Location::where('id', $location_of_use_id)->exists()) {
                $query->where('location_of_use_id', $location_of_use_id);
            }

            if (Location::where('id', $storage_location_id)->exists()) {
                $query->where('storage_location_id', $storage_location_id);
            }

            // 消耗品の合計件数
            $total_count = $query->count();
            $consumableItems = $query->paginate(20);

            // map関数を使用するとpaginateオブジェクトの構造が変わり、ペジネーションが使えなくなる
            $consumableItems->getCollection()->transform(function ($consumableItem) {
                $this->imageService->setImagePathToObject($consumableItem);
                return $consumableItem;
            });

            // プルダウン用データ
            $locations = Location::all();
            $user = auth()->user();

            // Notification.vueのリンククリックで送られてきたItemのid
            $linkedItem = Item::with($withRelations)
                ->select($selectFields)
                ->find($item_id);

            // 入出庫モーダルで処理をした後、在庫数を更新するAPI通信
            if ($request->has('reload')) {
                return [
                    'items' => $consumableItems,
                    'total_count' => $total_count
                ];
            }

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
        } catch (\Exception $e) {
            Log::error('エラーが発生しました: ' . $e->getMessage());
        }
    }
}
