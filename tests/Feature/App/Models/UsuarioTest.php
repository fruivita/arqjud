<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Cargo;
use App\Models\FuncaoConfianca;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Pipes\Usuario\JoinAll;
use Database\Seeders\PerfilSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

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

test('escopo operadores retorna todos os usuários do perfil operador', function () {
    $administrador = Perfil::firstWhere('slug', Perfil::ADMINISTRADOR);
    $operador = Perfil::firstWhere('slug', Perfil::OPERADOR);

    Usuario::factory()->for($administrador, 'perfil')->create();
    Usuario::factory(2)->for($operador, 'perfil')->create();

    expect(Usuario::count())->toBe(3)
        ->and(Usuario::operadores()->count())->toBe(2);
});

test('routeNotificationForMail retorna a rota de notificação para o usuário', function () {
    $usuario = Usuario::factory()->make();

    expect($usuario->routeNotificationForMail(new Notification()))
        ->toBe($usuario->email);
});

test('usuário sem nome, username, email e matrícula ou com lotação inválida é considerado com cadastro incompleto', function () {
    $usuario = new Usuario();
    expect($usuario->habilitado())->toBeFalse();

    $usuario->nome = 'foo';
    expect($usuario->habilitado())->toBeFalse();

    $usuario->username = 'bar';
    expect($usuario->habilitado())->toBeFalse();

    $usuario->email = 'baz@baz.baz';
    expect($usuario->habilitado())->toBeFalse();

    $usuario->matricula = '123';
    expect($usuario->habilitado())->toBeFalse();

    $usuario->lotacao_id = 0;
    expect($usuario->habilitado())->toBeFalse();

    $usuario->lotacao_id = -1;
    expect($usuario->habilitado())->toBeFalse();

    $usuario->lotacao_id = 1;
    expect($usuario->habilitado())->toBeTrue();
});

test('retorna os usuários pelo escopo search que busca a partir do início do texto no nome, matrícula, username ou email do usuário', function (string $termo, int $quantidade) {
    Usuario::factory()->create([
        'nome' => 'eeeeffff',
        'matricula' => '111111',
        'username' => 'aaaabbbb',
        'email' => 'foo@bar.com',
    ]);
    Usuario::factory()->create([
        'nome' => 'gggghhhh',
        'matricula' => '111222',
        'username' => 'ccccdddd',
        'email' => 'taz@bar.com',
    ]);

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 2],
    ['eeee', 1],
    ['111', 2],
    ['cccc', 1],
    ['foo', 1],
    ['bar', 0],
]);

test('retorna os usuários pelo escopo search que busca a partir do início do texto na sigla ou nome da lotação', function (string $termo, int $quantidade) {
    Usuario::factory(2)->for(Lotacao::factory(['sigla' => 'aaaabbbb', 'nome' => 'eeeeffff']), 'lotacao')->create();
    Usuario::factory(3)->for(Lotacao::factory(['sigla' => 'ccccdddd', 'nome' => 'gggghhhh']), 'lotacao')->create();

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['gggg', 3],
]);

test('retorna os usuários pelo escopo search que busca a partir do início do texto no nome do cargo', function (string $termo, int $quantidade) {
    Usuario::factory(2)->for(Cargo::factory(['nome' => 'eeeeffff']), 'cargo')->create();
    Usuario::factory(3)->for(Cargo::factory(['nome' => 'gggghhhh']), 'cargo')->create();

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['eeee', 2],
    ['gggg', 3],
]);

test('retorna os usuários pelo escopo search que busca a partir do início do texto no nome da função de confiança', function (string $termo, int $quantidade) {
    Usuario::factory(2)->for(FuncaoConfianca::factory(['nome' => 'eeeeffff']), 'funcaoConfianca')->create();
    Usuario::factory(3)->for(FuncaoConfianca::factory(['nome' => 'gggghhhh']), 'funcaoConfianca')->create();

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['eeee', 2],
    ['gggg', 3],
]);

test('retorna os usuários pelo escopo search que busca a partir do início do texto no nome do perfil', function (string $termo, int $quantidade) {
    Perfil::factory()->hasUsuarios(2)->create(['nome' => 'eeeeffff']);
    Perfil::factory()->hasUsuarios(3)->create(['nome' => 'gggghhhh']);

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['eeee', 2],
    ['gggg', 3],
]);

test('retorna os usuários pelo escopo search que busca a partir do início do texto no username ou nome do usuário delegante', function (string $termo, int $quantidade) {
    Usuario::factory()->hasDelegados(2)->create(['username' => 'aaaabbbb', 'nome' => 'eeeeffff']);
    Usuario::factory()->hasDelegados(3)->create(['username' => 'ccccdddd', 'nome' => 'gggghhhh']);

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 7],
    ['aaaa', 3],
    ['gggg', 4],
]);

test('retorna os usuários pelo escopo search que busca a partir do início do texto no nome do perfil antigo', function (string $termo, int $quantidade) {
    Usuario::factory(2)->for(Perfil::factory(['nome' => 'eeeeffff']), 'perfilAntigo')->create();
    Usuario::factory(3)->for(Perfil::factory(['nome' => 'gggghhhh']), 'perfilAntigo')->create();

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['eeee', 2],
    ['gggg', 3],
]);
