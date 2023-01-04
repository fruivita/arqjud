<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Autorizacao\UsuarioController;
use App\Http\Requests\Autorizacao\UpdateUsuarioRequest;
use App\Http\Resources\Usuario\UsuarioResource;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Pipes\Usuario\RevogarDelegacoes;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();
    Auth::login($this->usuario);
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue listar os usuários', function () {
    get(route('autorizacao.usuario.index'))->assertForbidden();
});

test('usuário sem permissão não consegue visualizar um usuário', function () {
    $usuario = Usuario::factory()->create();

    get(route('autorizacao.usuario.edit', $usuario))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        UsuarioController::class,
        $action,
        $request
    );
})->with([
    ['update', UpdateUsuarioRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Usuario::factory(2)->create();

    concederPermissao([Permissao::USUARIO_VIEW_ANY]);

    get(route('autorizacao.usuario.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Autorizacao/Usuario/Index')
                ->has('usuarios.data', 3)
                ->has('usuarios.meta.termo')
                ->has('usuarios.meta.order')
        );
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()->completo()->create();

    get(route('autorizacao.usuario.edit', $usuario))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Autorizacao/Usuario/Edit')
                ->where('usuario', UsuarioResource::make($usuario->load(['lotacao', 'cargo', 'funcaoConfianca', 'perfil', 'delegante', 'perfilAntigo']))->response()->getData(true))
                ->has('perfis.data', Perfil::disponiveisParaAtribuicao()->count())
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::USUARIO_VIEW);

    $usuario = Usuario::factory()->completo()->create();

    get(route('autorizacao.usuario.edit', $usuario))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Autorizacao/Usuario/Edit')
        );
});

test('atualiza um usuário e remove suas delegações', function () {
    $perfis = Perfil::all();

    $this->usuario->perfil_id = $perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id;
    $this->usuario->save();
    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()->create(['perfil_id' => $perfis->firstWhere('slug', Perfil::PADRAO)->id]);

    $delegado = Usuario::factory()->create([
        'perfil_id' => $perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id,
        'perfil_concedido_por' => $usuario->id,
        'antigo_perfil_id' => $perfis->firstWhere('slug', Perfil::OPERADOR)->id,
    ]);

    patch(route('autorizacao.usuario.update', $usuario), [
        'perfil_id' => $perfis->firstWhere('slug', Perfil::OPERADOR)->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $usuario->refresh();
    $delegado->refresh();

    expect($usuario->perfil_id)->toBe($perfis->firstWhere('slug', Perfil::OPERADOR)->id)
        ->and($delegado->perfil_id)->toBe($perfis->firstWhere('slug', Perfil::OPERADOR)->id)
        ->and($usuario->perfil_concedido_por)->toBeNull()
        ->and($usuario->antigo_perfil_id)->toBeNull();
});

test('registra o log em caso de falha na atualização do usuário', function () {
    $perfis = Perfil::all();
    $this->usuario->perfil_id = $perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id;
    $this->usuario->save();

    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()->create(['perfil_id' => $perfis->firstWhere('slug', Perfil::PADRAO)->id]);

    $this->partialMock(RevogarDelegacoes::class)
        ->shouldReceive('handle')
        ->andThrow(\Exception::class)
        ->once();

    Log::spy();

    patch(route('autorizacao.usuario.update', $usuario), [
        'perfil_id' => $perfis->firstWhere('slug', Perfil::OPERADOR)->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.erro');

    Log::shouldHaveReceived('critical')
        ->withArgs(fn ($message) => $message === __('Falha ao atualizar o usuário'))
        ->once();
});

test('atualização do usuário está protegida por transaction', function () {
    $perfis = Perfil::all();
    $this->usuario->perfil_id = $perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id;
    $this->usuario->save();

    concederPermissao(Permissao::USUARIO_UPDATE);

    $usuario = Usuario::factory()->create(['perfil_id' => $perfis->firstWhere('slug', Perfil::PADRAO)->id]);

    $this->partialMock(RevogarDelegacoes::class)
        ->shouldReceive('handle')
        ->andThrow(\Exception::class)
        ->once();

    $database = DB::spy();

    (new UsuarioController())->update(new UpdateUsuarioRequest([
        'perfil_id' => $perfis->firstWhere('slug', Perfil::OPERADOR)->id,
    ]), $usuario);

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
    $database->shouldNotReceive('commit');
});

test('UsuarioController usa trait', function () {
    expect(
        collect(class_uses(UsuarioController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
