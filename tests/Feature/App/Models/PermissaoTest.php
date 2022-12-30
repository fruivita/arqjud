<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permissao;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar permissões duplicadas, isto é, com mesmo nome ou slug', function (string $campo, mixed $valor) {
    expect(
        fn () => Permissao::factory(2)->create([$campo => $valor])
    )->toThrow(QueryException::class, 'Duplicate entry');
})->with([
    ['nome', 'foo'],
    ['slug', 'foo'],
]);

test('lança exception ao tentar criar permissão com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Permissao::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome', Str::random(51),       'Data too long for column'], // máximo 50 caracteres
    ['nome', null,                  'cannot be null'],           // obrigatório
    ['slug',      Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['slug',      null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Caminho feliz
test('campos opcionais estão definidos', function () {
    Permissao::factory()->create(['descricao' => null]);

    expect(Permissao::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Permissao::factory()->create([
        'nome' => Str::random(50),
        'slug' => Str::random(50),
        'descricao' => Str::random(255),
    ]);

    expect(Permissao::count())->toBe(1);
});

test('slugs das permissões estão definidas', function () {
    expect(Permissao::CONFIGURACAO_VIEW)->toBe('configuracao_view')
        ->and(Permissao::CONFIGURACAO_UPDATE)->toBe('configuracao_update')
        ->and(Permissao::DELEGACAO_VIEW_ANY)->toBe('delegacao_view_any')
        ->and(Permissao::DELEGACAO_CREATE)->toBe('delegacao_create')
        ->and(Permissao::IMPORTACAO_CREATE)->toBe('importacao_create')
        ->and(Permissao::LOG_VIEW_ANY)->toBe('log_view_any')
        ->and(Permissao::LOG_VIEW)->toBe('log_view')
        ->and(Permissao::LOG_DELETE)->toBe('log_delete')
        ->and(Permissao::ATIVIDADE_VIEW_ANY)->toBe('atividade_view_any')
        ->and(Permissao::ATIVIDADE_VIEW)->toBe('atividade_view')
        ->and(Permissao::ATIVIDADE_DELETE)->toBe('atividade_delete')
        ->and(Permissao::PERMISSAO_VIEW_ANY)->toBe('permissao_view_any')
        ->and(Permissao::PERMISSAO_VIEW)->toBe('permissao_view')
        ->and(Permissao::PERMISSAO_UPDATE)->toBe('permissao_update')
        ->and(Permissao::PERFIL_VIEW_ANY)->toBe('perfil_view_any')
        ->and(Permissao::PERFIL_VIEW)->toBe('perfil_view')
        ->and(Permissao::PERFIL_UPDATE)->toBe('perfil_update')
        ->and(Permissao::USUARIO_VIEW_ANY)->toBe('usuario_view_any')
        ->and(Permissao::USUARIO_VIEW)->toBe('usuario_view')
        ->and(Permissao::USUARIO_UPDATE)->toBe('usuario_update')
        ->and(Permissao::LOCALIDADE_VIEW_ANY)->toBe('localidade_view_any')
        ->and(Permissao::LOCALIDADE_VIEW)->toBe('localidade_view')
        ->and(Permissao::LOCALIDADE_CREATE)->toBe('localidade_create')
        ->and(Permissao::LOCALIDADE_UPDATE)->toBe('localidade_update')
        ->and(Permissao::LOCALIDADE_DELETE)->toBe('localidade_delete')
        ->and(Permissao::PREDIO_VIEW_ANY)->toBe('predio_view_any')
        ->and(Permissao::PREDIO_VIEW)->toBe('predio_view')
        ->and(Permissao::PREDIO_CREATE)->toBe('predio_create')
        ->and(Permissao::PREDIO_UPDATE)->toBe('predio_update')
        ->and(Permissao::PREDIO_DELETE)->toBe('predio_delete')
        ->and(Permissao::ANDAR_VIEW_ANY)->toBe('andar_view_any')
        ->and(Permissao::ANDAR_VIEW)->toBe('andar_view')
        ->and(Permissao::ANDAR_CREATE)->toBe('andar_create')
        ->and(Permissao::ANDAR_UPDATE)->toBe('andar_update')
        ->and(Permissao::ANDAR_DELETE)->toBe('andar_delete')
        ->and(Permissao::SALA_VIEW_ANY)->toBe('sala_view_any')
        ->and(Permissao::SALA_VIEW)->toBe('sala_view')
        ->and(Permissao::SALA_CREATE)->toBe('sala_create')
        ->and(Permissao::SALA_UPDATE)->toBe('sala_update')
        ->and(Permissao::SALA_DELETE)->toBe('sala_delete')
        ->and(Permissao::ESTANTE_VIEW_ANY)->toBe('estante_view_any')
        ->and(Permissao::ESTANTE_VIEW)->toBe('estante_view')
        ->and(Permissao::ESTANTE_CREATE)->toBe('estante_create')
        ->and(Permissao::ESTANTE_UPDATE)->toBe('estante_update')
        ->and(Permissao::ESTANTE_DELETE)->toBe('estante_delete')
        ->and(Permissao::PRATELEIRA_VIEW_ANY)->toBe('prateleira_view_any')
        ->and(Permissao::PRATELEIRA_VIEW)->toBe('prateleira_view')
        ->and(Permissao::PRATELEIRA_CREATE)->toBe('prateleira_create')
        ->and(Permissao::PRATELEIRA_UPDATE)->toBe('prateleira_update')
        ->and(Permissao::PRATELEIRA_DELETE)->toBe('prateleira_delete')
        ->and(Permissao::CAIXA_VIEW_ANY)->toBe('caixa_view_any')
        ->and(Permissao::CAIXA_VIEW)->toBe('caixa_view')
        ->and(Permissao::CAIXA_CREATE)->toBe('caixa_create')
        ->and(Permissao::CAIXA_UPDATE)->toBe('caixa_update')
        ->and(Permissao::CAIXA_DELETE)->toBe('caixa_delete')
        ->and(Permissao::VOLUME_CAIXA_VIEW_ANY)->toBe('volume_caixa_view_any')
        ->and(Permissao::VOLUME_CAIXA_VIEW)->toBe('volume_caixa_view')
        ->and(Permissao::VOLUME_CAIXA_CREATE)->toBe('volume_caixa_create')
        ->and(Permissao::VOLUME_CAIXA_UPDATE)->toBe('volume_caixa_update')
        ->and(Permissao::VOLUME_CAIXA_DELETE)->toBe('volume_caixa_delete')
        ->and(Permissao::PROCESSO_VIEW_ANY)->toBe('processo_view_any')
        ->and(Permissao::PROCESSO_VIEW)->toBe('processo_view')
        ->and(Permissao::PROCESSO_CREATE)->toBe('processo_create')
        ->and(Permissao::PROCESSO_UPDATE)->toBe('processo_update')
        ->and(Permissao::PROCESSO_DELETE)->toBe('processo_delete')
        ->and(Permissao::MOVER_PROCESSO_CREATE)->toBe('mover_processo_create')
        ->and(Permissao::SOLICITACAO_VIEW_ANY)->toBe('solicitacao_view_any')
        ->and(Permissao::SOLICITACAO_CREATE)->toBe('solicitacao_create')
        ->and(Permissao::SOLICITACAO_UPDATE)->toBe('solicitacao_update')
        ->and(Permissao::SOLICITACAO_DELETE)->toBe('solicitacao_delete')
        ->and(Permissao::SOLICITACAO_EXTERNA_VIEW_ANY)->toBe('solicitacao_externa_view_any')
        ->and(Permissao::SOLICITACAO_EXTERNA_CREATE)->toBe('solicitacao_externa_create')
        ->and(Permissao::SOLICITACAO_EXTERNA_DELETE)->toBe('solicitacao_externa_delete')
        ->and(Permissao::GUIA_VIEW_ANY)->toBe('guia_view_any')
        ->and(Permissao::GUIA_VIEW)->toBe('guia_view');
});

test('uma permissão pode ser usada em muitos perfis', function () {
    Permissao::factory()->hasPerfis(3)->create();

    $permissao = Permissao::with('perfis')->first();

    expect($permissao->perfis)->toHaveCount(3);
});
