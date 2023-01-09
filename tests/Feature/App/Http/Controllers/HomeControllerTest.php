<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\HomeController;
use App\Http\Requests\ShowProcessoHomeRequest;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();
    Auth::login($this->usuario);
});

// Autorização
test('usuário sem autorização não consegue pesquisar processos na página home', function () {
    post(route('home.show', ['termo' => 'abc']))->assertForbidden();
});

// Caminho feliz
test('página home dispensa autorização específica', function () {
    get(route('home.show'))->assertOk();
});

test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        HomeController::class,
        $action,
        $request
    );
})->with([
    ['show', ShowProcessoHomeRequest::class],
]);

test('action show compartilha os dados esperados com a view/componente correto para usuários com autorização específica', function () {
    Solicitacao::factory()->solicitada()->create(['destino_id' => $this->usuario->lotacao_id]);
    Solicitacao::factory(2)->entregue()->create(['destino_id' => $this->usuario->lotacao_id]);
    Solicitacao::factory(4)->devolvida()->create(['destino_id' => $this->usuario->lotacao_id]);
    Solicitacao::factory(3)->solicitada()->create();

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_VIEW_ANY);
    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    get(route('home.show'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Home/Home')
                ->whereAll([
                    'solicitacoes' => [
                        'data' => [
                            'solicitadas' => 1,
                            'entregues' => 2,
                            'devolvidas' => 4,
                        ],
                        'links' => [
                            'create' => route('solicitacao.create'),
                            'view_any' => route('solicitacao.index'),
                        ],
                    ],
                ])
        );
});

test('action show compartilha os dados esperados com a view/componente correto para usuários sem autorização específica', function () {
    Solicitacao::factory()->solicitada()->create(['destino_id' => $this->usuario->lotacao_id]);
    Solicitacao::factory(2)->entregue()->create(['destino_id' => $this->usuario->lotacao_id]);
    Solicitacao::factory(4)->devolvida()->create(['destino_id' => $this->usuario->lotacao_id]);
    Solicitacao::factory(3)->solicitada()->create();

    get(route('home.show'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Home/Home')
                ->whereAll([
                    'solicitacoes' => [
                        'data' => [
                            'solicitadas' => 1,
                            'entregues' => 2,
                            'devolvidas' => 4,
                        ],
                        'links' => [],
                    ],
                ])
        );
});

test('action show compartilha os dados esperados com view/componente diverso para usuários com autorização específica', function () {
    concederPermissao(Permissao::PROCESSO_VIEW);

    get(route('home.show'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Home/HomeProcesso')
                ->has('processo')
                ->where('links.search', route('home.show'))
        );
});

test('pesquisa e retorna o processo detalhado e sua solicitação ativa', function () {
    concederPermissao(Permissao::PROCESSO_VIEW);

    $processo = Processo::factory()->create(['numero' => '02393484420224003909']);
    $Solicitacao = Solicitacao::factory()->for($processo, 'processo')->solicitada()->create();
    Solicitacao::factory()->create();

    post(route('home.show', ['termo' => '02393484420224003909']))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Home/HomeProcesso')
                ->whereAll([
                    'processo.data.id' => $processo->id,
                    'processo.data.solicitacao_ativa.0.id' => $Solicitacao->id,
                ])
        );
});
