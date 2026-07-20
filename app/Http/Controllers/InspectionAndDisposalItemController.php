<?php

namespace App\Http\Controllers;

use App\Services\ImageService;
use App\UseCases\InspectionAndDisposalItem\HistoryDisposalUseCase;
use App\UseCases\InspectionAndDisposalItem\HistoryInspectionsUseCase;
use App\UseCases\InspectionAndDisposalItem\ScheduledDisposalUseCase;
use App\UseCases\InspectionAndDisposalItem\ScheduledInspectionsUseCase;
use Inertia\Inertia;

class InspectionAndDisposalItemController extends Controller
{
    protected $imageService;

    protected $scheduledInspectionsUseCase;

    protected $historyInspectionsUseCase;

    protected $scheduledDisposalUseCase;

    protected $historyDisposalUseCase;

    public function __construct(
        ImageService $imageService,
        ScheduledInspectionsUseCase $scheduledInspectionsUseCase,
        HistoryInspectionsUseCase $historyInspectionsUseCase,
        ScheduledDisposalUseCase $scheduledDisposalUseCase,
        HistoryDisposalUseCase $historyDisposalUseCase
    ) {
        $this->imageService = $imageService;
        $this->scheduledInspectionsUseCase = $scheduledInspectionsUseCase;
        $this->historyInspectionsUseCase = $historyInspectionsUseCase;
        $this->scheduledDisposalUseCase = $scheduledDisposalUseCase;
        $this->historyDisposalUseCase = $historyDisposalUseCase;
    }

    public function index()
    {
        $scheduledInspections = $this->scheduledInspectionsUseCase->handle();
        $historyInspections = $this->historyInspectionsUseCase->handle();
        $scheduledDisposals = $this->scheduledDisposalUseCase->handle();
        $historyDisposals = $this->historyDisposalUseCase->handle();

        return Inertia::render('InspectionAndDisposalItems/Index', [
            'scheduledInspections' => $scheduledInspections,
            'scheduledDisposals' => $scheduledDisposals,
            'historyInspections' => $historyInspections,
            'historyDisposals' => $historyDisposals,
        ]);
    }
}
