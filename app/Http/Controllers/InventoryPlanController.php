<?php

namespace App\Http\Controllers;

use App\Models\InventoryPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryPlanRequest;
use App\Http\Requests\UpdateInventoryPlanRequest;
use Inertia\Inertia;

class InventoryPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventoryPlans = InventoryPlan::select('name','start_date', 'end_date', 'status')->get();

        // dd($inventoryPlans);

        return Inertia::render('InventoryPlans/Index', [
            'inventoryPlans' => $inventoryPlans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryPlanRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryPlan $inventoryPlan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryPlan $inventoryPlan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryPlanRequest $request, InventoryPlan $inventoryPlan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryPlan $inventoryPlan)
    {
        //
    }
}
