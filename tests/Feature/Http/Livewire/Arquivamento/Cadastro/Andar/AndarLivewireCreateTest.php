<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Andar\AndarLivewireCreate;
use App\Models\Andar;
use App\Models\Predio;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->predio = Predio::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.andar.create', $this->predio->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.andar.create', $this->predio->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.numero', '')
    ->call('store')
    ->assertHasErrors(['andar.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.numero', ['foo'])
    ->call('store')
    ->assertHasErrors(['andar.numero' => 'integer']);
});

test('número precisa estar entre -100 e 300', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.numero', -101)
    ->call('store')
    ->assertHasErrors(['andar.numero' => 'between'])
    ->set('andar.numero', 301)
    ->call('store')
    ->assertHasErrors(['andar.numero' => 'between']);
});

test('número e predio_id precisam ser únicos', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Andar::factory()->create(['numero' => 99, 'predio_id' => $this->predio->id]);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.numero', 99)
    ->call('store')
    ->assertHasErrors(['andar.numero' => 'unique']);
});

test('apelido é opcional', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.apelido', '')
    ->call('store')
    ->assertHasNoErrors(['andar.apelido']);
});

test('apelido precisa ser uma string', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.apelido', ['foo'])
    ->call('store')
    ->assertHasErrors(['andar.apelido' => 'string']);
});

test('apelido precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.apelido', Str::random(101))
    ->call('store')
    ->assertHasErrors(['andar.apelido' => 'max']);
});

test('apelido e predio_id precisa ser único', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Andar::factory()->create(['apelido' => '99', 'predio_id' => $this->predio->id]);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.apelido', '99')
    ->call('store')
    ->assertHasErrors(['andar.apelido' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.descricao', '')
    ->call('store')
    ->assertHasNoErrors(['andar.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.descricao', ['foo'])
    ->call('store')
    ->assertHasErrors(['andar.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.descricao', Str::random(256))
    ->call('store')
    ->assertHasErrors(['andar.descricao' => 'max']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Andar::factory(30)->for($this->predio, 'predio')->create();

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('andares', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::AndarCreate->value);

    get(route('arquivamento.cadastro.andar.create', $this->predio->id))
    ->assertOk()
    ->assertSeeLivewire(AndarLivewireCreate::class);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.numero', 1)
    ->set('andar.apelido', '1º')
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::AndarCreate->value);
    concederPermissao(Permissao::AndarDelete->value);

    $andar = Andar::factory()->create();

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->call('marcarParaExcluir', $andar->id)
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
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.numero', 99)
    ->set('andar.apelido', '99º')
    ->set('andar.descricao', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $andar = Andar::with('predio')->first();

    expect($andar->numero)->toBe(99)
    ->and($andar->apelido)->toBe('99º')
    ->and($andar->descricao)->toBe('foo bar')
    ->and($andar->predio->id)->toBe($this->predio->id);
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::AndarCreate->value);

    $branco = new Andar();

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->set('andar.numero', 1)
    ->set('andar.apelido', '1º')
    ->call('store')
    ->assertOk()
    ->assertSet('andar', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::AndarCreate->value);

    Livewire::test(AndarLivewireCreate::class, ['id' => $this->predio->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'andar',
            'apelido',
            'qtd_salas',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('AndarLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(AndarLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
