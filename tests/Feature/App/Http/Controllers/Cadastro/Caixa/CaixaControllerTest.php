<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use App\Http\Controllers\Cadastro\Caixa\CaixaController;
use App\Http\Requests\Cadastro\Caixa\EditCaixaRequest;
use App\Http\Requests\Cadastro\Caixa\IndexCaixaRequest;
use App\Http\Requests\Cadastro\Caixa\PostCaixaRequest;
use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Processo;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->prateleira = Prateleira::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir uma caixa', function () {
    $id_caixa = Caixa::factory()->create()->id;

    expect(Caixa::where('id', $id_caixa)->exists())->toBeTrue();

    delete(route('cadastro.caixa.destroy', $id_caixa))
        ->assertForbidden();

    expect(Caixa::where('id', $id_caixa)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação da caixa', function () {
    get(route('cadastro.caixa.create', $this->prateleira))->assertForbidden();
});

// Caminho feliz
// test('action do controller usa o form request', function ($action, $request) {
//     $this->assertActionUsesFormRequest(
//         CaixaController::class,
//         $action,
//         $request
//     );
// })->with([
//     ['index', IndexCaixaRequest::class],
//     ['store', PostCaixaRequest::class],
//     ['edit', EditCaixaRequest::class],
//     ['update', PostCaixaRequest::class],
// ]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Caixa::factory(2)->create();

    concederPermissao(Permissao::CAIXA_VIEW_ANY);

    get(route('cadastro.caixa.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Caixa/Index')
                ->has('caixas.data', 2)
        );
});

// test('action create compartilha os dados esperados com a view/componente correto', function () {
//     Caixa::factory()->for($this->prateleira)->create();

//     $this->travel(1)->seconds();
//     $ultima_caixa_criada = Caixa::factory()->for($this->prateleira)->create();
//     $ultima_caixa_criada->load('localidadeCriadora');

//     $this->travel(1)->seconds();
//     // caixa de outra prateleira, será desconsiderada
//     Caixa::factory()->create();

//     concederPermissao(Permissao::CaixaCreate);

//     get(route('cadastro.caixa.create', $this->prateleira))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Caixa/Create')
//                 ->where('ultima_insercao', [
//                     'numero' => $ultima_caixa_criada->numero,
//                     'ano' => $ultima_caixa_criada->ano,
//                     'guarda_permanente' => $ultima_caixa_criada->guarda_permanente,
//                     'descricao' => $ultima_caixa_criada->descricao,
//                     'localidade_criadora_id' => $ultima_caixa_criada->localidade_criadora_id,
//                     'localidade_criadora' => $ultima_caixa_criada->localidadeCriadora->only(['id', 'nome']),
//                 ])
//                 ->where('prateleira_pai', Prateleira::hierarquiaAscendente()->find($this->prateleira->id)->only(['id', 'numero', 'localidade_nome', 'predio_nome', 'andar_numero', 'andar_apelido', 'sala_numero', 'estante_numero']))
//                 ->has('localidades', 5)
//         );
// });

// test('cria uma nova caixa', function () {
//     $localidade = Localidade::factory()->create();
//     concederPermissao(Permissao::CaixaCreate);

//     expect(Caixa::count())->toBe(0);

//     post(route('cadastro.caixa.store', $this->prateleira), [
//         'numero' => 10,
//         'ano' => 2020,
//         'guarda_permanente' => true,
//         'complemento' => 'foo',
//         'descricao' => 'foo bar',
//         'localidade_criadora_id' => $localidade->id,
//         'prateleira_id' => $this->prateleira->id,
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $caixa = Caixa::first();

//     expect(Caixa::count())->toBe(1)
//         ->and($caixa->numero)->toBe(10)
//         ->and($caixa->ano)->toBe(2020)
//         ->and($caixa->guarda_permanente)->toBeTrue()
//         ->and($caixa->complemento)->toBe('foo')
//         ->and($caixa->descricao)->toBe('foo bar')
//         ->and($caixa->localidade_criadora_id)->toBe($localidade->id)
//         ->and($caixa->prateleira_id)->toBe($this->prateleira->id);
// });

// test('action edit compartilha os dados esperados com a view/componente correto', function (bool $permissao) {
//     concederPermissao(Permissao::CaixaUpdate);

//     if ($permissao) {
//         concederPermissao(Permissao::VolumeCaixaCreate);
//         concederPermissao(Permissao::VolumeCaixaUpdate);
//     }

//     $caixa = Caixa::factory()->has(VolumeCaixa::factory(3), 'volumes')->create();

//     get(route('cadastro.caixa.edit', $caixa))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Caixa/Edit')
//                 ->where('caixa', Caixa::hierarquiaAscendente()->find($caixa->id))
//                 ->has('localidades', 2 + 1) // 1 localidade criadora da caixa
//                 ->has('volumes_caixa.data', 3)
//                 ->has('filtros')
//                 ->where('per_page', 10)
//                 ->where('can', ['updateCaixa' => true, 'createVolumeCaixa' => $permissao, 'viewOrUpdateVolumeCaixa' => $permissao])
//         );
// })->with([
//     false,
//     true,
// ]);

// test('action edit também é executável com permissão de visualização', function () {
//     concederPermissao(Permissao::CaixaView);

//     $caixa = Caixa::factory()->create();

//     get(route('cadastro.caixa.edit', $caixa))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Caixa/Edit')
//                 ->where('caixa', Caixa::hierarquiaAscendente()->find($caixa->id))
//                 ->where('can', ['updateCaixa' => false, 'createVolumeCaixa' => false, 'viewOrUpdateVolumeCaixa' => false])
//         );
// });

// test('atualiza uma caixa e o status de guarda dos processos da caixa', function () {
//     concederPermissao(Permissao::CaixaUpdate);

//     $localidade = Localidade::factory()->create();
//     $caixa = Caixa::factory()->create();
//     $volume_caixa = VolumeCaixa::factory()->for($caixa, 'caixa')->create();
//     Processo::factory(2)->for($volume_caixa, 'volumeCaixa')->create();

//     patch(route('cadastro.caixa.update', $caixa), [
//         'numero' => 10,
//         'ano' => 2020,
//         'guarda_permanente' => true,
//         'complemento' => 'foo',
//         'descricao' => 'foo bar',
//         'localidade_criadora_id' => $localidade->id,
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $caixa->refresh();

//     $volume_caixa->load('processos');

//     expect($caixa->numero)->toBe(10)
//         ->and($caixa->ano)->toBe(2020)
//         ->and($caixa->guarda_permanente)->toBe(true)
//         ->and($caixa->complemento)->toBe('foo')
//         ->and($caixa->descricao)->toBe('foo bar')
//         ->and($caixa->localidade_criadora_id)->toBe($localidade->id)
//         ->and($volume_caixa->processos->pluck('guarda_permanente')->toArray())->toBe([true, true]);
// });

test('exclui a caixa informada', function () {
    $id_caixa = Caixa::factory()->create()->id;

    concederPermissao(Permissao::CAIXA_DELETE);

    expect(Caixa::where('id', $id_caixa)->exists())->toBeTrue();

    delete(route('cadastro.caixa.destroy', $id_caixa))
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    expect(Caixa::where('id', $id_caixa)->exists())->toBeFalse();
});

test('CaixaController usa trait', function () {
    expect(
        collect(class_uses(CaixaController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
