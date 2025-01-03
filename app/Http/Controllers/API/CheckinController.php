<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckinController extends Controller
{
    function checkin(Request $request) : void {
        Log::info(($request->all()));
    }
}
