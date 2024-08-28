<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use App\Models\Item;

class DashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('staff-higher');

        $itemsByCategory = Item::with('category')->get()->groupBy('category.name');

        // 空でも送れる
        return Inertia::render('Dashboard', [
            'itemsByCategory' => $itemsByCategory,
        ]); 

    }
}
