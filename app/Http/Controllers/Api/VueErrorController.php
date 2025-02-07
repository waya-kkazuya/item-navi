<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VueErrorController extends Controller
{
    public function logError(Request $request)
    {
        $errorDetails = $request->all();
        Log::error('Vue Error:', $errorDetails);

        return response()->json(['status' => 'error logged']);
    }
}
