<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveWallet
{
    /**
     * Keep one selected wallet environment for every authenticated client page.
     * An explicit account query is an intentional switch; otherwise preserve
     * the existing session selection while the client navigates the platform.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('account');

        if (in_array($requested, ['demo', 'real'], true)) {
            $request->session()->put('active_wallet', $requested);
        } elseif (!in_array($request->session()->get('active_wallet'), ['demo', 'real'], true)) {
            $request->session()->put('active_wallet', 'demo');
        }

        return $next($request);
    }
}
