<?php

namespace App\Http\Controllers\Client;

class ClientModuleController extends Controller
{
    public function markets()
    {
        return view('client.placeholder', ['title' => 'Markets']);
    }

    public function trading()
    {
        return view('client.placeholder', ['title' => 'Trading hub']);
    }

    public function portfolio()
    {
        return view('client.placeholder', ['title' => 'Portfolio']);
    }

    public function activity()
    {
        return view('client.placeholder', ['title' => 'Activity']);
    }
}
