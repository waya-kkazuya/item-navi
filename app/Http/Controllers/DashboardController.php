<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use App\Models\Item;
use App\Models\Edithistory;
use Illuminate\Support\Facades\DB;
use App\UseCases\Dashboard\ItemsByTypeUseCase;
use App\UseCases\Dashboard\GroupedEditHistoriesUseCase;
use PhpParser\Node\Expr\List_;

class DashboardController extends Controller
{
    private $itemsByTypeUseCase;
    private $groupedEditHistories;

    const CATEGORY = 1;
    const LOCATION_OF_USE = 2;

    public function __construct(
        ItemsByTypeUseCase $itemsByTypeUseCase,
        GroupedEditHistoriesUseCase $groupedEditHistories
    ) {
        $this->itemsByTypeUseCase = $itemsByTypeUseCase;
        $this->groupedEditHistories = $groupedEditHistories;
    }

    public function index(Request $request)
    {
        Gate::authorize('staff-higher');

        $type = $request->input('type', self::CATEGORY);
        List('allItems' => $allItems, 'itemsByType' => $itemsByType) = $this->itemsByTypeUseCase->handle($type);

        $groupedEditHistories = $this->groupedEditHistories->handle();

        return Inertia::render('Dashboard', [
            'allItems' => $allItems,
            'itemsByType' => $itemsByType,
            'groupedEdithistories' => $groupedEditHistories,
            'type' => $type
        ]); 
    }
}
