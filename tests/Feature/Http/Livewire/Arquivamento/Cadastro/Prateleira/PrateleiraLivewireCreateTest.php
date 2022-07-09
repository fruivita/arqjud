<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Prateleira\PrateleiraLivewireCreate;
use App\Models\Caixa;
use App\Models\Prateleira;
use App\Models\Estante;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->estante = Estante::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.prateleira.create', $this->estante->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.prateleira.create', $this->estante->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.numero', '')
    ->call('store')
    ->assertHasErrors(['prateleira.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.numero', ['foo'])
    ->call('store')
    ->assertHasErrors(['prateleira.numero' => 'integer']);
});

test('número precisa estar entre 1 and 100000', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.numero', 0)
    ->call('store')
    ->assertHasErrors(['prateleira.numero' => 'between'])
    ->set('prateleira.numero', 100001)
    ->call('store')
    ->assertHasErrors(['prateleira.numero' => 'between']);
});

test('número e estante_id precisam ser únicos', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Prateleira::factory()->create(['numero' => 99, 'estante_id' => $this->estante->id]);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.numero', 99)
    ->call('store')
    ->assertHasErrors(['prateleira.numero' => 'unique']);
});

test('apelido é opcional', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.apelido', '')
    ->call('store')
    ->assertHasNoErrors(['prateleira.apelido']);
});

test('apelido precisa ser uma string', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.apelido', ['foo'])
    ->call('store')
    ->assertHasErrors(['prateleira.apelido' => 'string']);
});

test('apelido precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.apelido', Str::random(101))
    ->call('store')
    ->assertHasErrors(['prateleira.apelido' => 'max']);
});

test('apelido e estante_id precisam ser únicos', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Prateleira::factory()->create(['apelido' => '99', 'estante_id' => $this->estante->id]);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.apelido', '99')
    ->call('store')
    ->assertHasErrors(['prateleira.apelido' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.descricao', '')
    ->call('store')
    ->assertHasNoErrors(['prateleira.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.descricao', ['foo'])
    ->call('store')
    ->assertHasErrors(['prateleira.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.descricao', Str::random(256))
    ->call('store')
    ->assertHasErrors(['prateleira.descricao' => 'max']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Prateleira::factory(30)->for($this->estante, 'estante')->create();

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('prateleiras', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    get(route('arquivamento.cadastro.prateleira.create', $this->estante->id))
    ->assertOk()
    ->assertSeeLivewire(PrateleiraLivewireCreate::class);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.numero', 1)
    ->set('prateleira.apelido', '1')
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);
    concederPermissao(Permissao::PrateleiraDelete->value);

    $prateleira = Prateleira::factory()->create();

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->call('marcarParaExcluir', $prateleira->id)
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
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.numero', 99)
    ->set('prateleira.apelido', '99')
    ->set('prateleira.descricao', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $prateleira = Prateleira::with('estante')->first();

    expect($prateleira->numero)->toBe(99)
    ->and($prateleira->apelido)->toBe('99')
    ->and($prateleira->descricao)->toBe('foo bar')
    ->and($prateleira->estante->id)->toBe($this->estante->id);
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    $branco = new Prateleira();

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->set('prateleira.numero', 1)
    ->set('prateleira.apelido', '1º')
    ->call('store')
    ->assertOk()
    ->assertSet('prateleira', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::PrateleiraCreate->value);

    Livewire::test(PrateleiraLivewireCreate::class, ['id' => $this->estante->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'prateleira',
            'apelido',
            'qtd_caixas',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('PrateleiraLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(PrateleiraLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
