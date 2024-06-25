<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Inspection;
use Inertia\Inertia;
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Services\ImageService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;


class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {        
        $search = $request->query('search', '');

        // 作成日でソートの値、初期値はasc
        $sortOrder = $request->query('sortOrder', 'asc');

        // プルダウンの数値、第2引数は初期値で0
        $category_id = $request->query('category_id', 0);
        $location_of_use_id = $request->query('location_of_use_id', 0);
        // Log::info("location_of_use_id");
        // Log::info($location_of_use_id);
        // dd($location_of_use_id);

        $storage_location_id = $request->query('storage_location_id', 0);

        // withによるeagerローディングではリレーションを使用する
        $query = Item::with(['category',  'locationOfUse', 'storageLocation'])
        ->searchItems($search)
        ->select(
            'id',
            'name',
            'category_id',
            'image_path1',
            'image_path2',
            'image_path3',
            'stocks',
            'minimum_stock',
            'usage_status',
            'end_user',
            'location_of_use_id',
            'storage_location_id',
            'acquisition_category',
            'where_to_buy',
            'price',
            'date_of_acquisition',
            'inspection_schedule',
            'disposal_schedule',
            'manufacturer',
            'product_number',
            // 'vendor_website_url',
            'remarks',
            'qrcode_path',
            'created_at'
        )->orderBy('created_at', $sortOrder);


        // // ブラウザから直接値を入力されることを考慮して事前に対策する

        // フィルター部分はFatコントローラー防止で、分離できる
        // カテゴリでフィルター
        // カテゴリIDで0以外が指定されている場合、そのカテゴリのアイテムだけを取得
        // DBに設定されているidしか入力できないようif文に条件追加
        if ($category_id > 0 && $category_id <= Category::max('id')) {
            $query->where('category_id', $category_id);
        }

        
        // Location::max(id)を使用して、Locationsテーブルに存在するidを指定する
        if ($location_of_use_id > 0 && $location_of_use_id <= Location::max('id')) {
            $query->where('location_of_use_id', $location_of_use_id);
        }

        // カラムに存在しない、idでクエリを実行すると動作がおかしくなる
        // $query->where('location_of_use_id', 99);


        // // 保管場所でフィルター
        // // 保管場所すべてでvalue=0(利用場所すべて)以外が指定されている場合、その利用場所のItemだけを取得
        // // 念のため  < location::count()のようにするべきか
        if ($storage_location_id > 0 && $storage_location_id <= Location::max('id')) {
            $query->where('storage_location_id', $storage_location_id);
        }


        // ペジネーション
        $items = $query->paginate(20);

        // 画像3つを変換
        $items->map(function ($item) {
            // publicフォルダ内のパスから、シンボリックリンクでstorage/app/publicのデータを読み込む
            // $item->file_name = asset('storage/images/' . $item->file_name);
            // 'images'ではなく、'items'
            $item->image_path1 = asset('storage/items/' . $item->image_path1);
            $item->image_path2 = asset('storage/items/' . $item->image_path2);
            $item->image_path3 = asset('storage/items/' . $item->image_path3);

            return $item;
        });


        // プルダウン用データ
        $categories = Category::all();
        $locations = Location::all();

        // itemsテーブルで使用しているidのみ抽出
        $locationOfUseIds = Item::distinct()->pluck('location_of_use_id');
        $storageLocationIds = Item::distinct()->pluck('storage_location_id');
        
        // dd($locationOfUseIds);
        // dd($storageLocationIds);
        // dd($locations);

        

        return Inertia::render('Items/Index', [
            'items' => $items,
            'categories' => $categories,
            'locations' => $locations,
            'search' => $search,
            'sortOrder' => $sortOrder,
            'category_id' => $category_id,
            'location_of_use_id' => $location_of_use_id,
            'storage_location_id' => $storage_location_id,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        // Gate::authorize('staff-higher');

        $categories = Category::all();
        $locations = Location::all();


        return Inertia::render('Items/Create', [
            'categories' => $categories,
            'locations' => $locations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
        Gate::authorize('staff-higher');

        $imageFiles = $request->file('file_name');

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
        // QRコード生成、サービスに切り分け
        $qrcodeName = 'QR' . uniqid(rand().'_') . '.png';
        QrCode::format('png')->size(200)->generate('Hello Laravel!', storage_path('app/public/qrcode/' . $qrcodeName));

        // dd($request->location_of_use_id);

        // もしもカテゴリが消耗品以外で、minimumに数値が入っていたらnullにする
        // categoriesテーブルで消耗品のidは2
        if($request->category_id == 2){
            $minimum_stock = $request->minimum_stock;
        } else {
            $minimum_stock = null;
        }

        // DB::beginTransaction();

        // try{
        //     // ここに処理を書く
            
        //     DB::commit(); // ここで確定

        //     return to_route('items.index')
        //     ->with([
        //         'message' => '登録しました。',
        //         'status' => 'success'
        //     ]);

        // }catch(\Exception $e){
        //     DB::rollBack();
        // }



        // 保存したオブジェクトを変数に入れてInspectionのcreateに使用する
        $item = Item::create([
            'id' => $request->id,
            'name' => $request->name,
            'category_id' => $request->category_id ,
            'image_path1' => $fileNameToStores[0] ?? null,
            'image_path2' => $fileNameToStores[1] ?? null,
            'image_path3' => $fileNameToStores[2] ?? null,
            'stocks' => $request->stocks ?? 0,
            'minimum_stock' => $minimum_stock,
            'usage_status' => $request->usage_status,
            'end_user' => $request->end_user,
            'location_of_use_id' => $request->location_of_use_id,
            'storage_location_id' => $request->storage_location_id,
            'acquisition_category' => $request->acquisition_category,
            'where_to_buy' => $request->where_to_buy,
            'price' => $request->price ?? 0,
            'date_of_acquisition' => $request->date_of_acquisition,
            'inspection_schedule' => $request->inspection_schedule,
            'disposal_schedule' => $request->disposal_schedule,
            'manufacturer' => $request->manufacturer,
            'product_number' => $request->product_number,
            'remarks' => $request->remarks,
            'qrcode_path' => $qrcodeName
        ]);


        // ここにも条件分岐、点検が必要な備品のカテゴリのときのみ保存する
        Inspection::create([
            'item_id' => $item->id,
            'scheduled_date' => $request->inspection_schedule,
            'inspection_date' => null, // migrationでnullableにする
            'inspection_person' => '', // 空白は保存できるのか,nullとの違い
            'status' => false, // 未実施がfalse
            'next_scheduled_date' => null,
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
        $item_category_location = Item::with(['category',  'locationOfUse', 'storageLocation'])->find($item->id);  

        return Inertia::render('Items/Show', [
            'item' => $item_category_location
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
        $locations = Location::all();

        // $item_category->image_path1 = asset('storage/items/' . $item_category->image_path1);
        // $item_category->image_path2 = asset('storage/items/' . $item_category->image_path2);
        // $item_category->image_path3 = asset('storage/items/' . $item_category->image_path3);    

        return Inertia::render('Items/Edit', [
            'item' => $item_category,
            'categories' => $categories,
            'locations' => $locations
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
        $item->minimum_stock = $request->minimum_stock;
        $item->usage_status = $request->usage_status;
        $item->end_user = $request->end_user;
        $item->location_of_use_id = $request->location_of_use_id;
        $item->storage_location_id = $request->storage_location_id;
        $item->acquisition_category = $request->acquisition_category;
        $item->where_to_buy = $request->where_to_buy;
        $item->price = $request->price;
        $item->date_of_acquisition = $request->date_of_acquisition;
        $item->inspection_schedule = $request->inspection_schedule;
        $item->disposal_schedule = $request->disposal_schedule;
        $item->manufacturer = $request->manufacturer;
        $item->product_number = $request->product_number;
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
