<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Cadastro\Processo\ProcessoController;
use App\Http\Requests\Cadastro\Processo\StoreProcessoRequest;
use App\Http\Requests\Cadastro\Processo\UpdateProcessoRequest;
use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\Processo\ProcessoResource;
use App\Models\Caixa;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->caixa = Caixa::factory()->create();

    Auth::login(Usuario::factory()->create());
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue excluir um processo', function () {
    $id_processo = Processo::factory()->create()->id;

    expect(Processo::where('id', $id_processo)->exists())->toBeTrue();

    delete(route('cadastro.processo.destroy', $id_processo))->assertForbidden();

    expect(Processo::where('id', $id_processo)->exists())->toBeTrue();
});

test('usuário sem permissão não consegue exibir formulário de criação do processo', function () {
    get(route('cadastro.processo.create', $this->caixa))->assertForbidden();
});

// Caminho feliz
test('action do controller usa o form request', function (string $action, string $request) {
    $this->assertActionUsesFormRequest(
        ProcessoController::class,
        $action,
        $request
    );
})->with([
    ['store', StoreProcessoRequest::class],
    ['update', UpdateProcessoRequest::class],
]);

test('action index compartilha os dados esperados com a view/componente correto', function () {
    Processo::factory(2)->create();

    concederPermissao(Permissao::PROCESSO_VIEW_ANY);

    get(route('cadastro.processo.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Processo/Index')
                ->has('processos.data', 2)
                ->has('processos.meta.termo')
                ->has('processos.meta.order')
        );
});

test('action create compartilha os dados esperados com a view/componente correto', function () {
    Processo::factory()->for($this->caixa)->create();

    $this->travel(1)->seconds();
    $ultimo_processo_criado = Processo::factory()->for($this->caixa)->create();

    $this->travel(1)->seconds();
    // processo de outra caixa, será desconsiderado
    Processo::factory()->create();

    concederPermissao(Permissao::PROCESSO_CREATE);

    get(route('cadastro.processo.create', $this->caixa))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Processo/Create')
                ->whereAll([
                    'ultima_insercao.data' => ProcessoResource::make($ultimo_processo_criado)->resolve(),
                    'caixa' => CaixaResource::make($this->caixa->load(['localidadeCriadora', 'prateleira.estante.sala.andar.predio.localidade', 'tipoProcesso']))->response()->getData(true),
                ])
        );
});

test('cria um novo processo com a guarda definida pela caixa', function (bool $gp) {
    concederPermissao(Permissao::PROCESSO_CREATE);

    $this->caixa->guarda_permanente = $gp;
    $this->caixa->save();

    expect(Processo::count())->toBe(0);

    post(route('cadastro.processo.store', $this->caixa), [
        'numero' => '1357900-66.2022.3.00.3639',
        'numero_antigo' => '9352203-94.2022.7.06.2096',
        'gp' => $gp,
        'arquivado_em' => '20-12-2020',
        'vol_caixa_inicial' => 5,
        'vol_caixa_final' => 8,
        'qtd_volumes' => 10,
        'descricao' => 'foo bar',
        'caixa_id' => $this->caixa->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $processo = Processo::first();

    expect(Processo::count())->toBe(1)
        ->and($processo->numero)->toBe('1357900-66.2022.3.00.3639')
        ->and($processo->numero_antigo)->toBe('9352203-94.2022.7.06.2096')
        ->and($processo->guarda_permanente)->toBe($gp)
        ->and($processo->arquivado_em->format('d-m-Y'))->toBe('20-12-2020')
        ->and($processo->vol_caixa_inicial)->toBe(5)
        ->and($processo->vol_caixa_final)->toBe(8)
        ->and($processo->qtd_volumes)->toBe(10)
        ->and($processo->descricao)->toBe('foo bar')
        ->and($processo->caixa_id)->toBe($this->caixa->id)
        ->and($processo->processo_pai_id)->toBeNull();
})->with([
    true,
    false,
]);

test('é possível criar o relacionamento com o processo pai ao criar um processo', function () {
    concederPermissao(Permissao::PROCESSO_CREATE);

    expect(Processo::count())->toBe(0);

    $processo_pai = Processo::factory()->create();

    post(route('cadastro.processo.store', $this->caixa), [
        'numero' => '1357900-66.2022.3.00.3639',
        'processo_pai_numero' => $processo_pai->numero,
        'arquivado_em' => '20-12-2020',
        'vol_caixa_inicial' => 5,
        'vol_caixa_final' => 8,
        'qtd_volumes' => 10,
        'caixa_id' => $this->caixa->id,
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $processo = Processo::firstWhere('numero', '13579006620223003639');

    expect(Processo::count())->toBe(2)
        ->and($processo->numero)->toBe('1357900-66.2022.3.00.3639')
        ->and($processo->processo_pai_id)->toBe($processo_pai->id);
});

test('action edit compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::PROCESSO_UPDATE);

    $processo = Processo::factory()->hasProcessosFilho(3)->hasSolicitacoes(4)->create();

    $processo->load(['caixa.prateleira.estante.sala.andar.predio.localidade', 'caixa.localidadeCriadora', 'caixa.tipoProcesso', 'processoPai']);

    get(route('cadastro.processo.edit', $processo))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Cadastro/Processo/Edit')
                ->where('processo', ProcessoResource::make($processo)->response()->getData(true))
                ->has('processos_filho.data', 3)
                ->has('processos_filho.meta.order')
        );
});

test('action edit também é executável com permissão de visualização', function () {
    concederPermissao(Permissao::PROCESSO_VIEW);

    $processo = Processo::factory()->create();

    get(route('cadastro.processo.edit', $processo))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Cadastro/Processo/Edit')
        );
});

test('atualiza um processo, mas não altera o atributo guarda permanente', function () {
    concederPermissao(Permissao::PROCESSO_UPDATE);

    $processo = Processo::factory()->create(['guarda_permanente' => true]);
    $pai = Processo::factory()->create();

    patch(route('cadastro.processo.update', $processo), [
        'numero' => '02393484420224003909',
        'numero_antigo' => '18960718119064902226',
        'processo_pai_numero' => $pai->numero,
        'arquivado_em' => '21-01-2000',
        'vol_caixa_inicial' => 5,
        'vol_caixa_final' => 8,
        'qtd_volumes' => 15,
        'descricao' => 'foo bar',
    ])
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $processo->refresh();

    expect($processo->numero)->toBe('0239348-44.2022.4.00.3909')
        ->and($processo->numero_antigo)->toBe('1896071-81.1906.4.90.2226')
        ->and($processo->processo_pai_id)->toBe($pai->id)
        ->and($processo->arquivado_em->format('d-m-Y'))->toBe('21-01-2000')
        ->and($processo->guarda_permanente)->toBeTrue()
        ->and($processo->vol_caixa_inicial)->toBe(5)
        ->and($processo->vol_caixa_final)->toBe(8)
        ->and($processo->qtd_volumes)->toBe(15)
        ->and($processo->descricao)->toBe('foo bar');
});

test('exclui o processo informado', function () {
    $id_processo = Processo::factory()->create()->id;

    concederPermissao(Permissao::PROCESSO_DELETE);

    expect(Processo::where('id', $id_processo)->exists())->toBeTrue();

    delete(route('cadastro.processo.destroy', $id_processo))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    expect(Processo::where('id', $id_processo)->exists())->toBeFalse();
});

test('ProcessoController usa trait', function () {
    expect(
        collect(class_uses(ProcessoController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
