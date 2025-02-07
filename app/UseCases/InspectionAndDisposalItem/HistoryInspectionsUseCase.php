<?php

namespace App\UseCases\InspectionAndDisposalItem;

use App\Models\Inspection;
use App\Services\ImageService;

class HistoryInspectionsUseCase
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function handle()
    {
        $inspectionWithRelations = [
            'item.category',
            'item.unit',
            'item.usageStatus',
            'item.locationOfUse',
            'item.storageLocation',
            'item.acquisitionMethod',
        ];

        $inspectionSelectFields = [
            'id',
            'item_id',
            'inspection_scheduled_date',
            'inspection_date',
            'status',
            'inspection_person',
            'details',
            'created_at',
        ];

        $historyInspections = Inspection::with($inspectionWithRelations)
            ->select($inspectionSelectFields)
            ->where('status', true)
            ->orderBy('inspection_scheduled_date', 'asc')
            ->paginate(10);

        return $historyInspections->setCollection($this->imageService->setImagePathInCollection($historyInspections->getCollection()));
    }
}
