<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilPermissaoSeeder;
use Database\Seeders\PerfilSeeder;
use Database\Seeders\PermissaoSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
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
        Permissao::IMPORTACAO_CREATE,
        Permissao::LOG_VIEW_ANY,
        Permissao::LOG_VIEW,
        Permissao::ATIVIDADE_VIEW_ANY,
        Permissao::ATIVIDADE_VIEW,
        Permissao::PERMISSAO_VIEW_ANY,
        Permissao::PERMISSAO_VIEW,
        Permissao::PERMISSAO_UPDATE,
        Permissao::PERFIL_VIEW_ANY,
        Permissao::PERFIL_VIEW,
        Permissao::PERFIL_CREATE,
        Permissao::PERFIL_UPDATE,
        Permissao::PERFIL_DELETE,
        Permissao::USUARIO_VIEW_ANY,
        Permissao::USUARIO_VIEW,
        Permissao::USUARIO_UPDATE,
        Permissao::LOTACAO_VIEW_ANY,
        Permissao::LOTACAO_UPDATE,
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
        Permissao::TIPO_PROCESSO_VIEW_ANY,
        Permissao::TIPO_PROCESSO_VIEW,
        Permissao::TIPO_PROCESSO_CREATE,
        Permissao::TIPO_PROCESSO_UPDATE,
        Permissao::TIPO_PROCESSO_DELETE,
        Permissao::CAIXA_VIEW_ANY,
        Permissao::CAIXA_VIEW,
        Permissao::CAIXA_CREATE,
        Permissao::CAIXA_UPDATE,
        Permissao::CAIXA_DELETE,
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

test('retorna os perfis disponíveis para atribuição utilizando o escopo disponiveisParaAtribuicao', function () {
    $this->seed([PerfilSeeder::class]);

    $perfil = Perfil::firstWhere('slug', Perfil::GERENTE_NEGOCIO);

    $perfis = Perfil::where('poder', '<=', $perfil->poder)->pluck('id');

    $usuario = Usuario::factory()->for($perfil, 'perfil')->create();

    Auth::login($usuario);

    $disponiveis = Perfil::disponiveisParaAtribuicao()->pluck('id');

    expect($disponiveis)->toMatchArray($perfis->toArray());
});

test('perfis estão na ordem hierarquica correta', function () {
    // perfil com maior poder possui maiores permissões na aplicação
    $this->seed([PerfilSeeder::class]);

    $perfis = Perfil::all();

    expect($perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->poder)->toBeGreaterThan($perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->poder)
        ->and($perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->poder)->toBeGreaterThan($perfis->firstWhere('slug', Perfil::OPERADOR)->poder)
        ->and($perfis->firstWhere('slug', Perfil::OPERADOR)->poder)->toBeGreaterThan($perfis->firstWhere('slug', Perfil::OBSERVADOR)->poder)
        ->and($perfis->firstWhere('slug', Perfil::OBSERVADOR)->poder)->toBeGreaterThan($perfis->firstWhere('slug', Perfil::PADRAO)->poder);
});

test('retorna os perfis pelo escopo search que busca a partir do início do texto no nome, slug ou poder', function (string $termo, int $quantidade) {
    Perfil::factory()->create([
        'nome' => 'eeeeffff',
        'poder' => '11111',
        'slug' => 'aaaabbbb',
    ]);
    Perfil::factory()->create([
        'nome' => 'gggghhhh',
        'poder' => '11122',
        'slug' => 'ccccdddd',
    ]);

    $query = Perfil::query();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 2],
    ['eeee', 1],
    ['111', 2],
    ['cccc', 1],
    ['bar', 0],
]);

test('método administrador retorna o perfil administrador', function () {
    $this->seed([PerfilSeeder::class]);

    $adm = Perfil::administrador();

    expect($adm)->toBeInstanceOf(Perfil::class)
        ->and($adm->slug)->toBe(Perfil::ADMINISTRADOR);
});

test('método gerenteNegocio retorna o perfil gerente de negócio', function () {
    $this->seed([PerfilSeeder::class]);

    $adm = Perfil::gerenteNegocio();

    expect($adm)->toBeInstanceOf(Perfil::class)
        ->and($adm->slug)->toBe(Perfil::GERENTE_NEGOCIO);
});

test('método operador retorna o perfil operador', function () {
    $this->seed([PerfilSeeder::class]);

    $adm = Perfil::operador();

    expect($adm)->toBeInstanceOf(Perfil::class)
        ->and($adm->slug)->toBe(Perfil::OPERADOR);
});

test('método observador retorna o perfil observador', function () {
    $this->seed([PerfilSeeder::class]);

    $adm = Perfil::observador();

    expect($adm)->toBeInstanceOf(Perfil::class)
        ->and($adm->slug)->toBe(Perfil::OBSERVADOR);
});

test('método padrao retorna o perfil padrão', function () {
    $this->seed([PerfilSeeder::class]);

    $adm = Perfil::padrao();

    expect($adm)->toBeInstanceOf(Perfil::class)
        ->and($adm->slug)->toBe(Perfil::PADRAO);
});
