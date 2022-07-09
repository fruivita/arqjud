<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\ConfiguracaoSeeder;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);
});

// Exceptions
test('lança exception ao tentar criar usuários duplicados, isto é, com mesmo username ou guid', function () {
    expect(
        fn () => Usuario::factory(2)->create(['username' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Usuario::factory(2)->create(['guid' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar usuário com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Usuario::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',     Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['username', Str::random(21),  'Data too long for column'], // máximo 20 caracteres
    ['username', null,             'cannot be null'],           // obrigatório
    ['password', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['guid',     Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['domain',   Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Usuario::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['perfil_id',            10, 'Cannot add or update a child row'], // não existente
    ['perfil_concedido_por', 10, 'Cannot add or update a child row'], // não existente
    ['antigo_perfil_id',     10, 'Cannot add or update a child row'], // não existente
]);

// Caminho feliz
test('cria muitos usuários', function () {
    Usuario::factory(30)->create();

    expect(Usuario::count())->toBe(30);
});

test('campos opcionais estão definidos', function () {
    Usuario::factory()->create(['nome' => null]);

    expect(Usuario::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Usuario::factory()->create([
        'nome' => Str::random(255),
        'username' => Str::random(20),
        'password' => Str::random(255),
        'guid' => Str::random(255),
        'domain' => Str::random(255),
    ]);

    expect(Usuario::count())->toBe(1);
});

test('um usuário possui um perfil', function () {
    $usuario = Usuario::factory()->for(Perfil::factory(), 'perfil')->create();

    $usuario->load(['perfil']);

    expect($usuario->perfil)->toBeInstanceOf(Perfil::class);
});

test('um usuário pode ter um perfil antigo', function () {
    $usuario = Usuario::factory()->for(Perfil::factory(), 'perfilAntigo')->create();

    $usuario->load(['perfilAntigo']);

    expect($usuario->perfilAntigo)->toBeInstanceOf(Perfil::class);
});

test('perfil padrão do usuário é "Padrão"', function () {
    $usuario = Usuario::create(['username' => 'foo']);

    $usuario->refresh();

    expect($usuario->perfil->id)->toBe(Perfil::PADRAO);
});

test('se não for informada a lotação, a lotação padrão é "SEM_LOTACAO"', function () {
    $usuario = Usuario::create(['username' => 'foo']);

    $usuario->refresh();

    expect($usuario->lotacao->id)->toBe(Lotacao::SEM_LOTACAO);
});

test('usuário pode delegar seu perfil para diversos outros, contudo o usuário pode receber apenas uma única delegação', function () {
    $delegante = Usuario::factory()->create(['perfil_id' => Perfil::ADMINISTRADOR]);

    Usuario::factory(3)->create([
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
        'perfil_concedido_por' => $delegante->id,
        'antigo_perfil_id' => Perfil::OBSERVADOR,
    ]);

    $delegante->load(['delegados', 'delegante']);
    $delegado = Usuario::with('delegante')
    ->where('perfil_concedido_por', $delegante->id)
    ->get()
    ->random();

    expect($delegante->delegados)->toHaveCount(3)
    ->and($delegante->delegante)->toBeNull()
    ->and($delegado->delegante->id)->toBe($delegante->id)
    ->and($delegado->delegados)->toHaveCount(0);
});

test('método possuiPermissao informa se o usuário possui determinada permissão', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    login('foo');

    expect(usuarioAutenticado()->possuiPermissao(Permissao::SimulacaoCreate))->toBeFalse();

    concederPermissao(Permissao::SimulacaoCreate->value);

    expect(usuarioAutenticado()->possuiPermissao(Permissao::SimulacaoCreate))->toBeTrue();

    revogaPermissao(Permissao::SimulacaoCreate->value);

    expect(usuarioAutenticado()->possuiPermissao(Permissao::SimulacaoCreate))->toBeFalse();

    logout();
});

test('método paraHumano retorna o usuário em formato para leitura humana', function () {
    $samaccountname = 'foo';
    $usuario = login($samaccountname);

    expect($usuario->paraHumano())->toBe($samaccountname);

    logout();
});

test('retorna os usuários ordenados pelo escopo de ordenação padrão', function () {
    $primeiro = ['nome' => 'foo', 'username' => 'bar'];
    $segundo = ['nome' => 'foo', 'username' => 'baz'];
    $terceiro = ['nome' => null, 'username' => 'barr'];
    $quarto = ['nome' => null, 'username' => 'barz'];

    Usuario::factory()->create($segundo);
    Usuario::factory()->create($primeiro);
    Usuario::factory()->create($quarto);
    Usuario::factory()->create($terceiro);

    $usuarios = Usuario::ordenacaoPadrao()->get();

    expect($usuarios->get(0)->username)->toBe($primeiro['username'])
    ->and($usuarios->get(1)->username)->toBe($segundo['username'])
    ->and($usuarios->get(2)->username)->toBe($terceiro['username'])
    ->and($usuarios->get(3)->username)->toBe($quarto['username']);
});

test('método delegar concede ao usuário informado o mesmo perfil do usuário autenticado e salva seu antigo perfil', function () {
    $delegante = Usuario::factory()->create(['perfil_id' => Perfil::GERENTE_NEGOCIO]);

    $delegado = Usuario::factory()->create(['perfil_id' => Perfil::OBSERVADOR]);

    $delegante->delegar($delegado);

    $delegado->refresh();

    expect($delegado->perfil_id)->toBe(Perfil::GERENTE_NEGOCIO)
    ->and($delegado->perfil_concedido_por)->toBe($delegante->id)
    ->and($delegado->antigo_perfil_id)->toBe(Perfil::OBSERVADOR);
});

test('método revogaDelegacao revoga o perfil do usuário, retornando-o ao seu perfil antigo', function () {
    $delegante = Usuario::factory()->create(['perfil_id' => Perfil::GERENTE_NEGOCIO]);

    $delegado = Usuario::factory()->create([
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
        'perfil_concedido_por' => $delegante->id,
        'antigo_perfil_id' => Perfil::OBSERVADOR,
    ]);

    $delegado->revogaDelegacao();

    $delegante->refresh();
    $delegado->refresh();

    expect($delegante->perfil_id)->toBe(Perfil::GERENTE_NEGOCIO)
    ->and($delegante->perfil_concedido_por)->toBeNull()
    ->and($delegante->perfil_concedido_por)->toBeNull()
    ->and($delegado->perfil_id)->toBe(Perfil::OBSERVADOR)
    ->and($delegado->perfil_concedido_por)->toBeNull()
    ->and($delegado->perfil_concedido_por)->toBeNull();
});

test("método updateERevogaDelegacoes atualiza o perfil do usuário e remove as delegações feitas por ele", function () {
    $delegante = Usuario::factory()->create([
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
    ]);

    $delegado_1 = Usuario::factory()->create([
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
        'perfil_concedido_por' => $delegante->id,
        'antigo_perfil_id' => Perfil::OBSERVADOR,
    ]);

    $delegado_2 = Usuario::factory()->create([
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
        'perfil_concedido_por' => $delegante->id,
        'antigo_perfil_id' => Perfil::PADRAO,
    ]);

    $delegante->perfil_id = Perfil::ADMINISTRADOR;
    $delegante->updateERevogaDelegacoes();

    $delegante->refresh();
    $delegado_1->refresh();
    $delegado_2->refresh();

    expect($delegante->perfil_id)->toBe(Perfil::ADMINISTRADOR)
    ->and($delegante->perfil_concedido_por)->toBeNull()
    ->and($delegante->antigo_perfil_id)->toBeNull()
    ->and($delegado_1->perfil_id)->toBe(Perfil::OBSERVADOR)
    ->and($delegado_1->perfil_concedido_por)->toBeNull()
    ->and($delegado_1->antigo_perfil_id)->toBeNull()
    ->and($delegado_2->perfil_id)->toBe(Perfil::PADRAO)
    ->and($delegado_2->perfil_concedido_por)->toBeNull()
    ->and($delegado_2->antigo_perfil_id)->toBeNull();
});

test('método eSuperAdmin identifica um super administrador', function () {
    $this->seed(ConfiguracaoSeeder::class);

    $usuario_comum = login('bar');
    $usuario_comum->refresh();

    expect($usuario_comum->eSuperAdmin())->toBeFalse();

    logout();

    // 'dumb user' é o usuário admnistrador definido no ConfiguracaoSeeder
    $super_admin = login('dumb user');
    $super_admin->refresh();

    expect($super_admin->eSuperAdmin())->toBeTrue();
});

test('sem configuração definida, o método eSuperAdmin retornará falso para qualquer usuário', function () {
    $usuario_comum = login('bar');
    $usuario_comum->refresh();

    expect($usuario_comum->eSuperAdmin())->toBeFalse();

    logout();

    $super_admin = login('dumb user');
    $super_admin->refresh();

    expect($super_admin->eSuperAdmin())->toBeFalse();
});

test('método permissoes retorna o id de todas as permissões do usuário', function () {
    $usuario = login('bar');
    $usuario->refresh();

    expect($usuario->permissoes())->toBeEmpty();

    concederPermissao(Permissao::LogViewAny->value);
    concederPermissao(Permissao::SimulacaoCreate->value);

    expect($usuario->permissoes())->toContain(
        Permissao::LogViewAny->value,
        Permissao::SimulacaoCreate->value
    );
});

test('método perfilPorDelegacao verifica se o perfil do usuário foi obtido por delegação ou se é um perfil original', function () {
    $delegante = Usuario::factory()->create();

    $delegado = Usuario::factory()->create();

    expect($delegado->perfilPorDelegacao())->toBeFalse();

    $delegante->delegar($delegado);

    expect($delegado->perfilPorDelegacao())->toBeTrue();
});
