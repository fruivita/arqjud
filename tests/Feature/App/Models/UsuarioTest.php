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
test('lança exception ao tentar criar usuários duplicados, isto é, com mesma matrícula, email ou guid', function () {
    expect(
        fn () => Usuario::factory(2)->create(['matricula' => '11111'])
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
    ['matricula', null,             'cannot be null'],           // obrigatório
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
]);

// Caminho feliz
test('campos opcionais estão definidos', function () {
    Usuario::factory()->create([
        'email' => null,
        'nome' => null,
        'password' => null,
        'ultimo_login' => null,
        'ip' => null,
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
    ]);

    expect(Usuario::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Usuario::factory()->create([
        'matricula' => Str::random(20),
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

test('usuário sem nome, email e matrícula ou com lotação inválida é considerado com cadastro incompleto', function () {
    $usuario = new Usuario();
    expect($usuario->habilitado())->toBeFalse();

    $usuario->nome = 'foo';
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

test('retorna os usuários pelo escopo search que busca a partir do início do texto no nome, matrícula ou email do usuário', function (string $termo, int $quantidade) {
    Usuario::factory()->create([
        'nome' => 'eeeeffff',
        'matricula' => '11111111',
        'email' => 'foooo@bar.com',
    ]);
    Usuario::factory()->create([
        'nome' => 'gggghhhh',
        'matricula' => '11112222',
        'email' => 'tazzz@bar.com',
    ]);

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 2],
    ['eeee', 1],
    ['1111', 2],
    ['foooo', 1],
    ['barrr', 0],
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

test('método perfilSuperior identifica como perfil superior caso o outro não tenha perfil', function () {
    $usuario = Usuario::factory()->create();
    $outro = Usuario::factory()->create(['perfil_id' => null]);

    expect($usuario->perfilSuperior($outro))->toBeTrue();
});

test('método perfilSuperior identifica se o perfil de um usuário é superior ao do outro com base no poder do perfil', function (string $poder, bool $esperado) {
    $usuario = Usuario::factory()->for(Perfil::factory(['poder' => $poder]), 'perfil')->create();
    $outro = Usuario::factory()->for(Perfil::factory(['poder' => 500]), 'perfil')->create();

    expect($usuario->perfilSuperior($outro))->toBe($esperado);
})->with([
    [499, false],
    [501, true],
]);

test('pertenceLotacaoAdministravel informa se o usuário está lotado em uma lotação administrável', function (bool $administravel) {
    $usuario = Usuario::factory()->for(Lotacao::factory(['administravel' => $administravel]), 'lotacao')->create();

    expect($usuario->pertenceLotacaoAdministravel())->toBe($administravel);
})->with([true, false]);

test('resetarPerfil atribui o perfil padrão ao usuário informado', function () {
    $usuario = Usuario::factory()->create();

    expect($usuario->perfil_id)->not->toBe(Perfil::administrador()->id);

    Usuario::resetarPerfil($usuario->id);

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe(Perfil::padrao()->id);
});

test('resetarPerfil não altera o perfil do usuário se ele for administrador', function () {
    $adm = Perfil::administrador();

    $usuario = Usuario::factory()->create(['perfil_id' => $adm->id]);

    expect($usuario->perfil_id)->toBe($adm->id);

    Usuario::resetarPerfil($usuario->id);

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe($adm->id);
});
