<?php

namespace App\Http\Controllers\Api\Caixa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Caixa\ShowCaixaRequest;
use App\Http\Resources\Caixa\CaixaOnlyResource;
use App\Models\Caixa;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class CaixaController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ShowCaixaRequest $request)
    {
        return response()->json([
            'caixa' => CaixaOnlyResource::make(
                Caixa::with('volumes')
                    ->where('numero', $request->integer('numero'))
                    ->where('ano', $request->integer('ano'))
                    ->where('guarda_permanente', $request->boolean('guarda_permanente'))
                    ->where('localidade_criadora_id', $request->integer('localidade_criadora_id'))
                    ->when(
                        $request->input('complemento'),
                        function ($query, $complemento) {
                            return $query->where('complemento', $complemento);
                        },
                        function ($query) {
                            return $query->whereNull('complemento');
                        }
                    )
                    ->firstOrFail()
            ),
        ]);
    }
}
