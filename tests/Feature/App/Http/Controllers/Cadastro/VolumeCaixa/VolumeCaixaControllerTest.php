<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use App\Http\Controllers\Cadastro\VolumeCaixa\VolumeCaixaController;
use App\Http\Requests\Cadastro\VolumeCaixa\PostVolumeCaixaRequest;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->caixa = Caixa::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir um volume de caixa', function () {
    $id_volume_caixa = VolumeCaixa::factory()->create()->id;

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeTrue();

    delete(route('cadastro.volumeCaixa.destroy', $id_volume_caixa))
        ->assertForbidden();

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do volume da caixa', function () {
    get(route('cadastro.volumeCaixa.create', $this->caixa))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function ($action, $request) {
    $this->assertActionUsesFormRequest(
        VolumeCaixaController::class,
        $action,
        $request
    );
})->with([
    // ['store', PostVolumeCaixaRequest::class],
    ['update', PostVolumeCaixaRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    VolumeCaixa::factory(2)->create();

    concederPermissao(Permissao::VOLUME_CAIXA_VIEW_ANY);

    get(route('cadastro.volumeCaixa.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/VolumeCaixa/Index')
                ->has('volumes.data', 2)
        );
});

// test('action create compartilha os dados esperados com a view/componente correto', function () {
//     VolumeCaixa::factory()->for($this->caixa)->create();

//     $this->travel(1)->seconds();
//     $ultimo_volume_caixa_criado = VolumeCaixa::factory()->for($this->caixa)->create();

//     $this->travel(1)->seconds();
//     // volume da caixa de outra caixa, será desconsiderada
//     VolumeCaixa::factory()->create();

//     concederPermissao(Permissao::VolumeCaixaCreate);

//     get(route('cadastro.volumeCaixa.create', $this->caixa))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/VolumeCaixa/Create')
//                 ->where('ultima_insercao', [
//                     'numero' => $ultimo_volume_caixa_criado->numero,
//                 ])
//                 ->where('caixa_pai', Caixa::hierarquiaAscendente()->find($this->caixa->id)->only(['id', 'numero', 'ano', 'guarda_permanente', 'complemento', 'localidade_nome', 'predio_nome', 'andar_numero', 'andar_apelido', 'sala_numero', 'estante_numero', 'prateleira_numero', 'caixa_localidade_criadora_nome']))
//         );
// });

// test('cria um novo volume da caixa', function () {
//     concederPermissao(Permissao::VolumeCaixaCreate);

//     expect(VolumeCaixa::count())->toBe(0);

//     post(route('cadastro.volumeCaixa.store', $this->caixa), [
//         'numero' => 10,
//         'descricao' => 'foo bar',
//         'caixa_id' => $this->caixa->id,
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $volume_caixa = VolumeCaixa::first();

//     expect(VolumeCaixa::count())->toBe(1)
//         ->and($volume_caixa->numero)->toBe(10)
//         ->and($volume_caixa->descricao)->toBe('foo bar')
//         ->and($volume_caixa->caixa_id)->toBe($this->caixa->id);
// });

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    $volume_caixa = VolumeCaixa::factory()->hasProcessos(3)->create();

    get(route('cadastro.volumeCaixa.edit', $volume_caixa))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/VolumeCaixa/Edit')
                ->where('volume_caixa.data.id', $volume_caixa->id)
                ->has('processos.data', 3)
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_VIEW);

    $volume_caixa = VolumeCaixa::factory()->create();

    get(route('cadastro.volumeCaixa.edit', $volume_caixa))->assertOk();
});

test('atualiza um volume da caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    $volume_caixa = VolumeCaixa::factory()->create();

    patch(route('cadastro.volumeCaixa.update', $volume_caixa), [
        'numero' => 10,
        'descricao' => 'foo bar',
    ])
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    $volume_caixa->refresh();

    expect($volume_caixa->numero)->toBe(10)
        ->and($volume_caixa->descricao)->toBe('foo bar');
});

test('exclui o volume da caixa informada', function () {
    $id_volume_caixa = VolumeCaixa::factory()->create()->id;

    concederPermissao(Permissao::VOLUME_CAIXA_DELETE);

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeTrue();

    delete(route('cadastro.volumeCaixa.destroy', $id_volume_caixa))
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    expect(VolumeCaixa::where('id', $id_volume_caixa)->exists())->toBeFalse();
});

test('VolumeCaixaController usa trait', function () {
    expect(
        collect(class_uses(VolumeCaixaController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
