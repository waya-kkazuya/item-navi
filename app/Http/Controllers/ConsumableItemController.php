<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Location;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ConsumableItemController extends Controller
{
    const CONSUMABLE_ITEM_CATEGORY_ID = 1;
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request, $item_id = null)
    {
        Gate::authorize('user-higher');

        Log::info('ConsumableItemController index method called');

        try {
            $search    = $request->query('search', '');
            $sortOrder = $request->query('sortOrder', 'desc');
            // プルダウンの数値、第2引数は初期値で0
            $location_of_use_id  = $request->query('locationOfUseId', 0);
            $storage_location_id = $request->query('storageLocationId', 0);

            $withRelations = [
                'category',
                'unit',
                'usageStatus',
                'locationOfUse',
                'storageLocation',
                'acquisitionMethod',
                'inspections',
                'disposal',
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
                'created_at',
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
            $total_count     = $query->count();
            $consumableItems = $query->paginate(20);

            $current_page = $consumableItems->currentPage();                                  // 現在のページ番号
            $per_page     = $consumableItems->perPage();                                      // 1ページあたりの項目数
            $start_number = ($current_page - 1) * $per_page + 1;                              // 現在のページの最初の項目番号
            $end_number   = min($start_number + $consumableItems->count() - 1, $total_count); // 現在のページの最後の項目番号

            // map関数を使用するとpaginateオブジェクトの構造が変わり、ペジネーションが使えなくなるのでtransformを使う
            $consumableItems->getCollection()->transform(function ($consumableItem) {
                $this->imageService->setImagePathToObject($consumableItem);
                return $consumableItem;
            });

            // プルダウン用データ
            $locations = Location::all();
            $user      = auth()->user();

            // Notification.vueのリンククリックで送られてきたItemのid
            $linkedItem = Item::with($withRelations)
                ->select($selectFields)
                ->find($item_id);

            Log::info('ConsumableItemController index method succeeded');

            return Inertia::render('ConsumableItems/Index', [
                'consumableItems'   => $consumableItems,
                'locations'         => $locations,
                'search'            => $search,
                'sortOrder'         => $sortOrder,
                'locationOfUseId'   => $location_of_use_id,
                'storageLocationId' => $storage_location_id,
                'totalCount'        => $total_count,
                'userName'          => $user->name,
                'linkedItem'        => $linkedItem,
                'startNumber'       => $start_number,
                'endNumber'         => $end_number,
            ]);
        } catch (\Exception $e) {
            Log::error('ConsumableItemController index method failed', [
                'error'   => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
        }
    }
}
