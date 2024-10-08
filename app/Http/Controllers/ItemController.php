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
// use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Carbon\Carbon;
use App\Services\ManagementIdService;
use App\Services\ImageService;
use App\Services\QrCodeService;
use Intervention\Image\Typography\FontFactory;

class ItemController extends Controller
{
    const CATEGORY_ID_FOR_CONSUMABLE_ITME = 1;

    protected $managementIdService;
    protected $imageService;
    protected $qrCodeService;

    public function __construct(ManagementIdService $managementIdService, ImageService $imageService, QrCodeService $qrCodeService)
    {
        $this->managementIdService = $managementIdService;
        $this->imageService = $imageService;
        $this->qrCodeService = $qrCodeService;
    }

    public function index(Request $request)
    {  
        Gate::authorize('staff-higher');

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
        // 明示的に厳密にイコールで切り替え可能
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

        // map関数を使用するとpaginateオブジェクトの構造が変わり、ペジネーションが使えなくなる
        // コレクションを取得して変換
        $items->getCollection()->transform(function ($item) {
            // image1カラムがnullかチェック
            if(is_null($item->image1)) {
                $item->image_path1 = asset('storage/items/No_Image.jpg');
            } else {
                // image1の画像名のファイルが存在するかチェックする
                // if(Storage::exists('public/items/' . $item->image1)) {
                if(Storage::disk('public')->exists('items/' . $item->image1)) {
                    // 画像ファイルが存在する場合
                    \Log::info(' 画像ファイルが存在する場合');
                    $item->image_path1 = asset('storage/items/' . $item->image1);
                } else {
                    // 画像ファイルが存在しない場合
                    \Log::info(' 画像ファイルが存在しない場合');
                    $item->image_path1 = asset('storage/items/No_Image.jpg');
                }
            }
            // pending_inspection_dateの設定
            $item->pending_inspection_date = $item->inspections->where('status', false)->sortBy('scheduled_date')->first()->scheduled_date ?? null;

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



    public function create(Request $request)
    {   
        Gate::authorize('staff-higher');

        // dd($request);

        $categories = Category::all();
        $locations = Location::all();
        $units = Unit::all();
        $usage_statuses = UsageStatus::all();
        $acquisition_methods = AcquisitionMethod::all();

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
        

        // 画像保存もトランザクション処理内に入れるかどうか
        DB::beginTransaction();

        try{
            // もしもカテゴリが消耗品以外で、minimumに数値が入っていたらnullにする
            // categoriesテーブルで消耗品のidは1、定数に入れる
            if($request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME){
                $minimum_stock = $request->minimum_stock;
            } else {
                $minimum_stock = null;
            }

            $management_id = $this->managementIdService->generate($request->category_id);

            // dd($management_id);

            // 保存したオブジェクトを変数に入れてInspectionのcreateに使用する
            $item = Item::create([
                'id' => $request->id,
                'management_id' => $management_id,
                'name' => $request->name,
                'category_id' => $request->category_id ,
                // 'image1' => $fileNameToStore ?: null,
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

            if ($request->inspection_scheduled_date !== null) {
                Inspection::create([
                    'item_id' => $item->id,
                    'inspection_scheduled_date' => $request->inspection_scheduled_date,
                    'inspection_date' => null, // migrationでnullableにする
                    'status' => false, // 未実施がfalse
                    'inspection_person' => null, // 空白は保存できるのか,nullとの違い
                    'details' => null, 
                ]);
            }

            if ($request->disposal_scheduled_date !== null) {
                Disposal::create([
                    'item_id' => $item->id,
                    'disposal_scheduled_date' => $request->disposal_scheduled_date,
                    'disposal_date' => null, // migrationでnullableにする
                    'disposal_person' => '', // 空白は保存できるのか,nullとの違い
                    'details' => null, 
                ]);
            }

            // 画像名image1はレコードが作成された後に部分的に更新する
            // ->isValid()は念のため、ちゃんとアップロードできているかチェックしてくれる
            $fileNameToStore = null;
            if(!is_null($request->image_file) && $request->image_file->isValid() ){
                $fileNameToStore = $this->imageService::resizeUpload($request->image_file);
                Item::withoutEvents(function () use ($item, $fileNameToStore) {
                    $item->update(['image1' => $fileNameToStore]);
                });  
                // $pendingInspection->update(['inspection_scheduled_date' => $request->inspectionSchedule]);
            }

            // QRコード生成 ※消耗品の時だけ生成する
            $labelNameToStore = null;
            $qrCodeNameToStore = null;
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

            return to_route('items.index')
            ->with([
                'message' => '備品を登録しました',
                'status' => 'success'
            ]);

        } catch(ValidationException $e) {
            DB::rollBack();

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
        $withRelations = ['category', 'unit', 'usageStatus', 'locationOfUse', 'storageLocation', 'acquisitionMethod', 'inspections', 'disposal'];
        // 再代入はNG
        $item = Item::with($withRelations)->find($item->id);  

        // まだ点検を実施していない点検テーブルのレコードを取得
        $pendingInspection = $item->inspections->where('status', false)->sortBy('inspection_scheduled_date')->first();
        // 最後に行った点検のレコードを取得
        $previousInspection = $item->inspections->where('status', true)->sortByDesc('inspection_date')->first();
        // $previousInspectionDate = $previousInspection ? Carbon::parse($previousInspection->inspection_date)->format('Y年m月d日') : '';

        if (is_null($item->image1)) {
            $item->image_path1 = asset('storage/items/No_Image.jpg');
        } else {
            if (Storage::disk('public')->exists('items/' . $item->image1)) {
                // 画像ファイルが存在する場合
                $item->image_path1 = asset('storage/items/' . $item->image1);
            } else {
                // 画像ファイルが存在しない場合
                $item->image_path1 = asset('storage/items/No_Image.jpg');
            }
        }

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
            if (Storage::disk('public')->exists('items/' . $item->image1)) {
                // 画像ファイルが存在する場合
                $item->image_path1 = asset('storage/items/' . $item->image1);
            } else {
                // 画像ファイルが存在しない場合
                $item->image_path1 = asset('storage/items/No_Image.jpg');
            }
        }

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

        // トランザクション処理は、ItemObserverでのDB保存もロールバックする

        // トランザクション処理が失敗した際に、画像を元に戻すため
        // tempフォルダが存在するか確認、なければ作成
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }
    
        DB::beginTransaction();

        try {            
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

            // 編集理由はItemObserverのupdatedメソッドでセッションから取得しedithistoriesに保存する
            Session::put('edit_reeason_id', $request->edit_reeason_id);
            Session::put('edit_reason_text', $request->edit_reason_text);
            Session::put('operation_type', 'update');



            // 点検日のレコード、Vue側から値が返ってきたら変更の有無に関わらず保存する
            // inspectionScheduleはVueから渡ってきた点検予定日
            if ($request->inspectionSchedule) {
                // pendingInspectionは未実施の点検日のレコード
                if($request->pendingInspection) {
                    // 未実施の点検予定日が保存されているInspectionレコードがあればそのレコードを新しい値で更新
                    $pendingInspection = $item->inspections()->where('id', $request->pendingInspection['id'])->first(); //渡ってきたオブジェクトを取得
                    $pendingInspection->update(['inspection_scheduled_date' => $request->inspectionSchedule]);
                } else {
                    // 点検(Inspection)テーブルのレコードがないなら、新しいレコードを作成
                    $item->inspections()->create(['inspection_scheduled_date' => $request->inspectionSchedule]);
                }
            }

            // 廃棄フォームの更新または作成
            // もし廃棄予定日があればテーブルに保存する
            $disposal = $item->disposal()->first();
            if ($request->disposalSchedule) {
                if ($disposal) {
                    // 既存のレコードがあるなら、既存の廃棄レコードを更新
                    $disposal->update(['disposal_scheduled_date' => $request->disposalSchedule]);
                } else {
                    // 既存のレコードがないなら、新しい廃棄レコードを作成
                    $item->disposal()->create(['disposal_scheduled_date' => $request->disposalSchedule]);
                }    
            }

            $fileNameToStore = null;
            $fileNameOfOldImage = null;
            if(!is_null($request->image_file) && $request->image_file->isValid() ){
                // 古い画像があれば削除
                $fileNameOfOldImage = $item->image1;
                if ($fileNameOfOldImage) {
                    $temporaryBackupPath = 'temp/'.$fileNameOfOldImage;
                    // 一時的な退避フォルダ(temp)に変更前の画像をコピーでバックアップ
                    Storage::disk('public')->copy('items/'.$fileNameOfOldImage, $temporaryBackupPath);
                    Storage::disk('public')->delete('items/'.$fileNameOfOldImage);
                }

                // 画像ファイルのアップロードとDBのimage1のファイル名更新
                $fileNameToStore = ImageService::resizeUpload($request->image_file);
                $item->update(['image1' => $fileNameToStore]);
            }


            // QRラベルの生成のタイミング=category_idを消耗品のidに変更した瞬間、かつQRラベル画像がなまだ存在しない時
            // 1.変更後: $request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME
            // 2.変更前: $item->id !== self::CATEGORY_ID_FOR_CONSUMABLE_ITME
            // 3.is_null($item->qrcode) 
            $labelNameToStore = null;
            $qrCodeNameToStore = null;
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

            return to_route('items.show', $item->id)
            ->with([
                'message' => '備品を更新しました',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // 変更後の画像を削除
            if ($fileNameToStore) {
                Storage::disk('public')->delete('items/' . $fileNameToStore);
            }

            if ($temporaryBackupPath) {
                // バックアップファイルを元の場所に戻す
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


    public function destroy(Item $item)
    {
        $item->delete();

        return to_route('items.index')
        ->with([
            'message' => '備品を廃棄しました',
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
                'message' => '備品を復元しました',
                'status' => 'success'
            ]);
        }

        return to_route('items.index')
        ->with([
            'message' => '該当の備品が存在しませんでした',
            'status' => 'danger'
        ]);

    }


    // public function forceDelete($id)
    // {
        
    // }


    public function disposedItemIndex(){
        $disposedItems = Item::onlyTrashed()->get();
        
        return Inertia::render('Items/Index', [
            'disposedItems' => $disposedItems,
        ]);
    }
}
