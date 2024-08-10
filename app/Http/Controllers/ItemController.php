<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Inspection;
use App\Models\UsageStatus;
use App\Models\AcquisitionMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
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

    public function index(Request $request)
    {  
        Gate::authorize('staff-higher');

        $search = $request->query('search', '');

        // 作成日でソートの値、初期値はasc
        $sortOrder = $request->query('sortOrder', 'asc');

        // プルダウンの数値、第2引数は初期値で0
        $category_id = $request->query('category_id', 0);
        $location_of_use_id = $request->query('location_of_use_id', 0);
        $storage_location_id = $request->query('storage_location_id', 0);
        // Log::info("location_of_use_id");


        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod'];
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
            // 'inspection_schedule',
            // 'disposal_schedule',
            'remarks',
            'qrcode',
            'deleted_at',
            'created_at'
        ];
    
        Log::info("$request->has('disposal')");
        Log::info($request->has('disposal'));
        Log::info("$request->disposal");
        Log::info($request->disposal);

        // 明示的に厳密にイコールにしたらできた
        if ($request->disposal === 'true') {
            $query = Item::onlyTrashed();
        } else {
            $query = Item::whereNull('deleted_at');
        }


        // withによるeagerローディングではリレーションを使用する
        $query = $query->with($withRelations)
        ->searchItems($search)
        ->select($selectFields)
        ->orderBy('created_at', $sortOrder);


        


        

        // // ブラウザから直接値を入力されることを考慮して事前に対策する

        // フィルター部分はFatコントローラー防止で、分離できる
        // マジックナンバーを使わない

        // テスト：カラムに存在しない、idでクエリを実行すると動作がおかしくなる
        // $query->where('location_of_use_id', 99);

        // DBに設定されているidの時のみ反映
        // 各プルダウン変更時のクエリ、ローカルスコープに切り出しリファクタリング
        if (Category::where('id', $category_id)->exists()) {
            $query->where('category_id', $category_id);
        }

        if (Location::where('id', $location_of_use_id)->exists()) {
            $query->where('location_of_use_id', $location_of_use_id);
        }

        if (Location::where('id', $storage_location_id)->exists()) {
            $query->where('storage_location_id', $storage_location_id);
        }


        $total_count = $query->count();


        // paginateじゃなくget()の時のデータ構造解析
        // dd($query->get());

        $items = $query->paginate(20);

        // // map関数を使用するとpaginateオブジェクトの構造が変わり、ペジネーションが使えなくなる
        // コレクションを取得して変換
        $items->getCollection()->transform(function ($item) {
            // image1カラムがnullかチェック
            if(is_null($item->image1)) {
                $item->image_path1 = asset('storage/items/No_Image.jpg');
            } else {
                // image1の画像名のファイルが存在するかチェックする
                if(Storage::exists('public/items/' . $item->image1)) {
                    // 画像ファイルが存在する場合
                    $item->image_path1 = asset('storage/items/' . $item->image1);
                } else {
                    // 画像ファイルが存在しない場合
                    $item->image_path1 = asset('storage/items/No_Image.jpg');
                }
            }
            return $item;
        });

        // 変換後のコレクションを元のpaginateオブジェクトに戻す
        $items = $items->setCollection($items->getCollection());

        // dd($items);

        // プルダウン用データ
        $categories = Category::all();
        $locations = Location::all();

        // itemsテーブルで使用しているidのみ抽出
        // locationsを加工し、利用場所用の使用されているloactionsデータ
        // 保管場所の中で使用されているlocationsデータをVueファイルに渡してプルダウンに反映する
        $locationOfUseIds = Item::distinct()->pluck('location_of_use_id');
        $locationsOfUse = Location::whereIn('id', $locationOfUseIds)->get();
        // dd($locationsOfUse);

        $storageLocationIds = Item::distinct()->pluck('storage_location_id');
        $storageOfLocation = Location::whereIn('id', $storageLocationIds)->get();


        // 要完了
        if ($request->has('disposal')) {
            return $items;
        }

        
        return Inertia::render('Items/Index', [
            'items' => $items,
            'categories' => $categories,
            'locations' => $locations,
            'search' => $search,
            'sortOrder' => $sortOrder,
            'categoryId' => $category_id,
            'locationOfUseId' => $location_of_use_id,
            'storageLocationId' => $storage_location_id,
            'totalCount' => $total_count
        ]);


        
    }




    public function create()
    {   
        Gate::authorize('staff-higher');

        $categories = Category::all();
        $locations = Location::all();
        $units = Unit::all();
        $usage_statuses = UsageStatus::all();
        $acquisition_methods = AcquisitionMethod::all();

        return Inertia::render('Items/Create', [
            'categories' => $categories,
            'locations' => $locations,
            'units' => $units,
            'usageStatuses' => $usage_statuses,
            'acquisitionMethods' => $acquisition_methods,
        ]);
    }

    

    public function store(StoreItemRequest $request)
    {
        Gate::authorize('staff-higher');

        // $imageFiles = $request->file('file_name');　//複数ファイルの際の処理

        // $imageFile = $request->image_path1;
        // if(!is_null($imageFile) && $imageFile->isValid()){
        //     $created_image_path1 = Storage::putFile('public/items', $imageFile);
        // }
        // if(!is_null($imageFiles)){
        //     foreach($imageFiles as $imageFile){
        //         $fileNameToStore = ImageService::upload($imageFile, 'images');
        //     }
        // }

        // $fileNameToStores = [];
        // if(!is_null($imageFiles)){
        //     $fileNameToStores = array_map(function ($imageFile) {
        //         // ここで$imageFileを処理
        //         // $processedは処理後の結果
        //         $fileNameToStore = ImageService::upload($imageFile, 'items');
        //         // $processed = processImageFile($imageFile); // 仮の処理関数
        //         return $fileNameToStore;
        //     }, $imageFiles);
        // }


        
        // dd($fileNameToStores, $fileNameToStores[0], $fileNameToStores[1], $fileNameToStores[2]);
        // QRコード生成、サービスに切り分け
        $qrcodeName = 'QR' . uniqid(rand().'_') . '.png';
        QrCode::format('png')->size(200)->generate('Hello Laravel!', storage_path('app/public/qrcode/' . $qrcodeName));



        // もしもカテゴリが消耗品以外で、minimumに数値が入っていたらnullにする
        // categoriesテーブルで消耗品のidは2
        if($request->category_id == 2){
            $minimum_stock = $request->minimum_stock;
        } else {
            $minimum_stock = null;
        }

    
        
        DB::beginTransaction();

        try{
            // 保存したオブジェクトを変数に入れてInspectionのcreateに使用する
            $item = Item::create([
                'id' => $request->id,
                'name' => $request->name,
                'category_id' => $request->category_id ,
                'image1' => $fileNameToStore ?? null,
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
            
            DB::commit(); // ここで確定

            return to_route('items.index')
            ->with([
                'message' => '登録しました。',
                'status' => 'success'
            ]);

        }catch(ValidationException $e){
            DB::rollBack();
            // 保存した画像の削除処理、取り消し
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }catch(\Exception $e){
            DB::rollBack();
            // 保存した画像の削除処理、取り消し
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            return redirect()->back()
            ->with('error', '登録中にエラーが発生しました')->withInput();
        }
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

    public function forceDelete($id)
    {
        
    }

    public function disposedItemIndex(){
        $disposedItems = Item::onlyTrashed()->get();
        
        return Inertia::render('Items/Index', [
            'disposedItems' => $disposedItems,
        ]);
    }
}
