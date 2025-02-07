<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ItemRequestController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        Gate::authorize('staff-higher');

        Log::info('ItemRequestController updateStatus api method called');

        // $idはURLパラメータから取得される
        $itemRequest                    = ItemRequest::findOrFail($id);
        $itemRequest->request_status_id = $request->requestStatusId;
        $itemRequest->save();

        Log::info('ItemRequestController updateStatus api method succeeded');

        return response()->json(['message' => 'Status updated successfully'], 200);
    }
}
