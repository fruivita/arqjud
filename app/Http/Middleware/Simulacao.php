<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @see https://laravel.com/docs/middleware
 */
class Simulacao
{
    /**
     * Handle an incoming request.
     *
     * Verifica se o usuário está sendo simulado.
     *
     * @param \Illuminate\Http\Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('simulado')) {
            Auth::onceUsingID(session()->get('simulado')->getAuthIdentifier());
        }

        return $next($request);
    }
}
