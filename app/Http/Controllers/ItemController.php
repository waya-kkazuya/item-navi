<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Location;
use App\Models\Inspection;
use App\Models\Disposal;
use App\Models\UsageStatus;
use App\Models\AcquisitionMethod;
use App\Models\EditReason;
use App\Models\StockTransaction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Services\ManagementIdService;
use App\Services\ImageService;
use App\Services\QrCodeService;
use Inertia\Inertia;

class ItemController extends Controller
{
    const CATEGORY_ID_FOR_CONSUMABLE_ITME = 1;
    protected $managementIdService;
    protected $imageService;
    protected $qrCodeService;

    public function __construct(
        ManagementIdService $managementIdService,
        ImageService $imageService,
        QrCodeService $qrCodeService
    ) {
        $this->managementIdService = $managementIdService;
        $this->imageService = $imageService;
        $this->qrCodeService = $qrCodeService;

        // ミドルウェアの設定
        $this->middleware('RestrictGuestAccess', ['only' => ['store', 'update', 'destroy', 'restore']]);
    }

    public function index(Request $request)
    {  
        Gate::authorize('staff-higher');

        Log::info('ItemController index method called');

        try {
            $search = $request->query('search', '');
            
            // 作成日でソートの値、初期値はasc
            $sortOrder = $request->query('sortOrder', 'desc');

            // プルダウンの数値、第2引数は初期値で0
            $category_id = $request->query('categoryId', 0);
            $location_of_use_id = $request->query('locationOfUseId', 0);
            $storage_location_id = $request->query('storageLocationId', 0);

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

            // 通常の備品か廃棄済みの備品かの分岐
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
            $items = $query->paginate(20);
            $total_count = $items->total(); // 合計件数

            $current_page = $items->currentPage(); // 現在のページ番号
            $per_page = $items->perPage(); // 1ページあたりの項目数
            $start_number = ($current_page - 1) * $per_page + 1; // 現在のページの最初の項目番号
            $end_number = min($start_number + $items->count() - 1, $total_count); // 現在のページの最後の項目番号


            // map関数を使用するとpaginateオブジェクトの構造が変わり、ペジネーションが使えなくなる
            $items->getCollection()->transform(function ($item) {
                return $this->imageService->setImagePathToObject($item);
            });

            $items->getCollection()->transform(function ($item) {
                // inspection_scheduled_dateを追加
                $inspection = $item->inspections->where('status', false)->sortBy('inspection_scheduled_date')->first();
                $item->inspection_scheduled_date = $inspection ? $inspection->inspection_scheduled_date : null;
                return $item;
            });

            // 変換後のコレクションを元のpaginateオブジェクトに戻す
            $items = $items->setCollection($items->getCollection());

            // プルダウン用データ
            $categories = Category::all();
            $locations = Location::all();


            // 廃棄済み備品用API情報
            if ($request->has('disposal')) {
                return [
                    'items' => $items,
                    'total_count' => $total_count,
                    'startNumber' => $start_number,
                    'endNumber' => $end_number
                ];
            }
            
            Log::info('ItemController index method succeeded');

            return Inertia::render('Items/Index', [
                'items' => $items,
                'categories' => $categories,
                'locations' => $locations,
                'search' => $search,
                'sortOrder' => $sortOrder,
                'categoryId' => $category_id,
                'locationOfUseId' => $location_of_use_id,
                'storageLocationId' => $storage_location_id,
                'totalCount' => $total_count,
                'startNumber' => $start_number,
                'endNumber' => $end_number
            ]);
        } catch (\Exception $e) {
            Log::error('ItemController index method failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
        }
    }

    public function create(Request $request)
    {   
        Gate::authorize('staff-higher');

        Log::info('ItemController create method called');

        $categories = Category::all();
        $locations = Location::all();
        $units = Unit::all();
        $usage_statuses = UsageStatus::all();
        $acquisition_methods = AcquisitionMethod::all();

        Log::info('ItemController create method succeeded');

        // $request->queryはリクエスト一覧から「新規作成」でCreate.vueを開いたときに自動入力する値
        return Inertia::render('Items/Create', [
            'categories' => $categories,
            'locations' => $locations,
            'units' => $units,
            'usageStatuses' => $usage_statuses,
            'acquisitionMethods' => $acquisition_methods,
            'name' => $request->query('name'),
            'category_id' => $request->query('category_id'),
            'location_of_use_id' => $request->query('location_of_use_id'),
            'manufacturer' => $request->query('manufacturer'),
            'price' => $request->query('price'),
        ]);
    }

    public function store(StoreItemRequest $request)
    {
        Gate::authorize('staff-higher');

        Log::info('ItemController store method called');

        // 編集理由はItemObserverのメソッド内でセッションから取得し、edithistoriesに保存
        Session::put('operation_type', 'store');

        // 変数の初期化
        $fileNameToStore = null;
        $labelNameToStore = null;
        $qrCodeNameToStore = null;

        DB::beginTransaction();

        try{
            // もしもカテゴリが消耗品以外で、minimumに数値が入っていたらnullにする
            if($request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME){
                $minimum_stock = $request->minimum_stock;
            } else {
                $minimum_stock = null;
            }

            $management_id = $this->managementIdService->generate($request->category_id);

            $item = Item::create([
                'management_id' => $management_id,
                'name' => $request->name,
                'category_id' => $request->category_id ,
                'stock' => $request->stock ?? 0,
                'unit_id' => $request->unit_id,
                'minimum_stock' => $minimum_stock,
                'notification' => $request->notification,
                'usage_status_id' => $request->usage_status_id,
                'end_user' => $request->end_user ?: null,
                'location_of_use_id' => $request->location_of_use_id,
                'storage_location_id' => $request->storage_location_id,
                'acquisition_method_id' => $request->acquisition_method_id,
                'acquisition_source' => $request->acquisition_source ?: null,
                'price' => $request->price,
                'date_of_acquisition' => $request->date_of_acquisition,
                'manufacturer' => $request->manufacturer ?: null,
                'product_number' => $request->product_number ?: null,
                'remarks' => $request->remarks ?: null,
                'qrcode' => null,
            ]);

            if($request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME){
                StockTransaction::create([
                    'item_id' => $item->id,
                    'transaction_type' => '登録',
                    'quantity' => $item->stock,
                    'operator_name' => Auth::user()->name,
                ]);
            }

            if ($request->inspection_scheduled_date !== null) {
                Inspection::create([
                    'item_id' => $item->id,
                    'inspection_scheduled_date' => $request->inspection_scheduled_date,
                    'inspection_date' => null,
                    'status' => false, // 未実施がfalse
                    'inspection_person' => null,
                    'details' => null, 
                ]);
            }

            if ($request->disposal_scheduled_date !== null) {
                Disposal::create([
                    'item_id' => $item->id,
                    'disposal_scheduled_date' => $request->disposal_scheduled_date,
                    'disposal_date' => null,
                    'disposal_person' => null,
                    'details' => null, 
                ]);
            }

            // 画像名image1はレコードが作成された後に部分的に更新する
            // ->isValid()は念のため、ちゃんとアップロードできているかチェックしてくれる
            if(!is_null($request->image_file) && $request->image_file->isValid() ){
                $fileNameToStore = $this->imageService->resizeUpload($request->image_file);
                Item::withoutEvents(function () use ($item, $fileNameToStore) {
                    $item->update(['image1' => $fileNameToStore]);
                });  
            }

            // QRコード生成 ※消耗品の時だけ生成する
            if($request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME){ 
                $result = $this->qrCodeService::upload($item);
                // トランザクション処理失敗時のためにQRコード画像のファイル名を取得
                $labelNameToStore = $result['labelNameToStore'];
                $qrCodeNameToStore = $result['qrCodeNameToStore'];
                
                // 一時的にObserverを無効にする
                Item::withoutEvents(function () use ($item, $labelNameToStore) {
                    $item->update(['qrcode' => $labelNameToStore]);
                });
            }

            DB::commit();

            Log::info('ItemController store method succeeded');

            return to_route('items.index')
            ->with([
                'message' => '備品を登録しました',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('ItemController store method Transaction failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            // アップロードした備品の画像の削除
            $imagePath = 'items/' . $fileNameToStore;
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // qrCodeService内で保存したQRコードを削除
            $qrImagePath = 'qrcode/' . $qrCodeNameToStore;
            if (Storage::disk('public')->exists($qrImagePath)) {
                Storage::disk('public')->delete($qrImagePath);            
            }

            // 保存したQRコードラベルを削除
            $labelImagePath = 'labels/' . $labelNameToStore;
            if (Storage::disk('public')->exists($labelImagePath)) {
                Storage::disk('public')->delete($labelImagePath);            
            }

            return redirect()->back()
            ->with([
                'message' => '備品の登録中にエラーが発生しました',
                'status' => 'danger'
            ]);
        }
    }

    public function show(Item $item)
    {
        Gate::authorize('staff-higher');

        Log::info('ItemController show method called');

        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod', 'inspections', 'disposal'];
        $item = Item::with($withRelations)->find($item->id);  

        // まだ点検を実施していない点検テーブルのレコードを取得
        $uncompleted_inspection = $item->inspections->where('status', false)->sortBy('inspection_scheduled_date')->first();
        // // 最後に行った点検のレコードを取得
        $last_completed_inspection = $item->inspections->where('status', true)->sortByDesc('inspection_date')->first();

        // 画像パスを追加
        $this->imageService->setImagePathToObject($item);

        $user = auth()->user();

        Log::info('ItemController show method succeeded');

        return Inertia::render('Items/Show', [
            'item' => $item,
            'uncompleted_inspection' => $uncompleted_inspection,
            'last_completed_inspection' => $last_completed_inspection,
            'userName' => $user->name,
        ]);                                                           
    }

    public function edit(Item $item)
    {
        Gate::authorize('staff-higher');

        Log::info('ItemController edit method called');

        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod', 'inspections', 'disposal'];
        $item = Item::with($withRelations)->find($item->id);  

        // 未実行の点検予定日を取得
        $uncompleted_inspection = $item->inspections->where('status', false)->sortBy('inspection_scheduled_date')->first();
        $uncompleted_inspection_scheduled_date = $uncompleted_inspection ? $uncompleted_inspection->inspection_scheduled_date : null;

        // 画像パスを追加
        $this->imageService->setImagePathToObject($item);

        $categories = Category::all();
        $locations = Location::all();
        $units = Unit::all();
        $usage_statuses = UsageStatus::all();
        $acquisition_methods = AcquisitionMethod::all();
        $edit_reasons = EditReason::all();

        Log::info('ItemController edit method succeeded');

        return Inertia::render('Items/Edit', [
            'item' => $item,
            'uncompleted_inspection_scheduled_date' => $uncompleted_inspection_scheduled_date,
            'categories' => $categories,
            'locations' => $locations,
            'units' => $units,
            'usageStatuses' => $usage_statuses,
            'acquisitionMethods' => $acquisition_methods,
            'editReasons' => $edit_reasons,
        ]);
    }


    public function update(UpdateItemRequest $request, Item $item)
    {
        // トランザクション処理は、ItemObserverでのDB保存もロールバックする
        Gate::authorize('staff-higher');

        Log::info('ItemController update method called');

        // ロールバックした時の備品画像を元に戻す準備
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }

        // 編集理由はItemObserverのメソッド内でセッションから取得し、edithistoriesに保存
        Session::put('edit_reason_id', $request->edit_reason_id);
        Session::put('edit_reason_text', $request->edit_reason_text);
        Session::put('operation_type', 'update');

        // 変数の初期化
        $fileNameToStore = null;
        $fileNameOfOldImage = null;
        $temporaryBackupPath = null;
        $labelNameToStore = null;
        $qrCodeNameToStore = null;

        DB::beginTransaction();
        
        try {
            if(
                $request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME &&
                $item->stock != $request->stock
            ){
                StockTransaction::create([
                    'item_id' => $item->id,
                    'transaction_type' => '修正',
                    'quantity' => $request->stock - $item->stock, //差分を保存
                    'operator_name' => Auth::user()->name,
                ]);
            }

            $item->name = $request->name;
            $item->category_id = $request->category_id;
            $item->stock = $request->stock;
            $item->unit_id = $request->unit_id;
            // 消耗品の時だけminimum_stockを保存できる
            if ($request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME) {
                $item->minimum_stock = $request->minimum_stock;
            } else {
                $item->minimum_stock = null;
            }
            $item->notification = $request->notification;
            $item->usage_status_id = $request->usage_status_id;
            $item->end_user = $request->end_user;
            $item->location_of_use_id = $request->location_of_use_id;
            $item->storage_location_id = $request->storage_location_id;
            $item->acquisition_method_id = $request->acquisition_method_id;
            $item->acquisition_source = $request->acquisition_source;
            $item->price = $request->price;
            $item->date_of_acquisition = $request->date_of_acquisition;
            $item->manufacturer = $request->manufacturer;
            $item->product_number = $request->product_number;
            $item->remarks = $request->remarks;
            $item->save();

            // 点検レコードの更新処理
            // 未実行の点検レコードを取得
            $uncompleted_inspection = $item->inspections->where('status', false)->sortBy('inspection_scheduled_date')->first();
            // 更新すべき点検予定日が存在するか
            if (!is_null($request->inspection_scheduled_date)) {
                if(!is_null($uncompleted_inspection)) {
                    // 未実施の点検予定日が保存されているInspectionレコードがあればそのレコードを新しい値で更新
                    $uncompleted_inspection->update(['inspection_scheduled_date' => $request->inspection_scheduled_date]);
                } else {
                    // 点検(Inspection)テーブルのレコードがないなら、新しいレコードを作成
                    $item->inspections()->create(['inspection_scheduled_date' => $request->inspection_scheduled_date]);
                }
            }

            // 廃棄レコードの更新処理
            // 更新すべき廃棄予定日が存在するか
            $disposal = $item->disposal()->first();
            if (!is_null($request->disposal_scheduled_date)) {
                if (!is_null($disposal)) {
                    // 既存のレコードがあるなら、既存の廃棄レコードを更新
                    $disposal->update(['disposal_scheduled_date' => $request->disposal_scheduled_date]);
                } else {
                    // 既存のレコードがないなら、新しい廃棄レコードを作成
                    $item->disposal()->create(['disposal_scheduled_date' => $request->disposal_scheduled_date]);
                }
            }


            if(!is_null($request->image_file) && $request->image_file->isValid() ){
                // すでに画像があれば削除
                $fileNameOfOldImage = $item->image1;
                if ($fileNameOfOldImage) {
                    $temporaryBackupPath = 'temp/'.$fileNameOfOldImage;
                    // 一時的な退避フォルダ(temp)に変更前の画像をコピーでバックアップ
                    Storage::disk('public')->copy('items/'.$fileNameOfOldImage, $temporaryBackupPath);
                    Storage::disk('public')->delete('items/'.$fileNameOfOldImage);
                }

                // 画像ファイルのアップロードとDBのimage1のファイル名更新
                $fileNameToStore = $this->imageService->resizeUpload($request->image_file);
                $item->update(['image1' => $fileNameToStore]);
            }


            // QRラベルの生成のタイミング=category_idを消耗品のidに変更した瞬間、かつQRラベル画像がなまだ存在しない時
            // 1.変更後: $request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME
            // 2.変更前: $item->id !== self::CATEGORY_ID_FOR_CONSUMABLE_ITME
            // 3.is_null($item->qrcode) 
            if ($request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME
                && $item->id !== self::CATEGORY_ID_FOR_CONSUMABLE_ITME
                && is_null($item)) {

                $result = $this->qrCodeService::upload($item);
                // トランザクション処理失敗時のためにQRコード画像のファイル名を取得
                $labelNameToStore = $result['labelNameToStore'];
                $qrCodeNameToStore = $result['qrCodeNameToStore'];
                
                $item->update(['qrcode' => $labelNameToStore]);
            }

            DB::commit();

            Log::info('ItemController update method succeeded');

            return to_route('items.show', $item->id)
            ->with([
                'message' => '備品を更新しました',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('ItemController update method Transaction failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            // アップロードしたプロフィール画像を削除
            if (Storage::disk('public')->exists('items/' . $fileNameToStore)) {
                Storage::disk('public')->delete('items/' . $fileNameToStore);
            }

            // バックアップした変更前の画像を元の場所に保存
            if ($temporaryBackupPath) {
                Storage::disk('public')->move($temporaryBackupPath, 'items/'.$fileNameOfOldImage);
            }

            // qrCodeService内で保存したQRコードを削除
            $qrImagePath = 'qrcode/' . $qrCodeNameToStore;
            if (Storage::disk('public')->exists($qrImagePath)) {
                Storage::disk('public')->delete($qrImagePath);            
            }

            // 保存したQRコードラベルを削除
            $labelImagePath = 'labels/' . $labelNameToStore;
            if (Storage::disk('public')->exists($labelImagePath)) {
                Storage::disk('public')->delete($labelImagePath);            
            }

            return redirect()->back()
            ->with([
                'message' => '登録中にエラーが発生しました',
                'status' => 'danger'
            ]);
            
        } finally {
            // 成功しても失敗しても必ず行う処理
            if ($temporaryBackupPath) {
                Storage::disk('public')->delete($temporaryBackupPath);
            }
        }
    }

    // 廃棄はDisposalControllerで行う
    // public function destroy(Item $item)
    // {
    
    // }

    public function restore($id)
    {
        Gate::authorize('staff-higher');

        Log::info('ItemController restore method called');

        $item = Item::withTrashed()->find($id);
        if ($item) {
            $item->restore();

            Log::info('ItemController restore method succeeded');

            return to_route('items.index')
            ->with([
                'message' => '備品を復元しました',
                'status' => 'success'
            ]);
        }

        Log::warning('ItemController restore method failed');

        return to_route('items.index')
        ->with([
            'message' => '該当の備品が存在しませんでした',
            'status' => 'danger'
        ]);
    }

    // public function forceDelete($id)
    // {
        
    // }

    public function disposedItemIndex()
    {
        Gate::authorize('staff-higher');

        Log::info('ItemController disposedItemIndex method called');

        $disposedItems = Item::onlyTrashed()->get();

        Log::info('ItemController disposedItemIndex method succeeded');
        
        return Inertia::render('Items/Index', [
            'disposedItems' => $disposedItems,
        ]);
    }
}
