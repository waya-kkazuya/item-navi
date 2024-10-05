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

        // paginateじゃなくget()の時のデータ構造解析
        // dd($query->get());

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
            

            // 画像保存とimage1画像名カラムをupdateで部分的に変更
            // トランザクション処理失敗の時のため、画像削除処理をcatch節に書く

            // 画像保存処理はデータ保存処理の後に配置
            // image1は部分的に保存する
            
            // ->isValid()は念のため、ちゃんとアップロードできているかチェックしてくれる
            $fileNameToStore = null;
            if(!is_null($request->image_file) && $request->image_file->isValid() ){
                $fileNameToStore = $this->imageService::resizeUpload($request->image_file);
                $item->update(['image1' => $fileNameToStore]);
                // $pendingInspection->update(['inspection_scheduled_date' => $request->inspectionSchedule]);
            }

            // // QRコード生成、QrCodeServiceに切り分ける
            // // ※消耗品のときだけcategory_id=1のときだけ生成する
            $labelNameToStore = null;
            $qrCodeNameToStore = null;
            if($request->category_id == self::CATEGORY_ID_FOR_CONSUMABLE_ITME){ 
                // ※注意
                // 保存したあと、items->update()で部分的にqrcodeの名前を変更する
                $url = 'https://itemnavi.com/consumable_items';
                // $itemそのものを渡せるか
                // $labelNameToStore = $this->qrCodeService::upload($item->management_id, );
                // $labelNameToStore = $this->qrCodeService::upload($item);
                $result = $this->qrCodeService::upload($item);
                $labelNameToStore = $result['labelNameToStore'];
                $qrCodeNameToStore = $result['qrCodeNameToStore'];
                // dd($labelNameToStore, $qrCodeNameToStore);
                $item->update(['qrcode' => $labelNameToStore]);
            }

            DB::commit();

            Log::info('commitした');

            return to_route('items.index')
            ->with([
                'message' => '登録しました。',
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
                'message' => '登録中にエラーが発生しました',
                'status' => 'danger'
            ]);
        }
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
        // 再代入はNG
        $item = Item::with($withRelations)->find($item->id);  

        // statusがfalseの点検予定日だけを取得し、日付でソートして最も古いものを取得
        $pendingInspection = $item->inspections->where('status', false)->sortBy('inspection_scheduled_date')->first();
        // 最後に行った点検のレコードを取得
        $previousInspection = $item->inspections->where('status', true)->sortByDesc('inspection_date')->first();
        // dd($previousInspection);
        // $previousInspectionDate = $previousInspection ? Carbon::parse($previousInspection->inspection_date)->format('Y年m月d日') : '';

        // dd($pendingInspection);

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
            if (Storage::disk('public')->exists('items/' . $item->image1)) {
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
            // dd($request->image_file);
            
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


            // 点検フォームが空欄で編集で追加するパターン
            // 最初から点検フォームに入っている（DBに保存しているパターン）パターン
            // 点検データの更新または作成

            // 点検日のレコード、Vue側から値が返ってきたら変更の有無に関わらず保存する→シンプル
            // pendingInspectionとは、Inspectionsテーブルでstatusがfalseの一番近い（日付が古い）scheduled_date      
            // 変更すべきinspectionScheduleがあれば変更する、なければ何もしない
            if ($request->inspectionSchedule) {      
                if($request->pendingInspection) {
                    // 既存の点検予定日が保存されているInspectionレコードがあればそのレコードを新しい値で更新
                    $pendingInspection = $item->inspections()->where('id', $request->pendingInspection['id'])->first(); //渡ってきたオブジェクトを取得
                    $pendingInspection->update(['inspection_scheduled_date' => $request->inspectionSchedule]);
                } else {
                    // 既存のレコードがないなら、新しい点検のレコードを作成
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

            // ->isValid()は念のため、ちゃんとアップロードできているかチェックしてくれる
            $fileNameToStore = null;
            if(!is_null($request->image_file) && $request->image_file->isValid() ){
                // 古い画像があれば削除
                if ($item->image1) {
                    Storage::disk('public')->delete('items/' . $item->image1);
                }

                // 画像ファイルのアップロードとDBのimage1のファイル名更新
                $fileNameToStore = ImageService::resizeUpload($request->image_file);
                $item->update(['image1' => $fileNameToStore]);
            }


            // DB::commit();

            // ひとまず、showに画面遷移するように変更
            return to_route('items.show', $item->id)
            ->with([
                'message' => '更新しました。',
                'status' => 'success'
            ]);
            // return to_route('items.index')
            // ->with([
            //     'message' => '更新しました。',
            //     'status' => 'success'
            // ]);

        

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
