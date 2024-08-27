<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\ItemRequest;
use App\Models\Category;
use App\Models\Location;
use Inertia\Inertia;

class ItemRequestController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('staff-higher');

        // 作成日でソートの値、初期値はasc
        $sortOrder = $request->query('sortOrder', 'asc');

        $withRelations = ['category', 'locationOfUse'];
        $selectFields = [
            'id',
            'name',
            'category_id',
            'location_of_use_id',
            'requestor',
            'remarks_from_requestor',
            'request_status',
            'manufacturer',
            'reference', //カラム要追加
            'price',
            'created_at'
        ];

        $query = ItemRequest::with($withRelations)
        ->select($selectFields)
        ->orderBy('created_at', $sortOrder);

        $total_count = $query->count();

        $item_requests = $query->paginate(10);

        return Inertia::render('ItemRequests/Index', [
            'itemRequests' => $item_requests,
            'sortOrder' => $sortOrder,
            'totalCount' => $total_count
        ]); 
    }

    public function create()
    {
        Gate::authorize('staff-higher');
        
        $categories = Category::all();
        $locations = Location::all();

        return Inertia::render('ItemRequests/Create', [
            'categories' => $categories,
            'locations' => $locations,
        ]);
    }

    public function store(StoreItemRequestRequest $request)
    {
        Gate::authorize('staff-higher');



    }
}
