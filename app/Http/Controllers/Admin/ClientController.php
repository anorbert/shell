<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::query()
            ->where('role', 'client')
            ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();
                $query->where(function ($builder) use ($term) {
                    $builder->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.clients', compact('clients'));
    }
}
