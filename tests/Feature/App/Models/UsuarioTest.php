<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
});

// Exceptions
test('lança exception ao tentar criar usuários duplicados, isto é, com mesma matrícula, username, email ou guid', function () {
    expect(
        fn () => Usuario::factory(2)->create(['matricula' => '11111'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Usuario::factory(2)->create(['username' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Usuario::factory(2)->create(['email' => 'foo@foo.com'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Usuario::factory(2)->create(['guid' => 'foo.bar'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar usuário com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Usuario::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['matricula', Str::random(21),  'Data too long for column'], // máximo 20 caracteres
    ['username',  Str::random(21),  'Data too long for column'], // máximo 20 caracteres
    ['username',  null,             'cannot be null'],           // obrigatório
    ['email',     Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['password',  Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['guid',      Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['domain',    Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Usuario::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['perfil_id',            99999999, 'Cannot add or update a child row'], // não existente
    ['perfil_concedido_por', 99999999, 'Cannot add or update a child row'], // não existente
    ['antigo_perfil_id',     99999999, 'Cannot add or update a child row'], // não existente
]);

// Caminho feliz
test('campos opcionais estão definidos', function () {
    Usuario::factory()->create([
        'matricula' => null,
        'email' => null,
        'nome' => null,
        'password' => null,
        'guid' => null,
        'domain' => null,
    ]);

    expect(Usuario::count())->toBe(1);
});

test('relacionamentos opcionais estão definidos', function () {
    Usuario::factory()->create([
        'lotacao_id' => null,
        'cargo_id' => null,
        'funcao_confianca_id' => null,
        'perfil_id' => null,
        'perfil_concedido_por' => null,
        'antigo_perfil_id' => null,
    ]);

    expect(Usuario::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Usuario::factory()->create([
        'matricula' => Str::random(20),
        'username' => Str::random(20),
        'email' => Str::random(255),
        'nome' => Str::random(255),
        'password' => Str::random(255),
        'guid' => Str::random(255),
        'domain' => Str::random(255),
    ]);

    expect(Usuario::count())->toBe(1);
});

test('um usuário possui um perfil', function () {
    Usuario::factory()->for(Perfil::factory(), 'perfil')->create();

    $usuario = Usuario::with(['perfil'])->first();

    expect($usuario->perfil)->toBeInstanceOf(Perfil::class);
});

test('um usuário pode ter um perfil antigo', function () {
    Usuario::factory()->for(Perfil::factory(), 'perfilAntigo')->create();

    $usuario = Usuario::with(['perfilAntigo'])->first();

    expect($usuario->perfilAntigo)->toBeInstanceOf(Perfil::class);
});

test('um usuário pode criar várias solicitações de processo', function () {
    Usuario::factory()->hasSolicitacoesSolicitadas(3)->create();

    $usuario = Usuario::with('solicitacoesSolicitadas')->first();

    expect($usuario->solicitacoesSolicitadas)->toHaveCount(3);
});

test('um usuário pode recebedor de várias solicitações de processo', function () {
    Usuario::factory()->hasSolicitacoesRecebidas(3)->create();

    $usuario = Usuario::with('solicitacoesRecebidas')->first();

    expect($usuario->solicitacoesRecebidas)->toHaveCount(3);
});

test('um usuário pode ser o remetente de várias solicitações de processo', function () {
    Usuario::factory()->hasSolicitacoesRemetidas(3)->create();

    $usuario = Usuario::with('solicitacoesRemetidas')->first();

    expect($usuario->solicitacoesRemetidas)->toHaveCount(3);
});

test('um usuário pode rearquivar várias solicitações de processo', function () {
    Usuario::factory()->hasSolicitacoesRearquivadas(3)->create();

    $usuario = Usuario::with('solicitacoesRearquivadas')->first();

    expect($usuario->solicitacoesRearquivadas)->toHaveCount(3);
});

test('usuário pode delegar seu perfil para diversos outros', function () {
    $delegante = Usuario::factory()->hasDelegados(3)->create();

    $delegante->loadCount('delegados');

    expect($delegante->delegados_count)->toBe(3);
});

test('usuário delegante só há um', function () {
    $delegado = Usuario::factory()->for(Usuario::factory(), 'delegante')->create();

    $delegado->load('delegante');

    expect($delegado->delegante)->toBeInstanceOf(Usuario::class);
});

test('método possuiPermissao() informa se o usuário possui determinada permissão', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    login();

    expect(usuarioAutenticado()->possuiPermissao(Permissao::ANDAR_CREATE))->toBeFalse();

    concederPermissao(Permissao::ANDAR_CREATE);

    expect(usuarioAutenticado()->possuiPermissao(Permissao::ANDAR_CREATE))->toBeTrue();

    revogaPermissao(Permissao::ANDAR_CREATE);

    expect(usuarioAutenticado()->possuiPermissao(Permissao::ANDAR_CREATE))->toBeFalse();

    logout();
});
