<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Inspection;
use App\Models\Disposal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use App\Services\ImageService;


class InspectionAndDisposalItemController extends Controller
{
    protected $imageService;
    
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        Gate::authorize('staff-higher');

        // 欲しいのは備品情報ではなく、InspceitonとDisposalの予定
        $inspectionWithRelations = [
            'item.category', 
            'item.unit', 
            'item.usageStatus', 
            'item.locationOfUse', 
            'item.storageLocation', 
            'item.acquisitionMethod'
        ];
        
        $inspectionSelectFields = [
            'id',
            'item_id',
            'inspection_scheduled_date',
            'inspection_date',
            'status',
            'inspection_person',
            'details',
            'created_at'
        ];
        
        $scheduledInspections = Inspection::with($inspectionWithRelations)
            ->select($inspectionSelectFields)
            ->where('status', false)
            ->whereNotNull('inspection_scheduled_date') 
            ->orderBy('inspection_scheduled_date', 'asc')
            ->paginate(10);

        \Log::info("scheduledInspections");
        \Log::info($scheduledInspections->toArray());

        $scheduledInspections = $scheduledInspections->setCollection($this->imageService->setImagePath($scheduledInspections->getCollection()));



        $historyInspections = Inspection::with($inspectionWithRelations)
            ->select($inspectionSelectFields)
            ->where('status', true)
            ->orderBy('inspection_scheduled_date', 'asc')
            ->paginate(10);

        \Log::info("historyInspections");
        \Log::info($historyInspections->toArray());

        // ログ用
        $historyInspections->getCollection()->transform(function ($inspection) {
            if (is_null($inspection->item)) {
                \Log::info('Item is null for inspection ID: ' . $inspection->id);
            } else {
                \Log::info('Item is present for inspection ID: ' . $inspection->id . ', Item ID: ' . $inspection->item->id);
            }
            return $inspection;
        });

        $historyInspections = $historyInspections->setCollection($this->imageService->setImagePath($historyInspections->getCollection()));




        // ここからDisposal
        $disposalWithRelations = [
            'item' => function ($query) {
                $query->withTrashed()->with([
                    'category', 
                    'unit', 
                    'usageStatus', 
                    'locationOfUse', 
                    'storageLocation', 
                    'acquisitionMethod'
                ]);
            }
        ];

        $disposalSelectFields = [
            'id',
            'item_id',
            'disposal_scheduled_date',
            'disposal_date',
            'disposal_person',
            'details',
            'created_at'
        ];
        
        // クエリの再利用をやめる、干渉する可能性がある
        // 条件：廃棄されておらず、scheduled_dateが存在するレコード
        $scheduledDisposals = Disposal::with($disposalWithRelations)
            ->select($disposalSelectFields)
            ->orderBy('disposal_scheduled_date', 'asc')
            ->whereNotNull('disposal_scheduled_date') 
            ->whereHas('item', function ($query) {
                $query->whereNull('deleted_at');
            })->paginate(10);

        $scheduledDisposals = $scheduledDisposals->setCollection($this->imageService->setImagePath($scheduledDisposals->getCollection()));

        // 条件：廃棄されている
        $historyDisposals = Disposal::with($disposalWithRelations)
            ->select($disposalSelectFields)
            ->orderBy('disposal_scheduled_date', 'asc')
            ->whereHas('item', function ($query) {
                $query->whereNotNull('deleted_at');
            })->paginate(10);;
        
        $historyDisposals = $historyDisposals->setCollection($this->imageService->setImagePath($historyDisposals->getCollection()));


        return Inertia::render('InspectionAndDisposalItems/Index', [
            'scheduledInspections' => $scheduledInspections,
            'scheduledDisposals' => $scheduledDisposals,
            'historyInspections' => $historyInspections,
            'historyDisposals' => $historyDisposals,
        ]); 
    }
}
