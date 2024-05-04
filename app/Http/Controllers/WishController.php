<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishRequest;
use App\Http\Requests\UpdateWishRequest;
use Inertia\Inertia;

class WishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $wishes = Wish::with('category')->select(
            'id',
            'name',
            'category_id',
            'vendor',
            'location_of_use',
            'storage_location',
            'price',
            'reference_site_url',
            'applicant',
            'comment_from_applicant',
            'decision_status',
            'comment_from_administrator'
        )->get();

        return Inertia::render('Wishes/Index', [
            'wishes' => $wishes
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
    public function store(StoreWishRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Wish $wish)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wish $wish)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWishRequest $request, Wish $wish)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wish $wish)
    {
        //
    }
}
