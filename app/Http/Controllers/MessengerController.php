<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessengerController extends Controller
{
    function search_member(Request $request) : mixed {
        return auth()->user()->currentTeam->users()->whereNot('users.id', auth()->user()->id)->where('name', 'like', '%' . $request->input('search') . '%')->get();
    }
}
