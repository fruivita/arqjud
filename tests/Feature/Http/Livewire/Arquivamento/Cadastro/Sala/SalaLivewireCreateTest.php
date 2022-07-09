<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Sala\SalaLivewireCreate;
use App\Models\Andar;
use App\Models\Sala;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->andar = Andar::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.sala.create', $this->andar->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.sala.create', $this->andar->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', '')
    ->call('store')
    ->assertHasErrors(['sala.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', ['foo'])
    ->call('store')
    ->assertHasErrors(['sala.numero' => 'integer']);
});

test('número precisa estar entre 1 e 100000', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', 0)
    ->call('store')
    ->assertHasErrors(['sala.numero' => 'between'])
    ->set('sala.numero', 100001)
    ->call('store')
    ->assertHasErrors(['sala.numero' => 'between']);
});

test('número e andar_id precisam ser únicos', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Sala::factory()->create(['numero' => 99, 'andar_id' => $this->andar->id]);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', 99)
    ->call('store')
    ->assertHasErrors(['sala.numero' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.descricao', '')
    ->call('store')
    ->assertHasNoErrors(['sala.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.descricao', ['foo'])
    ->call('store')
    ->assertHasErrors(['sala.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.descricao', Str::random(256))
    ->call('store')
    ->assertHasErrors(['sala.descricao' => 'max']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Sala::factory(30)->for($this->andar, 'andar')->create();

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('salas', 25);
});

test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::SalaCreate->value);

    get(route('arquivamento.cadastro.sala.create', $this->andar->id))
    ->assertOk()
    ->assertSeeLivewire(SalaLivewireCreate::class);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', 1)
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::SalaCreate->value);
    concederPermissao(Permissao::SalaDelete->value);

    $sala = Sala::factory()->create();

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->call('marcarParaExcluir', $sala->id)
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
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', 99)
    ->set('sala.descricao', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $sala = Sala::with('andar')->first();

    expect($sala->numero)->toBe('99')
    ->and($sala->descricao)->toBe('foo bar')
    ->and($sala->andar->id)->toBe($this->andar->id);
});

test('ao criar uma sala, cria também a estante e a prateleira padrão', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', 99)
    ->set('sala.descricao', 'foo bar')
    ->call('store')
    ->assertOk();

    $sala = Sala::with('estantes.prateleiras')->first();
    $estante = $sala->estantes()->first();
    $prateleira = $estante->prateleiras()->first();

    expect($sala->numero)->toBe('99')
    ->and($sala->descricao)->toBe('foo bar')
    ->and($sala->andar_id)->toBe($this->andar->id)
    ->and($estante->numero)->toBe(0)
    ->and($estante->apelido)->toBe(__('Não informada'))
    ->and($estante->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'))
    ->and($estante->sala_id)->toBe($sala->id)
    ->and($prateleira->numero)->toBe(0)
    ->and($prateleira->apelido)->toBe(__('Não informada'))
    ->and($prateleira->estante_id)->toBe($estante->id)
    ->and($prateleira->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::SalaCreate->value);

    $branco = new Sala();

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->set('sala.numero', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('sala', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::SalaCreate->value);

    Livewire::test(SalaLivewireCreate::class, ['id' => $this->andar->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'sala',
            'qtd_estantes',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('SalaLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(SalaLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
