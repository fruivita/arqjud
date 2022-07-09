<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Estante\EstanteLivewireCreate;
use App\Models\Estante;
use App\Models\Sala;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->sala = Sala::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.estante.create', $this->sala->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.estante.create', $this->sala->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', '')
    ->call('store')
    ->assertHasErrors(['estante.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', ['foo'])
    ->call('store')
    ->assertHasErrors(['estante.numero' => 'integer']);
});

test('número precisa estar entre 1 e 100000', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', 0)
    ->call('store')
    ->assertHasErrors(['estante.numero' => 'between'])
    ->set('estante.numero', 100001)
    ->call('store')
    ->assertHasErrors(['estante.numero' => 'between']);
});

test('número e sala_id precisam ser únicos', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Estante::factory()->create(['numero' => 99, 'sala_id' => $this->sala->id]);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', 99)
    ->call('store')
    ->assertHasErrors(['estante.numero' => 'unique']);
});

test('apelido é opcional', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.apelido', '')
    ->call('store')
    ->assertHasNoErrors(['estante.apelido']);
});

test('apelido precisa ser uma string', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.apelido', ['foo'])
    ->call('store')
    ->assertHasErrors(['estante.apelido' => 'string']);
});

test('apelido precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.apelido', Str::random(101))
    ->call('store')
    ->assertHasErrors(['estante.apelido' => 'max']);
});

test('apelido e sala_id precisam ser únicos', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Estante::factory()->create(['apelido' => '99', 'sala_id' => $this->sala->id]);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.apelido', '99')
    ->call('store')
    ->assertHasErrors(['estante.apelido' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.descricao', '')
    ->call('store')
    ->assertHasNoErrors(['estante.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.descricao', ['foo'])
    ->call('store')
    ->assertHasErrors(['estante.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.descricao', Str::random(256))
    ->call('store')
    ->assertHasErrors(['estante.descricao' => 'max']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Estante::factory(30)->for($this->sala, 'sala')->create();

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('estantes', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    get(route('arquivamento.cadastro.estante.create', $this->sala->id))
    ->assertOk()
    ->assertSeeLivewire(EstanteLivewireCreate::class);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', 1)
    ->set('estante.apelido', '1')
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::EstanteCreate->value);
    concederPermissao(Permissao::EstanteDelete->value);

    $estante = Estante::factory()->create();

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->call('marcarParaExcluir', $estante->id)
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

test('cria um registro com permissão', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', 99)
    ->set('estante.apelido', '99')
    ->set('estante.descricao', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $estante = Estante::with('sala')->first();

    expect($estante->numero)->toBe(99)
    ->and($estante->apelido)->toBe('99')
    ->and($estante->descricao)->toBe('foo bar')
    ->and($estante->sala->id)->toBe($this->sala->id);
});

test('ao criar uma estante, cria também a prateleira padrão', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', 99)
    ->set('estante.descricao', 'foo bar')
    ->call('store')
    ->assertOk();

    $estante = Estante::with('prateleiras')->first();
    $prateleira = $estante->prateleiras()->first();

    expect($estante->numero)->toBe(99)
    ->and($estante->descricao)->toBe('foo bar')
    ->and($estante->sala_id)->toBe($this->sala->id)
    ->and($prateleira->numero)->toBe(0)
    ->and($prateleira->apelido)->toBe(__('Não informada'))
    ->and($prateleira->estante_id)->toBe($estante->id)
    ->and($prateleira->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    $branco = new Estante();

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->set('estante.numero', 1)
    ->set('estante.apelido', '1º')
    ->call('store')
    ->assertOk()
    ->assertSet('estante', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::EstanteCreate->value);

    Livewire::test(EstanteLivewireCreate::class, ['id' => $this->sala->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'estante',
            'apelido',
            'qtd_prateleiras',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('EstanteLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(EstanteLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
