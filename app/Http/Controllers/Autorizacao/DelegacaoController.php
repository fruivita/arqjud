<?php

namespace App\Http\Controllers\Autorizacao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Traits\ComFeedback;
use App\Models\Usuario;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class DelegacaoController extends Controller
{
    use ComFeedback;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Usuario  $delegado
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Usuario $delegado)
    {
        $this->authorize(Policy::DelegacaoCreate->value, $delegado);

        $salvo = auth()->user()->delegar($delegado);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Usuario  $delegado
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Usuario $delegado)
    {
        $this->authorize(Policy::DelegacaoDelete->value, $delegado);

        $salvo = $delegado->revogarDelegacao();

        return back()->with($this->feedback($salvo));
    }
}
