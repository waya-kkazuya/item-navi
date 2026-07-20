<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ItemRequestController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        Gate::authorize('staff-higher');

        // $idはURLパラメータから取得される
        $itemRequest = ItemRequest::findOrFail($id);
        $itemRequest->request_status_id = $request->requestStatusId;
        $itemRequest->save();

        return response()->json(['message' => 'Status updated successfully'], 200);
    }
}
