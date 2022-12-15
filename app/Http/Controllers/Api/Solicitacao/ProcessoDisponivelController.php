<?php

namespace App\Http\Controllers\Api\Solicitacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Solicitacao\ShowProcessoDisponivelRequest;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Models\Processo;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class ProcessoDisponivelController extends Controller
{
    /**
     * Processo disponível para solicitação.
     *
     * @param  \App\Http\Requests\Api\Solicitacao\ShowProcessoDisponivelRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ShowProcessoDisponivelRequest $request)
    {
        return response()->json([
            'processo' => ProcessoOnlyResource::make(
                Processo::firstWhere('numero', $request->input('numero'))
            ),
        ]);
    }
}
