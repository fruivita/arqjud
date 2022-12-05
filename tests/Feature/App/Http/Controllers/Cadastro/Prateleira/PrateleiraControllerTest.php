<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use App\Http\Controllers\Cadastro\Prateleira\PrateleiraController;
use App\Http\Requests\Cadastro\Prateleira\EditPrateleiraRequest;
use App\Http\Requests\Cadastro\Prateleira\IndexPrateleiraRequest;
use App\Http\Requests\Cadastro\Prateleira\PostPrateleiraRequest;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Prateleira;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->estante = Estante::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma prateleira', function () {
    $id_prateleira = Prateleira::factory()->create()->id;

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeTrue();

    delete(route('cadastro.prateleira.destroy', $id_prateleira))
        ->assertForbidden();

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação da prateleira', function () {
    get(route('cadastro.prateleira.create', $this->estante))->assertForbidden();
});

// Caminho feliz
// test('action do controller usa o form request', function ($action, $request) {
//     $this->assertActionUsesFormRequest(
//         PrateleiraController::class,
//         $action,
//         $request
//     );
// })->with([
//     ['index', IndexPrateleiraRequest::class],
//     ['store', PostPrateleiraRequest::class],
//     ['edit', EditPrateleiraRequest::class],
//     ['update', PostPrateleiraRequest::class],
// ]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Prateleira::factory(2)->create();

    concederPermissao(Permissao::PRATELEIRA_VIEW_ANY);

    get(route('cadastro.prateleira.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Prateleira/Index')
                ->has('prateleiras.data', 2)
        );
});

// test('action create compartilha os dados esperados com a view/componente correto', function () {
//     Prateleira::factory()->for($this->estante)->create();

//     $this->travel(1)->seconds();
//     $ultima_prateleira_criada = Prateleira::factory()->for($this->estante)->create();

//     $this->travel(1)->seconds();
//     // prateleira de outra estante, será desconsiderada
//     Prateleira::factory()->create();

//     concederPermissao(Permissao::PrateleiraCreate);

//     get(route('cadastro.prateleira.create', $this->estante))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Prateleira/Create')
//                 ->where('ultima_insercao', [
//                     'numero' => $ultima_prateleira_criada->numero,
//                 ])
//                 ->where('estante_pai', Estante::hierarquiaAscendente()->find($this->estante->id)->only(['id', 'numero', 'localidade_nome', 'predio_nome', 'andar_numero', 'andar_apelido', 'sala_numero']))
//         );
// });

// test('cria uma nova prateleira', function () {
//     concederPermissao(Permissao::PrateleiraCreate);

//     expect(Prateleira::count())->toBe(0);

//     post(route('cadastro.prateleira.store', $this->estante), [
//         'numero' => '10-a',
//         'descricao' => 'foo bar',
//         'estante_id' => $this->estante->id,
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $prateleira = Prateleira::first();

//     expect(Prateleira::count())->toBe(1)
//         ->and($prateleira->numero)->toBe('10-a')
//         ->and($prateleira->descricao)->toBe('foo bar')
//         ->and($prateleira->estante_id)->toBe($this->estante->id);
// });

// test('action edit compartilha os dados esperados com a view/componente correto', function (bool $permissao) {
//     concederPermissao(Permissao::PrateleiraUpdate);

//     if ($permissao) {
//         concederPermissao(Permissao::CaixaCreate);
//         concederPermissao(Permissao::CaixaView);
//     }

//     $prateleira = Prateleira::factory()->has(Caixa::factory(3), 'caixas')->create();

//     get(route('cadastro.prateleira.edit', $prateleira))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Prateleira/Edit')
//                 ->where('prateleira', Prateleira::hierarquiaAscendente()->find($prateleira->id))
//                 ->has('caixas.data', 3)
//                 ->has('filtros')
//                 ->where('per_page', 10)
//                 ->where('can', ['updatePrateleira' => true, 'createCaixa' => $permissao, 'viewOrUpdateCaixa' => $permissao])
//         );
// })->with([
//     false,
//     true,
// ]);

// test('action edit também é executável com permissão de visualização', function () {
//     concederPermissao(Permissao::PrateleiraView);

//     $prateleira = Prateleira::factory()->create();

//     get(route('cadastro.prateleira.edit', $prateleira))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Prateleira/Edit')
//                 ->where('prateleira', Prateleira::hierarquiaAscendente()->find($prateleira->id))
//                 ->where('can', ['updatePrateleira' => false, 'createCaixa' => false, 'viewOrUpdateCaixa' => false])
//         );
// });

// test('atualiza uma prateleira', function () {
//     concederPermissao(Permissao::PrateleiraUpdate);

//     $prateleira = Prateleira::factory()->create();

//     patch(route('cadastro.prateleira.update', $prateleira), [
//         'numero' => '10-a',
//         'descricao' => 'foo bar',
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $prateleira->refresh();

//     expect($prateleira->numero)->toBe('10-a')
//         ->and($prateleira->descricao)->toBe('foo bar');
// });

test('exclui a prateleira informada', function () {
    $id_prateleira = Prateleira::factory()->create()->id;

    concederPermissao(Permissao::PRATELEIRA_DELETE);

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeTrue();

    delete(route('cadastro.prateleira.destroy', $id_prateleira))
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    expect(Prateleira::where('id', $id_prateleira)->exists())->toBeFalse();
});

test('PrateleiraController usa trait', function () {
    expect(
        collect(class_uses(PrateleiraController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
