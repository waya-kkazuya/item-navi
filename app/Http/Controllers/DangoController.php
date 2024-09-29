<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Dango;
use PDF;

class DangoController extends Controller
{
    public function dango(){
        return view('dango');
    }
    
    public function pdf(){
        $dangos=Dango::all();
        $pdf=PDF::loadView('dango_pdf', compact('dangos'));
        return $pdf->download('dangofile.pdf');
    }
}
