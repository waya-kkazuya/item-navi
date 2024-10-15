<?php

namespace App\UseCases\InspectionAndDisposalItem;

use App\Models\Disposal;
use App\Services\ImageService;

class HistoryDisposalUseCase
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function handle()
    {
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

        
        $historyDisposals = Disposal::with($disposalWithRelations)
            ->select($disposalSelectFields)
            ->orderBy('disposal_scheduled_date', 'asc')
            ->whereHas('item', function ($query) {
                $query->whereNotNull('deleted_at'); //廃棄済みのデータ
            })
            ->paginate(10);

        return $historyDisposals->setCollection($this->imageService->setImagePath($historyDisposals->getCollection()));
    }
}
