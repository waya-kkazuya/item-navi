<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Inspection;
use App\Models\Disposal;
use App\Models\UsageStatus;
use App\Models\AcquisitionMethod;
use App\Models\EditReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Requests\CombinedRequest;
use Illuminate\Http\Request;    
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Carbon\Carbon;
use App\Services\ImageService;

class ItemController extends Controller
{

    public function index(Request $request)
    {  
        // dd(phpinfo());

        Gate::authorize('staff-higher');

        $search = $request->query('search', '');
        
        // 作成日でソートの値、初期値はasc
        $sortOrder = $request->query('sortOrder', 'asc');

        // プルダウンの数値、第2引数は初期値で0
        $category_id = $request->query('categoryId', 0);
        $location_of_use_id = $request->query('locationOfUseId', 0);
        $storage_location_id = $request->query('storageLocationId', 0);
        // Log::info("location_of_use_id");

        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod', 'inspections', 'disposal'];
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
            'created_at'
        ];
    
        Log::info("$request->has('disposal')");
        Log::info($request->has('disposal'));
        Log::info("$request->disposal");
        Log::info($request->disposal);

        // 通常の備品か廃棄済みの備品かの分岐
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

        // 点検予定日のレコードを抽出、1対多のテーブルゆえ
        // $query->get()->map(function ($item) {
        //     $item->pending_inspection_date = $item->inspections->where('status', false)->sortBy('scheduled_date')->first()->scheduled_date ?? null;
        //     return $item;
        // });

        // 備品の合計件数
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

            // pending_inspection_dateの設定
            $item->pending_inspection_date = $item->inspections->where('status', false)->sortBy('scheduled_date')->first()->scheduled_date ?? null;

            return $item;
        });

        // 変換後のコレクションを元のpaginateオブジェクトに戻す
        $items = $items->setCollection($items->getCollection());

        // dd($items);

        // プルダウン用データ
        $categories = Category::all();
        $locations = Location::all();


        // 未使用
        // itemsテーブルで使用しているidのみ抽出してユーザビリティを上げる
        // locationsを加工し、利用場所用の使用されているloactionsデータ
        // 保管場所の中で使用されているlocationsデータをVueファイルに渡してプルダウンに反映する
        $locationOfUseIds = Item::distinct()->pluck('location_of_use_id');
        $locationsOfUse = Location::whereIn('id', $locationOfUseIds)->get();
        // dd($locationsOfUse);

        $storageLocationIds = Item::distinct()->pluck('storage_location_id');
        $storageOfLocation = Location::whereIn('id', $storageLocationIds)->get();

        // dd($items);

        // 廃棄済み備品用API情報
        if ($request->has('disposal')) {
            return [
                'items' => $items,
                'total_count' => $total_count
            ];
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
        

        // 画像保存もトランザクション処理内に入れるかどうか
        // DB::beginTransaction();

        // try{


            // 画像アップロード
            $imageFile = $request->imageFile; // 一時保存
            // ->isValid()は念のため、ちゃんとアップロードできているかチェックしてくれる
            $fileNameToStore = null;
            if(!is_null($imageFile) && $imageFile->isValid() ){
                $fileNameToStore = ImageService::resizeUpload($imageFile);
            }


            // // QRコード生成、QrCodeServiceに切り分ける
            // // ※消耗品のときだけcategory_id=1のときだけ生成する
            // if($item->category_id == 1){
            //     // QrCode::format('png')->size(200)->generate('Hello Laravel!', storage_path('app/public/qrcode/' . $qrcodeName));
            //     // png生成にはImagickが必要
            //     $qrCode = QrCode::format('png')->size(200)->generate('Hello Laravel!');
            //     $qrManager = new ImageManager(new Driver());
            //     $qrImage = $qrManager->read($qrCode)->resize(30, 30);

            //     $label = $qrManager->create(91, 55)->fill('fff');
            //     $label->place(
            //         $qrImage,
            //         'top-left', 
            //         15, 
            //         15,
            //     );
            //     $label->text('管理ID ' . $item->management_id, 50, 15, function($font) {
            //         $font->size(12);
            //         $font->color('#000');
            //     });
            //     $label->text('備品名 ' . $item->name, 50, 30, function($font) {
            //         $font->size(12);
            //         $font->color('#000');
            //     });
            //     $label->text('備品カテゴリ ' . $item->category->name, 50, 45, function($font) {
            //         $font->size(12);
            //         $font->color('#000');
            //     });


            //     $labelName = $item->id . '_label.jpg';
            //     Storage::put('labels/' . $labelName, $label->encodeByExtension('jpg'));
            //     // 画像データをjpegへエンコードする

            //     // 画像をStorage/public/qrcodesに保存する
            //     // return $qrCodeNameToStore;
            // }


        // もしもカテゴリが消耗品以外で、minimumに数値が入っていたらnullにする
        // categoriesテーブルで消耗品のidは1、定数に入れる
        if($request->categoryId == 1){
            $minimum_stock = $request->minimumStock;
        } else {
            $minimum_stock = null;
        }




            // 保存したオブジェクトを変数に入れてInspectionのcreateに使用する
            $item = Item::create([
                'id' => $request->id,
                'name' => $request->name,
                'category_id' => $request->categoryId ,
                'image1' => $fileNameToStore ?: null,
                'stock' => $request->stock ?? 0,
                'unit_id' => $request->unitId,
                'minimum_stock' => $request->minimumStock,
                'notification' => $request->notification,
                'usage_status_id' => $request->usageStatusId,
                'end_user' => $request->endUser ?: null,
                'location_of_use_id' => $request->locationOfUseId,
                'storage_location_id' => $request->storageLocationId,
                'acquisition_method_id' => $request->acquisitionMethodId,
                'acquisition_source' => $request->acquisitionSource ?: null,
                'price' => $request->price,
                'date_of_acquisition' => $request->dateOfAcquisition,
                'manufacturer' => $request->manufacturer ?: null,
                'product_number' => $request->productNumber ?: null,
                'remarks' => $request->remarks ?: null,
                'qrcode' => null,
            ]);

            Inspection::create([
                'item_id' => $item->id,
                'scheduled_date' => $request->inspectionSchedule,
                'inspection_date' => null, // migrationでnullableにする
                'status' => false, // 未実施がfalse
                'inspection_person' => null, // 空白は保存できるのか,nullとの違い
                'details' => null, 
            ]);

            Disposal::create([
                'item_id' => $item->id,
                'scheduled_date' => $request->disposalSchedule,
                'disposal_date' => null, // migrationでnullableにする
                'disposal_person' => '', // 空白は保存できるのか,nullとの違い
                'details' => null, 
            ]);
        
            
            // DB::commit(); // ここで確定

            Log::info('commitした');

            return to_route('items.index')
            ->with([
                'message' => '登録しました。',
                'status' => 'success'
            ]);

        // }catch(ValidationException $e){
        //     DB::rollBack();

        //     return redirect()->back()
        //     ->with([
        //         'message' => '登録中にエラーが発生しました',
        //         'status' => 'danger'
        //     ]);
        //     // ->withErrors($e->errors())->withInput();

        // }catch(\Exception $e){
        //     DB::rollBack();

        //     return redirect()->back()
        //     ->with([
        //         'message' => '登録中にエラーが発生しました',
        //         'status' => 'danger'
        //     ]);
        //     // ->withInput();
        // }
    }



    public function show(Item $item)
    {
        // dd($item);
        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod', 'inspections', 'disposal'];
        $item = Item::with($withRelations)->find($item->id);  

        // statusがfalseの点検予定日だけを取得し、日付でソートして最も古いものを取得
        $pendingInspection = $item->inspections->where('status', false)->sortBy('scheduled_date')->first();
        // 最後に行った点検のレコードを取得
        $previousInspection = $item->inspections->where('status', true)->sortByDesc('inspection_date')->first();
        // dd($previousInspection);

        // image1カラムがnullかチェック
        if (is_null($item->image1)) {
            $item->image_path1 = asset('storage/items/No_Image.jpg');
        } else {
            // image1の画像名のファイルが存在するかチェックする
            if (Storage::exists('public/items/' . $item->image1)) {
                // 画像ファイルが存在する場合
                $item->image_path1 = asset('storage/items/' . $item->image1);
            } else {
                // 画像ファイルが存在しない場合
                $item->image_path1 = asset('storage/items/No_Image.jpg');
            }
        }

        // dd($pendingInspection);

        $user = auth()->user();

        return Inertia::render('Items/Show', [
            'item' => $item,
            'pendingInspection' => $pendingInspection,
            'previousInspection' => $previousInspection,
            'userName' => $user->name,
        ]);
    }


    public function edit(Item $item)
    {
        Gate::authorize('staff-higher');

        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod', 'inspections', 'disposal'];
        $item = Item::with($withRelations)->find($item->id);  

        // statusがfalseの点検予定日だけを取得し、日付でソートして最も古いものを取得
        $pendingInspection = $item->inspections->where('status', false)->sortBy('scheduled_date')->first();

        // image1カラムがnullかチェック
        if (is_null($item->image1)) {
            $item->image_path1 = asset('storage/items/No_Image.jpg');
        } else {
            // image1の画像名のファイルが存在するかチェックする
            if (Storage::exists('public/items/' . $item->image1)) {
                // 画像ファイルが存在する場合
                $item->image_path1 = asset('storage/items/' . $item->image1);
            } else {
                // 画像ファイルが存在しない場合
                $item->image_path1 = asset('storage/items/No_Image.jpg');
            }
        }

        // dd($pendingInspection);

        $categories = Category::all();
        $locations = Location::all();
        $units = Unit::all();
        $usage_statuses = UsageStatus::all();
        $acquisition_methods = AcquisitionMethod::all();
        $edit_reasons = EditReason::all();

        return Inertia::render('Items/Edit', [
            'item' => $item,
            'pendingInspection' => $pendingInspection,
            'categories' => $categories,
            'locations' => $locations,
            'units' => $units,
            'usageStatuses' => $usage_statuses,
            'acquisitionMethods' => $acquisition_methods,
            'pendingInspection' => $pendingInspection,
            'editReasons' => $edit_reasons,
        ]);
    }



    public function update(UpdateItemRequest $request, Item $item)
    {
        Gate::authorize('staff-higher');

        // dd($request->name, $request->pendingInspection, $item->name);


        // 課題１ Inspectionsのschedule
        // 課題２ 画像の変更処理　順番はどうか
        // １、DB上のitem->image1の画像のファイル名変更
        // ２、元の画像のファイル名からパス作成→元の画像ファイルの削除処理
        
        // トランザクション処理をする、ItemObserverでもDBにも保存するため
        // DB::beginTransaction();

        // try {
            // dd($request->imageFile);

            // 画像アップロード
            $imageFile = $request->imageFile; // 一時保存
            // ->isValid()は念のため、ちゃんとアップロードできているかチェックしてくれる
            $fileNameToStore = null;
            if(!is_null($imageFile) && $imageFile->isValid() ){
                $fileNameToStore = ImageService::resizeUpload($imageFile);
            }




            // dd($request->editReasonId, $request->editReasonText);
            // 編集理由はItemObserverのEditedメソッドで保存するので、
            // Sessionに一旦保存して、Edithistoryの保存する
             // 編集理由をセッションに保存->ItemObserverのupdatedメソッドで取得
            Session::put('editReasonId', $request->editReasonId);
            Session::put('editReasonText', $request->editReasonText);


            // Itemの更新
            // $item->image_path1 = $request->image_path1;
            $item->name = $request->name;
            $item->category_id = $request->categoryId;
            $item->image1 = $fileNameToStore ?: null;
            $item->stock = $request->stock;
            $item->unit_id = $request->unitId;
            $item->minimum_stock = $request->minimumStock;
            $item->notification = $request->notification;
            $item->usage_status_id = $request->usageStatusId;
            $item->end_user = $request->endUser;
            $item->location_of_use_id = $request->locationOfUseId;
            $item->storage_location_id = $request->storageLocationId;
            $item->acquisition_method_id = $request->acquisitionMethodId;
            $item->acquisition_source = $request->acquisitionSource;
            $item->price = $request->price;
            $item->date_of_acquisition = $request->dateOfAcquisition;
            // $item->inspection_schedule = $request->inspection_schedule;
            // $item->disposal_schedule = $request->disposal_schedule;
            $item->manufacturer = $request->manufacturer;
            $item->product_number = $request->productNumber;
            $item->remarks = $request->remarks;
            $item->save();


            // 点検フォームが空欄で編集で追加するパターン
            // 最初から点検フォームに入っている（DBに保存しているパターン）パターン
            // 点検データの更新または作成
            
            // 点検日のレコード、Vue側から値が返ってきたら変更の有無に関わらず保存する→シンプル        
            if ($request->inspectionSchedule) {
                // pendingInspectionとは、Inspectionsテーブルでstatusがfalseの一番近い（日付が古い）scheduled_date
                // nullの可能性もある
                // pendingInspectionのデータがあれば更新、作成されていなければ作成する
                // dd($request->pendingInspection);
                if($request->pendingInspection) {
                    // データがあるなら、既存の点検データを更新
                    $pendingInspection = $item->inspections()->where('id', $request->pendingInspection['id'])->first(); //渡ってきたオブジェクトを取得
                    $pendingInspection->update(['scheduled_date' => $request->inspectionSchedule]);
                } else {
                    // データがないなら、新しい点検データを作成
                    $item->inspections()->create(['scheduled_date' => $request->inspectionSchedule]);
                }
            }

            // 廃棄フォームの更新または作成
            // もし廃棄予定日があればテーブルに保存する
            $disposal = $item->disposal()->first();
            if ($request->disposalSchedule) {
                if ($disposal) {
                    // 既存のDisposalデータを更新
                    $disposal->update(['scheduled_date' => $request->disposalSchedule]);
                } else {
                    // 新しいDisposalデータを作成
                    $item->disposal()->create(['scheduled_date' => $request->disposalSchedule]);
                }    
            }

            // DB::commit();

            return to_route('items.index')
            ->with([
                'message' => '更新しました。',
                'status' => 'success'
            ]);

        

        // } catch (\Exception $e) {
        //     DB::rollBack();

    //     return redirect()->back()
    //     ->with([
    //         'message' => '登録中にエラーが発生しました',
    //         'status' => 'danger'
    //     ]);
        // }



    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return to_route('items.index')
        ->with([
            'message' => '廃棄しました。',
            'status' => 'danger'
        ]);
    }

    public function restore($id)
    {
        $item = Item::withTrashed()->find($id);
        if ($item) {
            $item->restore();

            return to_route('items.index')
            ->with([
                'message' => '備品を復元しました。',
                'status' => 'success'
            ]);
        }

        return to_route('items.index')
        ->with([
            'message' => '該当の備品が存在しませんでした',
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
