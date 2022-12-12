<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\Solicitacao\SolicitacaoResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Permissao;
use App\Models\Solicitacao;
use FruiVita\Corporativo\Models\Lotacao;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    $usuario =  login();

    $this->solicitacao = Solicitacao::factory()->solicitada()->create(['lotacao_destinataria_id' => $usuario->lotacao_id]);

    $this->solicitacao_api = [
        'id' => $this->solicitacao->id,
        'solicitada_em' => $this->solicitacao->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
        'entregue_em' => null,
        'devolvida_em' => null,
        'por_guia' => $this->solicitacao->por_guia,
        'descricao' => $this->solicitacao->descricao,
        'status' => $this->solicitacao->status,
        'processo_id' => $this->solicitacao->processo_id,
        'solicitante_id' => $this->solicitacao->solicitante_id,
        'recebedor_id' => $this->solicitacao->recebedor_id,
        'remetente_id' => $this->solicitacao->remetente_id,
        'rearquivador_id' => $this->solicitacao->rearquivador_id,
        'lotacao_destinataria_id' => $this->solicitacao->lotacao_destinataria_id,
        'guia_id' => $this->solicitacao->guia_id,
    ];
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::SOLICITACAO_EXTERNA_DELETE]);

    $resource = SolicitacaoResource::make($this->solicitacao);

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->solicitacao_api
            + [
                'links' => [
                    'externo_delete' => route('solicitacao.destroy', $this->solicitacao),
                ],
            ],
    ]);
});

test('retorna o processo, o solicitante, o recebedor, o remetente, o rearquivador, a lotação destinatária se houver o eager load da propriedade e sem os links se não houver rota autorizada', function () {
    $resource = SolicitacaoResource::make($this->solicitacao->load('processo', 'solicitante', 'recebedor', 'remetente', 'rearquivador', 'lotacaoDestinataria'));

    expect($resource->response()->getData(true))->toBe([
        'data' => $this->solicitacao_api
            + ['processo' => ProcessoOnlyResource::make($this->solicitacao->processo)->resolve()]
            + ['solicitante' => UsuarioOnlyResource::make($this->solicitacao->solicitante)->resolve()]
            + ['recebedor' => null]
            + ['remetente' => null]
            + ['rearquivador' => null]
            + ['lotacao_destinataria' => lotacaoApi($this->solicitacao->lotacaoDestinataria)]
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(SolicitacaoResource::make(null)->resolve())->toBeEmpty();
});

function lotacaoApi(Lotacao $lotacao)
{
    return [
        'id' => $lotacao->id,
        'nome' => $lotacao->nome,
        'sigla' => mb_strtoupper($lotacao->sigla),
        'lotacao_pai_id' => $lotacao->lotacao_pai_id,
    ];
}
