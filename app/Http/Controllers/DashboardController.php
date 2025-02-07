<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\UseCases\Dashboard\GroupedEditHistoriesUseCase;
use App\UseCases\Dashboard\ItemsByTypeUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private $itemsByTypeUseCase;
    private $groupedEditHistories;

    public function __construct(
        ItemsByTypeUseCase $itemsByTypeUseCase,
        GroupedEditHistoriesUseCase $groupedEditHistories
    ) {
        $this->itemsByTypeUseCase   = $itemsByTypeUseCase;
        $this->groupedEditHistories = $groupedEditHistories;
    }

    public function index(Request $request)
    {
        Gate::authorize('staff-higher');

        Log::info('DashboardController index method called');

        try {
            $type                                                        = $request->input('type', 'category');
            list('allItems' => $allItems, 'itemsByType' => $itemsByType) = $this->itemsByTypeUseCase->handle($type);

            $groupedEditHistories = $this->groupedEditHistories->handle();

            Log::info('DashboardController index method succeeded');

            return Inertia::render('Dashboard', [
                'allItems'             => $allItems,
                'itemsByType'          => $itemsByType,
                'groupedEdithistories' => $groupedEditHistories,
                'type'                 => $type,
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController index method failed', [
                'error'   => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
        }
    }
}
