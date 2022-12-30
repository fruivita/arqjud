<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Permissao;
use Database\Seeders\PerfilPermissaoSeeder;
use Database\Seeders\PerfilSeeder;
use Database\Seeders\PermissaoSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar perfis duplicados, isto é, com mesmo nome ou slug', function (string $campo, mixed $valor) {
    expect(
        fn () => Perfil::factory(2)->create([$campo => $valor])
    )->toThrow(QueryException::class, 'Duplicate entry');
})->with([
    ['nome', 'foo'],
    ['slug', 'foo'],
]);

test('lança exception ao tentar criar perfil com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Perfil::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['nome',      null,             'cannot be null'],           // obrigatório
    ['slug',      Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['slug',      null,             'cannot be null'],           // obrigatório
    ['poder',     65536,            'Out of range'],             // máximo 65535
    ['poder',     null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 50 caracteres
]);

test('lança exception ao tentar relacionar perfil e permissão duplicados', function () {
    $perfil = Perfil::factory()->create();
    $permissao = Permissao::factory()->create();

    DB::table('perfil_permissao')->insert([
        'perfil_id' => $perfil->id,
        'permissao_id' => $permissao->id,
    ]);

    expect(
        fn () => DB::table('perfil_permissao')->insert([
            'perfil_id' => $perfil->id,
            'permissao_id' => $permissao->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

// Caminho feliz
test('campos opcionais estão definidos', function () {
    Perfil::factory()->create(['descricao' => null]);

    expect(Perfil::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Perfil::factory()->create([
        'nome' => Str::random(50),
        'slug' => Str::random(50),
        'poder' => 65535,
        'descricao' => Str::random(255),
    ]);

    expect(Perfil::count())->toBe(1);
});

test('slugs dos perfis estão definidos', function () {
    expect(Perfil::ADMINISTRADOR)->toBe('administrador')
        ->and(Perfil::GERENTE_NEGOCIO)->toBe('gerente-de-negocio')
        ->and(Perfil::OPERADOR)->toBe('operador')
        ->and(Perfil::OBSERVADOR)->toBe('observador')
        ->and(Perfil::PADRAO)->toBe('padrao');
});

test('um perfil possui muitas permissões', function () {
    Perfil::factory()->hasPermissoes(3)->create();

    $perfil = Perfil::with('permissoes')->first();

    expect($perfil->permissoes)->toHaveCount(3);
});

test('um perfil possui muitos usuários', function () {
    Perfil::factory()->hasUsuarios(3)->create();

    $perfil = Perfil::with('usuarios')->first();

    expect($perfil->usuarios)->toHaveCount(3);
});

test('perfil administrador possui suas permissões iniciais definidas', function () {
    $this->seed([
        PerfilSeeder::class,
        PermissaoSeeder::class,
        PerfilPermissaoSeeder::class,
    ]);

    $permissoes = Perfil::query()
        ->with('permissoes')
        ->firstWhere('slug', Perfil::ADMINISTRADOR)
        ->permissoes
        ->pluck('slug');

    expect($permissoes->toArray())->toMatchArray([
        Permissao::CONFIGURACAO_VIEW,
        Permissao::CONFIGURACAO_UPDATE,
        Permissao::DELEGACAO_VIEW_ANY,
        Permissao::DELEGACAO_CREATE,
        Permissao::IMPORTACAO_CREATE,
        Permissao::LOG_VIEW_ANY,
        Permissao::LOG_VIEW,
        Permissao::LOG_DELETE,
        Permissao::ATIVIDADE_VIEW_ANY,
        Permissao::ATIVIDADE_VIEW,
        Permissao::ATIVIDADE_DELETE,
        Permissao::PERMISSAO_VIEW_ANY,
        Permissao::PERMISSAO_VIEW,
        Permissao::PERMISSAO_UPDATE,
        Permissao::PERFIL_VIEW_ANY,
        Permissao::PERFIL_VIEW,
        Permissao::PERFIL_UPDATE,
        Permissao::USUARIO_VIEW_ANY,
        Permissao::USUARIO_VIEW,
        Permissao::USUARIO_UPDATE,
        Permissao::LOCALIDADE_VIEW_ANY,
        Permissao::LOCALIDADE_VIEW,
        Permissao::LOCALIDADE_CREATE,
        Permissao::LOCALIDADE_UPDATE,
        Permissao::LOCALIDADE_DELETE,
        Permissao::PREDIO_VIEW_ANY,
        Permissao::PREDIO_VIEW,
        Permissao::PREDIO_CREATE,
        Permissao::PREDIO_UPDATE,
        Permissao::PREDIO_DELETE,
        Permissao::ANDAR_VIEW_ANY,
        Permissao::ANDAR_VIEW,
        Permissao::ANDAR_CREATE,
        Permissao::ANDAR_UPDATE,
        Permissao::ANDAR_DELETE,
        Permissao::SALA_VIEW_ANY,
        Permissao::SALA_VIEW,
        Permissao::SALA_CREATE,
        Permissao::SALA_UPDATE,
        Permissao::SALA_DELETE,
        Permissao::ESTANTE_VIEW_ANY,
        Permissao::ESTANTE_VIEW,
        Permissao::ESTANTE_CREATE,
        Permissao::ESTANTE_UPDATE,
        Permissao::ESTANTE_DELETE,
        Permissao::PRATELEIRA_VIEW_ANY,
        Permissao::PRATELEIRA_VIEW,
        Permissao::PRATELEIRA_CREATE,
        Permissao::PRATELEIRA_UPDATE,
        Permissao::PRATELEIRA_DELETE,
        Permissao::CAIXA_VIEW_ANY,
        Permissao::CAIXA_VIEW,
        Permissao::CAIXA_CREATE,
        Permissao::CAIXA_UPDATE,
        Permissao::CAIXA_DELETE,
        Permissao::VOLUME_CAIXA_VIEW_ANY,
        Permissao::VOLUME_CAIXA_VIEW,
        Permissao::VOLUME_CAIXA_CREATE,
        Permissao::VOLUME_CAIXA_UPDATE,
        Permissao::VOLUME_CAIXA_DELETE,
        Permissao::PROCESSO_VIEW_ANY,
        Permissao::PROCESSO_VIEW,
        Permissao::PROCESSO_CREATE,
        Permissao::PROCESSO_UPDATE,
        Permissao::PROCESSO_DELETE,
        Permissao::MOVER_PROCESSO_CREATE,
        Permissao::SOLICITACAO_VIEW_ANY,
        Permissao::SOLICITACAO_CREATE,
        Permissao::SOLICITACAO_UPDATE,
        Permissao::SOLICITACAO_DELETE,
        Permissao::SOLICITACAO_EXTERNA_VIEW_ANY,
        Permissao::SOLICITACAO_EXTERNA_CREATE,
        Permissao::SOLICITACAO_EXTERNA_DELETE,
        Permissao::GUIA_VIEW_ANY,
        Permissao::GUIA_VIEW,
    ]);
});
