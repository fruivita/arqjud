<?php

namespace App\Http\Controllers\Api\Solicitacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Solicitacao\ShowAutorizadaParaRecebedorRequest;
use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Solicitacao;
use App\Models\Usuario;

class AutorizadaParaRecebedorController extends Controller
{
    /**
     * Retorna as solicitações de processo possíveis de recebimento pelo
     * usuário recebedor, bem como os dados básicos deste usuário.
     *
     * Regra de negócio: as solicitações autorizadas para um usuário são
     * aquelas destinadas a sua lotação.
     *
     * @param  \App\Http\Requests\Api\Solicitacao\ShowAutorizadaParaRecebedorRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ShowAutorizadaParaRecebedorRequest $request)
    {
        $recebedor = Usuario::query()
            ->withOnly(['lotacao'])
            ->where('username', $request->input('recebedor'))
            ->firstOrFail();

        $solicitacoes = Solicitacao::with(['processo', 'solicitante', 'lotacaoDestinataria'])
            ->orWhereBelongsTo($recebedor->lotacao, 'lotacaoDestinataria')
            ->solicitadas()
            ->get();

        return response()->json([
            'recebedor' => UsuarioOnlyResource::make($recebedor),
            'solicitacoes' => SolicitacaoOnlyResource::collection($solicitacoes),
        ]);
    }
}
