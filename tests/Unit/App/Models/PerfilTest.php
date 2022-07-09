<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao as EnumPermissao;
use App\Models\Permissao;
use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilPermissaoSeeder;
use Database\Seeders\PermissaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(LotacaoSeeder::class);
});

// Exceptions
test('lança exception ao tentar criar perfis duplicados, isto é, com mesmo id ou nome', function () {
    expect(
        fn () => Perfil::factory(2)->create(['id' => 1])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Perfil::factory(2)->create(['nome' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar perfil com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Perfil::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['nome',      null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 50 caracteres
]);

// Falhas
test('método salvaESincronizaPermissoes faz rollback caso o update do perfil falhe', function () {
    $nome = 'foo';
    $descricao = 'bar';

    $perfil = Perfil::factory()->create([
        'nome' => $nome,
        'descricao' => $descricao,
    ]);

    $perfil->nome = 'new foo';
    $perfil->descricao = 'new bar';

    // tentativa de criar relacionamento com permissões inexistentes
    $salvo = $perfil->salvaESincronizaPermissoes([1, 2]);

    $perfil->refresh()->load('permissoes');

    expect($salvo)->toBeFalse()
    ->and($perfil->nome)->toBe($nome)
    ->and($perfil->descricao)->toBe($descricao)
    ->and($perfil->permissoes)->toBeEmpty();
});

test('método salvaESincronizaPermissoes registra em log a falha no update do perfil', function () {
    Log::spy();

    $perfil = Perfil::factory()->create();

    // tentativa de criar relacionamento com permissões inexistentes
    $perfil->salvaESincronizaPermissoes([1, 2]);

    Log::shouldHaveReceived('error')
    ->withArgs(fn ($message) => $message === __('Falha na atualização do perfil'))
    ->once();
});

// Caminho feliz
test('ids das permissões estão definidos', function () {
    expect(Perfil::ADMINISTRADOR)->toBe(9000)
    ->and(Perfil::GERENTE_NEGOCIO)->toBe(8000)
    ->and(Perfil::OBSERVADOR)->toBe(7000)
    ->and(Perfil::PADRAO)->toBe(1000);
});

test('cria muitos perfis', function () {
    Perfil::factory(30)->create();

    expect(Perfil::count())->toBe(30);
});

test('campos opcionais estão definidos', function () {
    Perfil::factory()->create(['descricao' => null]);

    expect(Perfil::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Perfil::factory()->create([
        'nome' => Str::random(50),
        'descricao' => Str::random(255),
    ]);

    expect(Perfil::count())->toBe(1);
});

test('um perfil possui muitas permissões', function () {
    Perfil::factory()->has(Permissao::factory(3), 'permissoes')->create();

    $perfil = Perfil::with('permissoes')->first();

    expect($perfil->permissoes)->toHaveCount(3);
});

test('um perfil possuir muitos usuáris', function () {
    Perfil::factory()->has(Usuario::factory(3), 'usuarios')->create();

    $perfil = Perfil::with('usuarios')->first();

    expect($perfil->usuarios)->toHaveCount(3);
});

test('método salvaESincronizaPermissoes salva os novos atributos e cria os relacionamentos com as permissões informadas', function () {
    $nome = 'foo';
    $descricao = 'bar';

    $perfil = Perfil::factory()->create([
        'nome' => 'baz',
        'descricao' => 'foo bar baz',
    ]);

    Permissao::factory()->create(['id' => 1]);
    Permissao::factory()->create(['id' => 2]);
    Permissao::factory()->create(['id' => 3]);

    $perfil->nome = $nome;
    $perfil->descricao = $descricao;

    $salvo = $perfil->salvaESincronizaPermissoes([1, 3]);
    $perfil->refresh()->load('permissoes');

    expect($salvo)->toBeTrue()
    ->and($perfil->nome)->toBe($nome)
    ->and($perfil->descricao)->toBe($descricao)
    ->and($perfil->permissoes->modelKeys())->toBe([1, 3]);
});

test('perfil administrador possui todas as permissões', function ($permissao) {
    $this->seed([
        PerfilSeeder::class,
        PermissaoSeeder::class,
        PerfilPermissaoSeeder::class,
    ]);

    $usuario = Usuario::factory()->create(['perfil_id' => Perfil::ADMINISTRADOR]);

    expect($usuario->possuiPermissao($permissao))->toBeTrue();
})->with([
    EnumPermissao::CaixaViewAny,
    EnumPermissao::CaixaView,
    EnumPermissao::CaixaCreate,
    EnumPermissao::CaixaCreateMany,
    EnumPermissao::CaixaUpdate,
    EnumPermissao::CaixaDelete,
    EnumPermissao::VolumeCaixaViewAny,
    EnumPermissao::VolumeCaixaView,
    EnumPermissao::VolumeCaixaCreate,
    EnumPermissao::VolumeCaixaUpdate,
    EnumPermissao::VolumeCaixaDelete,
    EnumPermissao::PredioViewAny,
    EnumPermissao::PredioView,
    EnumPermissao::PredioCreate,
    EnumPermissao::PredioUpdate,
    EnumPermissao::PredioDelete,
    EnumPermissao::ConfiguracaoView,
    EnumPermissao::ConfiguracaoUpdate,
    EnumPermissao::DelegacaoViewAny,
    EnumPermissao::DelegacaoCreate,
    EnumPermissao::DocumentacaoViewAny,
    EnumPermissao::DocumentacaoView,
    EnumPermissao::DocumentacaoCreate,
    EnumPermissao::DocumentacaoUpdate,
    EnumPermissao::DocumentacaoDelete,
    EnumPermissao::AndarViewAny,
    EnumPermissao::AndarView,
    EnumPermissao::AndarCreate,
    EnumPermissao::AndarUpdate,
    EnumPermissao::AndarDelete,
    EnumPermissao::ImportacaoCreate,
    EnumPermissao::LogViewAny,
    EnumPermissao::LogDelete,
    EnumPermissao::LogDownload,
    EnumPermissao::PermissaoViewAny,
    EnumPermissao::PermissaoView,
    EnumPermissao::PermissaoUpdate,
    EnumPermissao::PerfilViewAny,
    EnumPermissao::PerfilView,
    EnumPermissao::PerfilUpdate,
    EnumPermissao::SalaViewAny,
    EnumPermissao::SalaView,
    EnumPermissao::SalaCreate,
    EnumPermissao::SalaUpdate,
    EnumPermissao::SalaDelete,
    EnumPermissao::SimulacaoCreate,
    EnumPermissao::PrateleiraViewAny,
    EnumPermissao::PrateleiraView,
    EnumPermissao::PrateleiraCreate,
    EnumPermissao::PrateleiraUpdate,
    EnumPermissao::PrateleiraDelete,
    EnumPermissao::LocalidadeViewAny,
    EnumPermissao::LocalidadeView,
    EnumPermissao::LocalidadeCreate,
    EnumPermissao::LocalidadeUpdate,
    EnumPermissao::LocalidadeDelete,
    EnumPermissao::EstanteViewAny,
    EnumPermissao::EstanteView,
    EnumPermissao::EstanteCreate,
    EnumPermissao::EstanteUpdate,
    EnumPermissao::EstanteDelete,
    EnumPermissao::UsuarioViewAny,
    EnumPermissao::UsuarioUpdate,
]);

test('retorna os perfis ordenados pelo escopo de ordenação padrão', function () {
    Perfil::factory()->create(['id' => 10]);
    Perfil::factory()->create(['id' => 30]);
    Perfil::factory()->create(['id' => 20]);

    $perfis = Perfil::ordenacaoPadrao()->get();

    expect($perfis->get(0)->id)->toBe(30)
    ->and($perfis->get(1)->id)->toBe(20)
    ->and($perfis->get(2)->id)->toBe(10);
});

test('retorna os perfis disponíveis para atribuição utilizando o escopo definido', function () {
    $this->seed(PerfilSeeder::class);

    $usuario = login('foo');

    $usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $usuario->save();

    $perfis = Perfil::disponiveisParaAtribuicao()->get();

    expect(Perfil::count())->toBe(4)
    ->and($perfis->count())->toBe(3);
});

test('perfis estão na ordem hierarquica correta', function () {
    // perfil com id maior possui maiores permissões na aplicação
    expect(Perfil::ADMINISTRADOR)->toBeGreaterThan(Perfil::GERENTE_NEGOCIO)
    ->and(Perfil::GERENTE_NEGOCIO)->toBeGreaterThan(Perfil::OBSERVADOR)
    ->and(Perfil::OBSERVADOR)->toBeGreaterThan(Perfil::PADRAO);
});
