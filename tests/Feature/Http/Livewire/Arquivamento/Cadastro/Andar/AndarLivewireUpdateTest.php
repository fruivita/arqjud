<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Andar\AndarLivewireUpdate;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Sala;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->andar = Andar::factory()->create(['numero' => 2]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.andar.edit', $this->andar->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.andar.edit', $this->andar->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::AndarView->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.numero', '')
    ->call('update')
    ->assertHasErrors(['andar.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.numero', ['foo'])
    ->call('update')
    ->assertHasErrors(['andar.numero' => 'integer']);
});

test('número precisa estar entre -100 e 300', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.numero', -101)
    ->call('update')
    ->assertHasErrors(['andar.numero' => 'between'])
    ->set('andar.numero', 301)
    ->call('update')
    ->assertHasErrors(['andar.numero' => 'between']);
});

test('número e predio_id precisam ser únicos', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    $predio = Predio::factory()->create();
    Andar::factory()->create(['numero' => 99, 'predio_id' => $predio->id]);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.numero', 99)
    ->set('andar.predio_id', $predio->id)
    ->call('update')
    ->assertHasErrors(['andar.numero' => 'unique']);
});

test('apelido é opcional', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.apelido', '')
    ->call('update')
    ->assertHasNoErrors(['andar.apelido']);
});

test('apelido precisa ser uma string', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.apelido', ['foo'])
    ->call('update')
    ->assertHasErrors(['andar.apelido' => 'string']);
});

test('apelido precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.apelido', Str::random(101))
    ->call('update')
    ->assertHasErrors(['andar.apelido' => 'max']);
});

test('apelido e predio_id precisam ser únicos', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    $predio = Predio::factory()->create();
    Andar::factory()->create(['apelido' => '99', 'predio_id' => $predio->id]);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.apelido', '99')
    ->set('andar.predio_id', $predio->id)
    ->call('update')
    ->assertHasErrors(['andar.apelido' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['andar.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.descricao', ['foo'])
    ->call('update')
    ->assertHasErrors(['andar.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['andar.descricao' => 'max']);
});

test('localidade_id é obrigatório', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', '')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'required']);
});

test('localidade_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 'foo')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('localidade_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'exists']);
});

test('localidade_id é validado em tempo real', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', $localidade->id)
    ->assertHasNoErrors()
    ->set('localidade_id', 'foo')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('predio_id é obrigatório', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.predio_id', '')
    ->call('update')
    ->assertHasErrors(['andar.predio_id' => 'required']);
});

test('predio_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.predio_id', 'foo')
    ->call('update')
    ->assertHasErrors(['andar.predio_id' => 'integer']);
});

test('predio_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.predio_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['andar.predio_id' => 'exists']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Sala::factory(30)->for($this->andar, 'andar')->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('salas', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('arquivamento.cadastro.andar.edit', $this->andar->id))
    ->assertOk()
    ->assertSeeLivewire(AndarLivewireUpdate::class);
})->with([
    Permissao::AndarView->value,
    Permissao::AndarUpdate->value,
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    $predio = Predio::factory()->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.numero', 1)
    ->set('andar.apelido', '1º')
    ->set('andar.predio_id', $predio->id)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::AndarUpdate->value);
    concederPermissao(Permissao::SalaDelete->value);

    $sala = Sala::factory()->for($this->andar, 'andar')->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
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

test('localidades estão disponíveis para seleção', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Localidade::factory(10)->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->assertCount('localidades', 11);
});

test('atribui null ao prédio e disponibiliza novos prédios ao selecionar uma localidade', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    $localidade = Localidade::factory()->has(Predio::factory(10), 'predios')->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('localidade_id', $localidade->id)
    ->assertSet('andar.predio_id', null)
    ->assertCount('predios', 10);
});

test('pais são pré-selecionados', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    $this->andar->load('predio.localidade');

    Predio::factory(2)->for($this->andar->predio->localidade, 'localidade')->create();
    Localidade::factory(15)->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->assertCount('localidades', 16)
    ->assertSet('localidade_id', $this->andar->predio->localidade->id)
    ->assertCount('predios', 3);
});

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    $predio = Predio::factory()->create();

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.numero', 99)
    ->set('andar.apelido', '99º')
    ->set('andar.descricao', 'foo bar')
    ->set('andar.predio_id', $predio->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->andar->refresh();

    expect($this->andar->numero)->toBe(99)
    ->and($this->andar->apelido)->toBe('99º')
    ->and($this->andar->descricao)->toBe('foo bar')
    ->and($this->andar->predio_id)->toBe($predio->id);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'sala',
            'qtd_estantes',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('AndarLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(AndarLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
