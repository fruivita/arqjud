<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 */

use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\Solicitacao\SolicitacaoResource;
use App\Http\Resources\Usuario\UsuarioOnlyResource;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $usuario = Usuario::factory()->create();
    Auth::login($usuario);

    $this->solicitacao = Solicitacao::factory()->solicitada()->create(['destino_id' => $usuario->lotacao_id]);
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('retorna os campos principais e as rotas autorizadas do modelo', function () {
    concederPermissao([Permissao::SOLICITACAO_EXTERNA_DELETE, Permissao::SOLICITACAO_DELETE]);

    $resource = SolicitacaoResource::make($this->solicitacao);

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => solicitacaoApi($this->solicitacao)
            + [
                'links' => [
                    'delete' => route('atendimento.solicitar-processo.destroy', $this->solicitacao),
                    'externo_delete' => route('solicitacao.destroy', $this->solicitacao),
                ],
            ],
    ]);
});

test('retorna o processo, o solicitante, o recebedor, o remetente, o rearquivador e o destino se houver o eager load da propriedade e sem os links se não houver rota autorizada', function () {
    $resource = SolicitacaoResource::make($this->solicitacao->load('processo', 'solicitante', 'recebedor', 'remetente', 'rearquivador', 'destino'));

    expect($resource->response()->getData(true))->toMatchArray([
        'data' => solicitacaoApi($this->solicitacao)
            + ['processo' => ProcessoOnlyResource::make($this->solicitacao->processo)->resolve()]
            + ['solicitante' => UsuarioOnlyResource::make($this->solicitacao->solicitante)->resolve()]
            + ['recebedor' => null]
            + ['remetente' => null]
            + ['rearquivador' => null]
            + ['destino' => lotacaoApi($this->solicitacao->destino)]
            + ['links' => []],
    ]);
});

test('retorna o resource vazio se o modelo for nulo', function () {
    expect(SolicitacaoResource::make(null)->resolve())->toBeEmpty();
});
