<?php

namespace App\UseCases\InspectionAndDisposalItem;

use App\Models\Disposal;
use App\Services\ImageService;

class ScheduledDisposalUseCase
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
                    'acquisitionMethod',
                ]);
            },
        ];

        $disposalSelectFields = [
            'id',
            'item_id',
            'disposal_scheduled_date',
            'disposal_date',
            'disposal_person',
            'details',
            'created_at',
        ];

        $scheduledDisposals = Disposal::with($disposalWithRelations)
            ->select($disposalSelectFields)
            ->orderBy('disposal_scheduled_date', 'asc')
            ->whereNotNull('disposal_scheduled_date')
            ->whereHas('item', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->paginate(10);

        return $scheduledDisposals->setCollection($this->imageService->setImagePathInCollection($scheduledDisposals->getCollection()));
    }
}
