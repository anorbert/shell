<?php

namespace App\Http\Controllers\Guests;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        return view('guests.home');
    }
}
