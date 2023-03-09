<?php

namespace App\Http\Controllers\Api\Solicitacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Solicitacao\ShowSolicitanteRequest;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Usuario;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class SolicitanteController extends Controller
{
    /**
     * Usuário solicitante do processo com a sua lotação.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ShowSolicitanteRequest $request)
    {
        return response()->json([
            'solicitante' => UsuarioOnlyResource::make(
                Usuario::query()
                    ->withOnly(['lotacao'])
                    ->where('matricula', $request->input('solicitante'))
                    ->firstOrFail()
            ),
        ]);
    }
}
