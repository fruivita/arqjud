<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Sala\SalaLivewireUpdate;
use App\Models\Andar;
use App\Models\Estante;
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

    $this->sala = Sala::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.sala.edit', $this->sala->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.sala.edit', $this->sala->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::SalaView->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.numero', '')
    ->call('update')
    ->assertHasErrors(['sala.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.numero', ['foo'])
    ->call('update')
    ->assertHasErrors(['sala.numero' => 'integer']);
});

test('número precisa estar entre 1 e 100000', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.numero', 0)
    ->call('update')
    ->assertHasErrors(['sala.numero' => 'between'])
    ->set('sala.numero', 100001)
    ->call('update')
    ->assertHasErrors(['sala.numero' => 'between']);
});

test('número e andar_id precisam ser únicos', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $andar = Andar::factory()->create();
    Sala::factory()->create(['numero' => 99, 'andar_id' => $andar->id]);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.numero', 99)
    ->set('sala.andar_id', $andar->id)
    ->call('update')
    ->assertHasErrors(['sala.numero' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['sala.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.descricao', ['foo'])
    ->call('update')
    ->assertHasErrors(['sala.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['sala.descricao' => 'max']);
});

test('localidade_id é obrigatório', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', '')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'required']);
});

test('localidade_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 'foo')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('localidade_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'exists']);
});

test('localidade_id é validado em tempo real', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', $localidade->id)
    ->assertHasNoErrors()
    ->set('localidade_id', 'foo')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('predio_id é obrigatório', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('predio_id', '')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'required']);
});

test('predio_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 'foo')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('predio_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['predio_id' => 'exists']);
});

test('predio_id é validado em tempo real', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $predio = Predio::factory()->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('predio_id', $predio->id)
    ->assertHasNoErrors()
    ->set('predio_id', 'foo')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('andar_id é obrigatório', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.andar_id', '')
    ->call('update')
    ->assertHasErrors(['sala.andar_id' => 'required']);
});

test('andar_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.andar_id', 'foo')
    ->call('update')
    ->assertHasErrors(['sala.andar_id' => 'integer']);
});

test('andar_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.andar_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['sala.andar_id' => 'exists']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Estante::factory(30)->for($this->sala, 'sala')->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('estantes', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('arquivamento.cadastro.sala.edit', $this->sala->id))
    ->assertOk()
    ->assertSeeLivewire(SalaLivewireUpdate::class);
})->with([
    Permissao::SalaView->value,
    Permissao::SalaUpdate->value,
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $andar = Andar::factory()->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.numero', 1)
    ->set('sala.andar_id', $andar->id)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::SalaUpdate->value);
    concederPermissao(Permissao::EstanteDelete->value);

    $estante = Estante::factory()->for($this->sala, 'sala')->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
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

test('localidades estão disponíveis para seleção', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Localidade::factory(10)->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->assertCount('localidades', 11);
});

test('atribui null ao prédio, ao andar e disponibiliza novos prédios ao selecionar uma localidade', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $localidade = Localidade::factory()->has(Predio::factory(10), 'predios')->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('localidade_id', $localidade->id)
    ->assertSet('sala.predio_id', null)
    ->assertSet('sala.andar_id', null)
    ->assertCount('predios', 10);
});

test('atribui null ao andar e disponibiliza novos andares ao selecionar um prédio', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $predio = Predio::factory()->has(Andar::factory(10), 'andares')->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('predio_id', $predio->id)
    ->assertSet('sala.andar_id', null)
    ->assertCount('andares', 10);
});

test('pais são pré-selecionados', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $this->sala->load('andar.predio.localidade');

    Andar::factory(8)->for($this->sala->andar->predio, 'predio')->create();
    Predio::factory(2)->for($this->sala->andar->predio->localidade, 'localidade')->create();
    Localidade::factory(15)->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->assertCount('localidades', 16)
    ->assertSet('localidade_id', $this->sala->andar->predio->localidade->id)
    ->assertCount('predios', 3)
    ->assertSet('predio_id', $this->sala->andar->predio->id)
    ->assertCount('andares', 9);
});

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    $andar = Andar::factory()->create();

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->set('modo_edicao', true)
    ->set('sala.numero', 99)
    ->set('sala.descricao', 'foo bar')
    ->set('sala.andar_id', $andar->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->sala->refresh();

    expect($this->sala->numero)->toBe('99')
    ->and($this->sala->descricao)->toBe('foo bar')
    ->and($this->sala->andar_id)->toBe($andar->id);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::SalaUpdate->value);

    Livewire::test(SalaLivewireUpdate::class, ['id' => $this->sala->id])
    ->assertSet('modo_edicao', false)
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

test('SalaLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(SalaLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
