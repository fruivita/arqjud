<?php

namespace App\Http\Controllers\Movimentacao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Movimentacao\StoreMoveProcessoEntreCaixaRequest;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Traits\ComFeedback;
use App\Models\Localidade;
use App\Models\VolumeCaixa;
use Illuminate\Support\Arr;
use Inertia\Inertia;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://inertiajs.com/
 */
class MoveProcessoEntreCaixaController extends Controller
{
    use ComFeedback;

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize(Policy::MoverProcessoCreate->value);

        return Inertia::render('Movimentacao/EntreCaixa/Create', [
            'localidades' => fn () => LocalidadeOnlyResource::collection(Localidade::all()),
            'links' => fn () => [
                'search' => [
                    'processo' => route('api.movimentacao.processo.show'),
                    'caixa' => route('api.caixa.show'),
                ],
                'store' => route('movimentacao.entre-caixas.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Movimentacao\StoreMoveProcessoEntreCaixaRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreMoveProcessoEntreCaixaRequest $request)
    {
        $destino = VolumeCaixa::findOrFail($request->integer('volume_id'));

        $salvo = $destino->moverProcessos(
            Arr::pluck($request->input('processos'), 'numero'),
        );

        return back()->with($this->feedback($salvo));
    }
}
