<?php

namespace App\UseCases\InspectionAndDisposalItem;

use App\Models\Inspection;
use App\Services\ImageService;

class ScheduledInspectionsUseCase
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
            ->whereHas('item', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->orderBy('inspection_scheduled_date', 'asc')
            ->paginate(10);

        return $scheduledInspections->setCollection($this->imageService->setImagePathInCollection($scheduledInspections->getCollection()));
    }
}
