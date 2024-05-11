<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Services\ImageService;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // phpinfo();
        Log::info("sortDirection");
        Log::info($request->query('sortDirection', 'asc'));

        // $sortDirection = $request->query('sortDirection', 'asc');

        $items = Item::with('category')
        ->searchItems($request->query('search'))
        ->select(
            'id',
            'name',
            'category_id',
            'image_path1',
            'image_path2',
            'image_path3',
            'stocks',
            'usage_status',
            'end_user',
            'location_of_use',
            'storage_location',
            'acquisition_category',
            'price',
            'date_of_acquisition',
            'inspection_schedule',
            'disposal_schedule',
            'manufacturer',
            'product_number',
            'vendor',
            'vendor_website_url',
            'remarks',
            'qrcode_path'
        )->paginate(20);

        $items->map(function ($item) {
            // publicフォルダ内のパスから、シンボリックリンクでstorage/app/publicのデータを読み込む
            // $item->file_name = asset('storage/images/' . $item->file_name);
            // 'images'ではなく、'items'
            $item->image_path1 = asset('storage/items/' . $item->image_path1);
            $item->image_path2 = asset('storage/items/' . $item->image_path2);
            $item->image_path3 = asset('storage/items/' . $item->image_path3);
            return $item;
        });
        // ->orderBy('created_at', $sortDirection)
        
        // $items->transform(function ($item) {
        //     $item->image_path1 = asset('storage/' . $item->image_path1);
        //     return $item;
        // });

        // dd($items);

        // $items->getCollection()->transform(function ($item) {
        //     $item->image_path1 = asset('storage/items/' . $item->image_path1);
        //     return $item;
        // });

        return Inertia::render('Items/Index', [
            'items' => $items,
            // 'sort' => $sortDirection
        ]);
    }
    // 'imagePath1' => asset('storage/items/' . $item->)

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        // Gate::authorize('staff-higher');

        $categories = Category::all();

        return Inertia::render('Items/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
        Gate::authorize('staff-higher');

        // dd($request->image_path1);
        $imageFiles = $request->file('file_name');
        // dd($imageFiles);

        // $imageFile = $request->image_path1;
        // if(!is_null($imageFile) && $imageFile->isValid()){
        //     $created_image_path1 = Storage::putFile('public/items', $imageFile);
        // }
        // if(!is_null($imageFiles)){
        //     foreach($imageFiles as $imageFile){
        //         $fileNameToStore = ImageService::upload($imageFile, 'images');

        //         // ImageTest::create([
        //         //     'name' => $request->name,
        //         //     'file_name' => $fileNameToStore
        //         // ]);
        //     }
        // }

        $fileNameToStores = [];
        if(!is_null($imageFiles)){
            $fileNameToStores = array_map(function ($imageFile) {
                // ここで$imageFileを処理
                // $processedは処理後の結果
                $fileNameToStore = ImageService::upload($imageFile, 'items');
                // $processed = processImageFile($imageFile); // 仮の処理関数
                return $fileNameToStore;
            }, $imageFiles);
        }
        
        // dd($fileNameToStores, $fileNameToStores[0], $fileNameToStores[1], $fileNameToStores[2]);


        // dd($created_image_path1);

        Item::create([
            'id' => $request->id,
            'name' => $request->name,
            'category_id' => $request->category_id ,
            'image_path1' => $fileNameToStores[0] ?? null,
            'image_path2' => $fileNameToStores[1] ?? null,
            'image_path3' => $fileNameToStores[2] ?? null,
            'stocks' => $request->stocks ?? 0,
            'usage_status' => $request->usage_status,
            'end_user' => $request->end_user,
            'location_of_use' => $request->location_of_use,
            'storage_location' => $request->storage_location,
            'acquisition_category' => $request->acquisition_category,
            'price' => $request->price ?? 0,
            'date_of_acquisition' => $request->date_of_acquisition,
            'inspection_schedule' => $request->inspection_schedule,
            'disposal_schedule' => $request->disposal_schedule,
            'manufacturer' => $request->manufacturer,
            'product_number' => $request->product_number,
            'vendor' => $request->vendor,
            'vendor_website_url' => $request->vendor_website_url,
            'remarks' => $request->remarks,
            'qrcode_path' => $request->qrcode_path
        ]);

        return to_route('items.index')
        ->with([
            'message' => '登録しました。',
            'status' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        // dd($item);
        // categoryとのリレーションをロード
        $item_category = Item::with('category')->find($item->id);

        $item_category->image_path1 = asset('storage/items/' . $item_category->image_path1);
        $item_category->image_path2 = asset('storage/items/' . $item_category->image_path2);
        $item_category->image_path3 = asset('storage/items/' . $item_category->image_path3);    

        return Inertia::render('Items/Show', [
            'item' => $item_category
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        // Gate::authorize('staff-higher');

        // categoryとのリレーションをロード
        $item_category = Item::with('category')->find($item->id);
        $categories = Category::all();

        $item_category->image_path1 = asset('storage/items/' . $item_category->image_path1);
        $item_category->image_path2 = asset('storage/items/' . $item_category->image_path2);
        $item_category->image_path3 = asset('storage/items/' . $item_category->image_path3);    

        return Inertia::render('Items/Edit', [
            'item' => $item_category,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        // Gate::authorize('staff-higher');
        
        // dd($item->name, $request->name);
        $item->name = $request->name;
        $item->category_id = $request->category_id;
        $item->image_path1 = $request->image_path1;
        $item->image_path2 = $request->image_path2;
        $item->image_path3 = $request->image_path3;
        $item->usage_status = $request->usage_status;
        $item->end_user = $request->end_user;
        $item->location_of_use = $request->location_of_use;
        $item->acquisition_category = $request->acquisition_category;
        $item->price = $request->price;
        $item->date_of_acquisition = $request->date_of_acquisition;
        $item->inspection_schedule = $request->inspection_schedule;
        $item->disposal_schedule = $request->disposal_schedule;
        $item->manufacturer = $request->manufacturer;
        $item->product_number = $request->product_number;
        $item->vendor = $request->vendor;
        $item->vendor_website_url = $request->vendor_website_url;
        $item->remarks = $request->remarks;
        $item->qrcode_path = $request->qrcode_path;
        $item->save();

        return to_route('items.index')
        ->with([
            'message' => '更新しました。',
            'status' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return to_route('items.index')
        ->with([
            'message' => '削除しました。',
            'status' => 'danger'
        ]);
    }
}
