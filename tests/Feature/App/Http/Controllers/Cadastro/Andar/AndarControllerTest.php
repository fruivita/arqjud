<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use App\Http\Controllers\Cadastro\Andar\AndarController;
use App\Http\Requests\Cadastro\Andar\EditAndarRequest;
use App\Http\Requests\Cadastro\Andar\IndexAndarRequest;
use App\Http\Requests\Cadastro\Andar\PostAndarRequest;
use App\Models\Andar;
use App\Models\Predio;
use App\Models\Sala;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->predio = Predio::factory()->create();

    login();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem permissão não consegue excluir um andar', function () {
    $id_andar = Andar::factory()->create()->id;

    expect(Andar::where('id', $id_andar)->exists())->toBeTrue();

    delete(route('cadastro.andar.destroy', $id_andar))
        ->assertForbidden();

    expect(Andar::where('id', $id_andar)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do andar', function () {
    get(route('cadastro.andar.create', $this->predio))->assertForbidden();
});

// Caminho feliz
// test('action do controller usa o form request', function (string $action, string $request) {
//     $this->assertActionUsesFormRequest(
//         AndarController::class,
//         $action,
//         $request
//     );
// })->with([
//     ['index', IndexAndarRequest::class],
//     ['store', PostAndarRequest::class],
//     ['edit', EditAndarRequest::class],
//     ['update', PostAndarRequest::class],
// ]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Andar::factory(2)->create();

    concederPermissao(Permissao::ANDAR_VIEW_ANY);

    get(route('cadastro.andar.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Andar/Index')
                ->has('andares.data', 2)
        );
});

// test('action create compartilha os dados esperados com a view/componente correto', function () {
//     Andar::factory()->for($this->predio)->create();

//     $this->travel(1)->seconds();
//     $ultimo_andar_criado = Andar::factory()->for($this->predio)->create();

//     $this->travel(1)->seconds();
//     // andar de outro prédio, será desconsiderado
//     Andar::factory()->create();

//     concederPermissao(Permissao::AndarCreate);

//     get(route('cadastro.andar.create', $this->predio))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Andar/Create')
//                 ->where('ultima_insercao', [
//                     'numero' => $ultimo_andar_criado->numero,
//                     'apelido' => $ultimo_andar_criado->apelido,
//                 ])
//                 ->where('predio_pai', Predio::hierarquiaAscendente()->find($this->predio->id)->only(['id', 'nome', 'localidade_nome']))
//         );
// });

// test('cria um novo andar', function () {
//     concederPermissao(Permissao::AndarCreate);

//     expect(Andar::count())->toBe(0);

//     post(route('cadastro.andar.store', $this->predio), [
//         'numero' => 10,
//         'apelido' => 'foo',
//         'descricao' => 'foo bar',
//         'predio_id' => $this->predio->id,
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $andar = Andar::first();

//     expect(Andar::count())->toBe(1)
//         ->and($andar->numero)->toBe(10)
//         ->and($andar->apelido)->toBe('foo')
//         ->and($andar->descricao)->toBe('foo bar')
//         ->and($andar->predio_id)->toBe($this->predio->id);
// });

// test('action edit compartilha os dados esperados com a view/componente correto', function (bool $permissao) {
//     concederPermissao(Permissao::AndarUpdate);

//     $andar = Andar::factory()->has(Sala::factory(3), 'salas')->create();

//     if ($permissao) {
//         concederPermissao(Permissao::SalaCreate);
//         concederPermissao(Permissao::SalaUpdate);
//     }

//     get(route('cadastro.andar.edit', $andar))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Andar/Edit')
//                 ->where('andar', Andar::hierarquiaAscendente()->find($andar->id))
//                 ->has('salas.data', 3)
//                 ->has('filtros')
//                 ->where('per_page', 10)
//                 ->where('can', ['updateAndar' => true, 'createSala' => $permissao, 'viewOrUpdateSala' => $permissao])
//         );
// })->with([
//     false,
//     true,
// ]);

// test('action edit também é executável com permissão de visualização', function () {
//     concederPermissao(Permissao::AndarView);

//     $andar = Andar::factory()->create();

//     get(route('cadastro.andar.edit', $andar))
//         ->assertOk()
//         ->assertInertia(
//             fn (Assert $page) => $page
//                 ->component('Cadastro/Andar/Edit')
//                 ->where('andar', Andar::hierarquiaAscendente()->find($andar->id))
//                 ->where('can', ['updateAndar' => false, 'createSala' => false, 'viewOrUpdateSala' => false])
//         );
// });

// test('atualiza um andar', function () {
//     concederPermissao(Permissao::AndarUpdate);

//     $andar = Andar::factory()->create();

//     patch(route('cadastro.andar.update', $andar), [
//         'numero' => 10,
//         'apelido' => '10º',
//         'descricao' => 'foo bar',
//     ])
//         ->assertRedirect()
//         ->assertSessionHas('sucesso');

//     $andar->refresh();

//     expect($andar->numero)->toBe(10)
//         ->and($andar->apelido)->toBe('10º')
//         ->and($andar->descricao)->toBe('foo bar');
// });

test('exclui o andar informado', function () {
    $id_andar = Andar::factory()->create()->id;

    concederPermissao(Permissao::ANDAR_DELETE);

    expect(Andar::where('id', $id_andar)->exists())->toBeTrue();

    delete(route('cadastro.andar.destroy', $id_andar))
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    expect(Andar::where('id', $id_andar)->exists())->toBeFalse();
});

test('AndarController usa trait', function () {
    expect(
        collect(class_uses(AndarController::class))
            ->has([
                \App\Traits\ComPaginacaoEmCache::class,
                \App\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
