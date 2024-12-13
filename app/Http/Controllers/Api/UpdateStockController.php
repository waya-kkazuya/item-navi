<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;

class UpdateStockController extends Controller 
{ 
    public function getStock($itemId) 
    {
        $item = Item::findOrFail($itemId);
        
        return response()->json(['stock' => $item->stock]);
    }
}