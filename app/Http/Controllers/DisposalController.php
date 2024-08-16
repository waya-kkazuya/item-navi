<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisposalRequest;
use App\Models\Disposal;
use App\Models\Item;
use Illuminate\Http\Request;

class DisposalController extends Controller
{
    public function save(StoreDisposalRequest $request, Item $item)
    {
        // $validatedData = $request->validate([
        //     'itemName' => 'required|string|max:255',
        //     'quantity' => 'required|integer|min:1',
        //     'reason' => 'required|string|max:255',
        // ]);

        // dd($disposal);
        dd($item);
        dd($item->id);
        dd($request->disposalDate);
        $disposal = Disposal::find($item->id);

        // 対象の備品と関連のあるDisposalのレコードに保存
        $disposal->disposal_date = $request->disposalDate;
        $disposal->disposal_person = $request->disposalPerson;
        $disposal->details = $request->details;
        $disposal->save();


        // ソフトデリート

        return to_route('items.show')
        ->with([
            'message' => '更新しました。',
            'status' => 'success'
        ]);
    }
}
