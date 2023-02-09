<?php

namespace App\Http\Controllers\Api\Movimentacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Movimentacao\ShowProcessoMovimentavelRequest;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Models\Processo;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class ProcessoMovimentavelController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Http\Requests\Api\Movimentacao\ShowProcessoMovimentavelRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ShowProcessoMovimentavelRequest $request)
    {
        return response()->json([
            'processo' => ProcessoOnlyResource::make(
                Processo::where('numero', $request->input('numero'))->firstOrFail()
            ),
        ]);
    }
}
