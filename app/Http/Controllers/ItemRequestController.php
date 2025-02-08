<?php

namespace App\Http\Controllers;

use App\Events\RequestedItemDetectEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequestRequest;
use App\Models\Category;
use App\Models\ItemRequest;
use App\Models\Location;
use App\Models\RequestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ItemRequestController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('user-higher');

        Log::info('ItemRequestController index method called');

        // 作成日でソートの値、初期値はasc
        $sortOrder = $request->query('sortOrder', 'asc');

        $withRelations = ['category', 'locationOfUse', 'requestStatus'];
        $selectFields  = [
            'id',
            'name',
            'category_id',
            'location_of_use_id',
            'requestor',
            'remarks_from_requestor',
            'request_status_id',
            'manufacturer',
            'reference',
            'price',
            'created_at',
        ];

        $query = ItemRequest::with($withRelations)
            ->select($selectFields)
            ->orderBy('created_at', $sortOrder);

        $total_count = $query->count();

        $item_requests = $query->paginate(10);

        $item_requests->getCollection()->transform(function ($item) {
            $item->formatted_created_at = $item->created_at->format('Y-m-d H:i:s');
            return $item;
        });

        $item_requests = $item_requests->setCollection($item_requests->getCollection());

        $request_statuses = RequestStatus::all();

        Log::info('ItemRequestController index method succeeded');

        return Inertia::render('ItemRequests/Index', [
            'itemRequests'    => $item_requests,
            'sortOrder'       => $sortOrder,
            'totalCount'      => $total_count,
            'requestStatuses' => $request_statuses,
        ]);
    }

    public function create()
    {
        Gate::authorize('user-higher');

        Log::info('ItemRequestController create method called');

        $categories = Category::all();
        $locations  = Location::all();

        Log::info('ItemRequestController create method succeeded');

        return Inertia::render('ItemRequests/Create', [
            'categories' => $categories,
            'locations'  => $locations,
        ]);
    }

    public function store(StoreItemRequestRequest $request)
    {
        Gate::authorize('user-higher');

        Log::info('ItemRequestController store method called');

        DB::beginTransaction();

        try {
            $itemRequest = ItemRequest::create([
                'name'                   => $request->name,
                'category_id'            => $request->category_id,
                'location_of_use_id'     => $request->location_of_use_id,
                'requestor'              => $request->requestor,
                'remarks_from_requestor' => $request->remarks_from_requestor,
                'request_status_id'      => 1,
                'manufacturer'           => $request->manufacturer,
                'reference'              => $request->reference,
                'price'                  => $request->price,
            ]);

            // リクエストが作成されたらイベントを発火
            event(new RequestedItemDetectEvent($itemRequest));

            DB::commit();

            Log::info('ItemRequestController store method succeeded');

            return to_route('item_requests.index')
                ->with([
                    'message' => 'リクエストしました。',
                    'status'  => 'success',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('ItemRequestController store method Transaction failed', [
                'error'   => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->back()
                ->with([
                    'message' => '登録中にエラーが発生しました',
                    'status'  => 'danger',
                ]);
        }
    }

    public function destroy(ItemRequest $itemRequest)
    {
        Gate::authorize('staff-higher');

        Log::info('ItemRequestController destroy method called');

        $itemRequest->delete();

        Log::info('ItemRequestController destroy method succeeded');

        return to_route('item_requests.index')
            ->with([
                'message' => 'リクエストを削除しました',
                'status'  => 'danger',
            ]);
    }
}
