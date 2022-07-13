<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Caixa\CaixaLivewireCreate;
use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Prateleira;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->prateleira = Prateleira::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.caixa.create', $this->prateleira->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.caixa.create', $this->prateleira->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->assertForbidden();
});

// Rules
test('quantidade é obrigatório', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', '')
    ->call('store')
    ->assertHasErrors(['quantidade' => 'required']);
});

test('quantidade precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', 'foo')
    ->call('store')
    ->assertHasErrors(['quantidade' => 'integer']);
});

test('quantidade precisa ser entre 1 e 1000', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', 0)
    ->call('store')
    ->assertHasErrors(['quantidade' => 'between'])
    ->set('quantidade', 1001)
    ->call('store')
    ->assertHasErrors(['quantidade' => 'between']);
});

test('localidade_criadora_id é obrigatório', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.localidade_criadora_id', '')
    ->call('store')
    ->assertHasErrors(['caixa.localidade_criadora_id' => 'required']);
});

test('localidade_criadora_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.localidade_criadora_id', 'foo')
    ->call('store')
    ->assertHasErrors(['caixa.localidade_criadora_id' => 'integer']);
});

test('localidade_criadora_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.localidade_criadora_id', 9090909090)
    ->call('store')
    ->assertHasErrors(['caixa.localidade_criadora_id' => 'exists']);
});

test('ano é obrigatório', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.ano', '')
    ->call('store')
    ->assertHasErrors(['caixa.ano' => 'required']);
});

test('ano precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.ano', 'foo')
    ->call('store')
    ->assertHasErrors(['caixa.ano' => 'integer']);
});

test('ano precisa ser entre 1900 e o ano atual', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.ano', 1899)
    ->call('store')
    ->assertHasErrors(['caixa.ano' => 'between'])
    ->set('caixa.ano', now()->addYear()->format('Y'))
    ->call('store')
    ->assertHasErrors(['caixa.ano' => 'between']);
});

test('número é obrigatório', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.numero', '')
    ->call('store')
    ->assertHasErrors(['caixa.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.numero', 'foo')
    ->call('store')
    ->assertHasErrors(['caixa.numero' => 'integer']);
});

test('número maior ou igual a 1', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.numero', 0)
    ->call('store')
    ->assertHasErrors(['caixa.numero' => 'min']);
});

test('número, ano, guarda permanente, complemento e localidade criadora precisam ser únicos', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    $localidade = Localidade::factory()->create();

    Caixa::factory()->create([
        'ano' => 2020,
        'numero' => 10,
        'guarda_permanente' => true,
        'complemento' => 'foo',
        'localidade_criadora_id' => $localidade->id,
    ]);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.ano', 2020)
    ->set('caixa.numero', 10)
    ->set('caixa.guarda_permanente', true)
    ->set('caixa.complemento', 'foo')
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->call('store')
    ->assertHasErrors(['caixa.numero' => 'unique']);
});

test('complemento é opcional', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.complemento', '')
    ->call('store')
    ->assertHasNoErrors(['caixa.complemento']);
});

test('complemento precisa ter no máximo 50 caracteres', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.complemento', Str::random(51))
    ->call('store')
    ->assertHasErrors(['caixa.complemento' => 'max']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.descricao', '')
    ->call('store')
    ->assertHasNoErrors(['caixa.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.descricao', ['foo'])
    ->call('store')
    ->assertHasErrors(['caixa.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.descricao', Str::random(256))
    ->call('store')
    ->assertHasErrors(['caixa.descricao' => 'max']);
});

test('volumes é obrigatório', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('volumes', '')
    ->call('store')
    ->assertHasErrors(['volumes' => 'required']);
});

test('volumes precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('volumes', 'foo')
    ->call('store')
    ->assertHasErrors(['volumes' => 'integer']);
});

test('volumes precisa ser entre 1 e 1000', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('volumes', 0)
    ->call('store')
    ->assertHasErrors(['volumes' => 'between'])
    ->set('volumes', 1001)
    ->call('store')
    ->assertHasErrors(['volumes' => 'between']);
});

test('valida localidade criadora, complemento e ano ao sugerir próximo número da caixa', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    $localidade = Localidade::factory()->create();

    Caixa::factory()->for($localidade, 'localidadeCriadora')->create([
        'ano' => 2020,
        'numero' => 20,
        'guarda_permanente' => true,
        'complemento' => 'foo',
        'localidade_criadora_id' => $localidade->id,
    ]);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.localidade_criadora_id', 'foo')
    ->call('sugerirNumeroCaixa')
    ->assertHasErrors(['caixa.localidade_criadora_id' => 'integer'])
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->set('caixa.guarda_permanente', true)
    ->set('caixa.complemento', Str::random(51))
    ->call('sugerirNumeroCaixa')
    ->assertHasErrors(['caixa.complemento' => 'max'])
    ->set('caixa.complemento', 'foo')
    ->set('caixa.ano', 'bar')
    ->call('sugerirNumeroCaixa')
    ->assertHasErrors(['caixa.ano' => 'integer'])
    ->set('caixa.ano', 2020)
    ->call('sugerirNumeroCaixa')
    ->assertHasNoErrors()
    ->assertSet('caixa.numero', 21);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Caixa::factory(30)->for($this->prateleira, 'prateleira')->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('caixas', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    get(route('arquivamento.cadastro.caixa.create', $this->prateleira->id))
    ->assertOk()
    ->assertSeeLivewire(CaixaLivewireCreate::class);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', 1)
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->set('caixa.ano', 2000)
    ->set('caixa.guarda_permanente', false)
    ->set('caixa.numero', 10)
    ->set('volumes', 2)
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::CaixaCreate->value);
    concederPermissao(Permissao::CaixaDelete->value);

    $caixa = Caixa::factory()->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->call('marcarParaExcluir', $caixa->id)
    ->call('destroy')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notificacao', [
        'tipo' => Feedback::Sucesso->value,
        'icone' => Feedback::Sucesso->icone(),
        'cabecalho' => Feedback::Sucesso->nome(),
        'mensagem' => null,
        'duracao' => 3000,
    ]);
});

test('localidades criadoras estão disponíveis para seleção', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Localidade::factory(10)->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->assertCount('localidades_criadoras', 11);
});

test('sugere o próximo número da caixa (max número + 1)', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    $localidade_a = Localidade::factory()->create();
    $localidade_b = Localidade::factory()->create();

    Caixa::factory()->for($localidade_a, 'localidadeCriadora')->create([
        'ano' => 2020,
        'numero' => 20,
        'guarda_permanente' => true,
        'complemento' => 'foo',
        'localidade_criadora_id' => $localidade_a->id,
    ]);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.ano', 2020)
    ->set('caixa.guarda_permanente', true)
    ->set('caixa.complemento', 'foo')
    ->set('caixa.localidade_criadora_id', $localidade_a->id)
    ->call('sugerirNumeroCaixa')
    ->assertSet('caixa.numero', 21)
    ->set('caixa.ano', 2021)
    ->call('sugerirNumeroCaixa')
    ->assertSet('caixa.numero', 1)
    ->set('caixa.guarda_permanente', false)
    ->call('sugerirNumeroCaixa')
    ->assertSet('caixa.numero', 1)
    ->set('caixa.complemento', '')
    ->call('sugerirNumeroCaixa')
    ->assertSet('caixa.numero', 1)
    ->set('caixa.localidade_criadora_id', $localidade_b->id)
    ->call('sugerirNumeroCaixa')
    ->assertSet('caixa.numero', 1);
});

test('sem permissão para criar múltiplas caixas, a quantidade é ignorada é apenas uma caixa é criada', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', 10)
    ->set('caixa.ano', 2000)
    ->set('caixa.guarda_permanente', true)
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->set('caixa.numero', 55)
    ->set('volumes', 1)
    ->call('store')
    ->assertOk();

    expect(Caixa::count())->toBe(1);
});

test('sem permissão para criar múltiplos volumes, os volumes são ignorados e apenas um é criado para a caixa', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', 1)
    ->set('caixa.ano', 2000)
    ->set('caixa.guarda_permanente', true)
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->set('caixa.numero', 55)
    ->set('volumes', 20)
    ->call('store')
    ->assertOk();

    $caixa = Caixa::with('volumes')->first();

    expect($caixa->volumes)->toHaveCount(1)
    ->and($caixa->volumes->first()->numero)->toBe(1);
});

test('cria a quantidade de caixas definida com permissão', function () {
    concederPermissao(Permissao::CaixaCreate->value);
    concederPermissao(Permissao::CaixaCreateMany->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', 10)
    ->set('caixa.ano', 2000)
    ->set('caixa.guarda_permanente', true)
    ->set('caixa.complemento', 'foo')
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->set('caixa.numero', 55)
    ->set('caixa.descricao', 'foo bar')
    ->set('volumes', 1)
    ->assertHasNoErrors()
    ->call('store')
    ->assertOk();

    $caixas = Caixa::withCount('volumes')
            ->with('prateleira')
            ->get();

    $randomica = $caixas->random();
    $primeira = $caixas->first();
    $ultima = $caixas->last();

    expect(Caixa::count())->toBe(10)
    ->and($randomica->ano)->toBe(2000)
    ->and($primeira->numero)->toBe(55)
    ->and($ultima->numero)->toBe(64)
    ->and($randomica->guarda_permanente)->toBeTrue()
    ->and($randomica->complemento)->toBe('foo')
    ->and($randomica->localidade_criadora_id)->toBe($localidade->id)
    ->and($randomica->descricao)->toBe('foo bar')
    ->and($randomica->volumes_count)->toBe(1)
    ->and($randomica->prateleira->id)->toBe($this->prateleira->id);
});

test('cria a quantidade de volumes da caixa definida com permissão', function () {
    concederPermissao(Permissao::CaixaCreate->value);
    concederPermissao(Permissao::VolumeCaixaCreate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('quantidade', 1)
    ->set('caixa.ano', 2000)
    ->set('caixa.guarda_permanente', false)
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->set('caixa.numero', 55)
    ->set('volumes', 20)
    ->call('store')
    ->assertOk();

    $caixa = Caixa::with('volumes')->first();

    expect($caixa->volumes)->toHaveCount(20)
    ->and($caixa->volumes->first()->numero)->toBe(1)
    ->and($caixa->volumes->last()->numero)->toBe(20);
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    $localidade = Localidade::factory()->create();

    $branco = new Caixa();
    $branco->guarda_permanente = false;

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->set('caixa.ano', 2000)
    ->set('caixa.guarda_permanente', false)
    ->set('caixa.localidade_criadora_id', $localidade->id)
    ->set('caixa.numero', 55)
    ->call('store')
    ->assertOk()
    ->assertSet('caixa', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::CaixaCreate->value);

    Livewire::test(CaixaLivewireCreate::class, ['id' => $this->prateleira->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'criadora',
            'gp',
            'complemento',
            'caixa',
            'ano',
            'qtd_volumes',
            'acoes',
        ],
        'por_pagina' => 10,
    ])
    ->assertSet('quantidade', 1)
    ->assertSet('volumes', 1);
});

test('CaixaLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(CaixaLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
