<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Localidade\LocalidadeController;
use App\Models\Localidade;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma localidade', function () {
    $id_localidade = Localidade::factory()->create()->id;

    expect(Localidade::where('id', $id_localidade)->exists())->toBeTrue();

    delete(route('cadastro.localidade.destroy', $id_localidade))
        ->assertForbidden();

    expect(Localidade::where('id', $id_localidade)->exists())->toBeTrue();
});

// test('usuário sem permissão não consegue exibir formulário de criação da localidade', function () {
//     get(route('cadastro.localidade.create'))
//         ->assertForbidden();
// });

// // Caminho feliz
// test('action do controller usa o form request', function ($action, $request) {
//     $this->assertActionUsesFormRequest(
//         LocalidadeController::class,
//         $action,
//         $request
//     );
// })->with([
//     ['index', IndexLocalidadeRequest::class],
//     ['store', PostLocalidadeRequest::class],
//     ['edit', EditLocalidadeRequest::class],
//     ['update', PostLocalidadeRequest::class],
// ]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Localidade::factory(2)->create();

    concederPermissao(Permissao::LOCALIDADE_VIEW_ANY);

    get(route('cadastro.localidade.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Localidade/Index')
                ->has('localidades.data', 2)
        );
});

// test('action create compartilha os dados esperados com a view/componente correto', function () {
//     Localidade::factory()->create();

//     $this->travel(1)->seconds();

//     $ultima_localidade_criada = Localidade::factory()->create();

//     concederPermissao(Permissao::LocalidadeCreate);

//     get(route('cadastro.localidade.create'))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Localidade/Create')
//                 ->where('ultima_insercao', ['nome' => $ultima_localidade_criada->nome])
//         );
// });

// test('cria uma nova localidade', function () {
//     concederPermissao(Permissao::LocalidadeCreate);

//     expect(Localidade::count())->toBe(0);

//     post(route('cadastro.localidade.store', [
//         'nome' => 'foo',
//         'descricao' => 'foo bar',
//     ]))
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $localidade = Localidade::first();

//     expect(Localidade::count())->toBe(1)
//         ->and($localidade->nome)->toBe('foo')
//         ->and($localidade->descricao)->toBe('foo bar');
// });

// test('action edit compartilha os dados esperados com a view/componente correto', function (bool $permissao) {
//     concederPermissao(Permissao::LocalidadeUpdate);

//     if ($permissao) {
//         concederPermissao(Permissao::PredioCreate);
//         concederPermissao(Permissao::PredioUpdate);
//     }

//     $localidade = Localidade::factory()->has(Predio::factory(3), 'predios')->create();

//     get(route('cadastro.localidade.edit', $localidade))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Localidade/Edit')
//                 ->where('localidade', $localidade)
//                 ->has('predios.data', 3)
//                 ->has('filtros')
//                 ->where('per_page', 10)
//                 ->where('can', ['updateLocalidade' => true, 'createPredio' => $permissao, 'viewOrUpdatePredio' => $permissao])
//         );
// })->with([
//     false,
//     true,
// ]);

// test('action edit também é executável com permissão de visualização', function () {
//     concederPermissao(Permissao::LocalidadeView);

//     $localidade = Localidade::factory()->create();

//     get(route('cadastro.localidade.edit', $localidade))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Localidade/Edit')
//                 ->where('localidade', $localidade)
//                 ->where('can', ['updateLocalidade' => false, 'createPredio' => false, 'viewOrUpdatePredio' => false])
//         );
// });

// test('atualiza uma localidade', function () {
//     concederPermissao(Permissao::LocalidadeUpdate);

//     $localidade = Localidade::factory()->create();

//     patch(route('cadastro.localidade.update', $localidade), [
//         'nome' => 'foo',
//         'descricao' => 'foo bar',
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $localidade->refresh();

//     expect($localidade->nome)->toBe('foo')
//         ->and($localidade->descricao)->toBe('foo bar');
// });

test('exclui a localidade informada', function () {
    $id_localidade = Localidade::factory()->create()->id;

    concederPermissao(Permissao::LOCALIDADE_DELETE);

    expect(Localidade::where('id', $id_localidade)->exists())->toBeTrue();

    delete(route('cadastro.localidade.destroy', $id_localidade))
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    expect(Localidade::where('id', $id_localidade)->exists())->toBeFalse();
});

test('LocalidadeController usa trait', function () {
    expect(
        collect(class_uses(LocalidadeController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
