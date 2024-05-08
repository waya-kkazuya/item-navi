<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImageTest;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ImageTestController extends Controller
{
    public function index()
    {
        $imageTests = ImageTest::select(
            'name',
            'file_name',
        )->get();

        return Inertia::render('ImageTests/Index', [
            'imageTests' => $imageTests,
            // 'sort' => $sortDirection
        ]);

    }
}
