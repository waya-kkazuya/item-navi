<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Edithistory;

class AnalysisController extends Controller
{
    public function index()
    {   
        $startDate = '2024-05-09';
        $endDate = '2024-05-11';

        $period = Edithistory::betweenDate($startDate, $endDate)
                ->orderBy('edited_at')
                ->paginate(10);

        return Inertia::render('Analysis');
    }
}
