<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Estante\EstanteLivewireUpdate;
use App\Models\Andar;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->estante = Estante::factory()->create(['numero' => 2]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.estante.edit', $this->estante->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.estante.edit', $this->estante->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::EstanteView->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.numero', '')
    ->call('update')
    ->assertHasErrors(['estante.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.numero', ['foo'])
    ->call('update')
    ->assertHasErrors(['estante.numero' => 'integer']);
});

test('número precisa estar entre 1 e 100000', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.numero', 0)
    ->call('update')
    ->assertHasErrors(['estante.numero' => 'between'])
    ->set('estante.numero', 100001)
    ->call('update')
    ->assertHasErrors(['estante.numero' => 'between']);
});

test('número e sala_id precisam ser únicos', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $sala = Sala::factory()->create();
    Estante::factory()->create(['numero' => 99, 'sala_id' => $sala->id]);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.numero', 99)
    ->set('estante.sala_id', $sala->id)
    ->call('update')
    ->assertHasErrors(['estante.numero' => 'unique']);
});

test('apelido é opcional', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.apelido', '')
    ->call('update')
    ->assertHasNoErrors(['estante.apelido']);
});

test('apelido precisa ser uma string', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.apelido', ['foo'])
    ->call('update')
    ->assertHasErrors(['estante.apelido' => 'string']);
});

test('apelido precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.apelido', Str::random(101))
    ->call('update')
    ->assertHasErrors(['estante.apelido' => 'max']);
});

test('apelido e sala_id precisam ser únicos', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $sala = Sala::factory()->create();
    Estante::factory()->create(['apelido' => 99, 'sala_id' => $sala->id]);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.apelido', '99')
    ->set('estante.sala_id', $sala->id)
    ->call('update')
    ->assertHasErrors(['estante.apelido' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['estante.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.descricao', ['foo'])
    ->call('update')
    ->assertHasErrors(['estante.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['estante.descricao' => 'max']);
});

test('localidade_id é obrigatório', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', '')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'required']);
});

test('localidade_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 'foo')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('localidade_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'exists']);
});

test('localidade_id é validado em tempo real', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', $localidade->id)
    ->assertHasNoErrors()
    ->set('localidade_id', 'foo')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('predio_id é obrigatório', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('predio_id', '')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'required']);
});

test('predio_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 'foo')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('predio_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['predio_id' => 'exists']);
});

test('predio_id é validado em tempo real', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $predio = Predio::factory()->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('predio_id', $predio->id)
    ->assertHasNoErrors()
    ->set('predio_id', 'foo')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('andar_id é obrigatório', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('andar_id', '')
    ->call('update')
    ->assertHasErrors(['andar_id' => 'required']);
});

test('andar_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('andar_id', 'foo')
    ->call('update')
    ->assertHasErrors(['andar_id' => 'integer']);
});

test('andar_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('andar_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['andar_id' => 'exists']);
});

test('andar_id é validado em tempo real', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $andar = Andar::factory()->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('andar_id', $andar->id)
    ->assertHasNoErrors()
    ->set('andar_id', 'foo')
    ->assertHasErrors(['andar_id' => 'integer']);
});

test('sala_id é obrigatório', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.sala_id', '')
    ->call('update')
    ->assertHasErrors(['estante.sala_id' => 'required']);
});

test('sala_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.sala_id', 'foo')
    ->call('update')
    ->assertHasErrors(['estante.sala_id' => 'integer']);
});

test('sala_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.sala_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['estante.sala_id' => 'exists']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Prateleira::factory(30)->for($this->estante, 'estante')->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('prateleiras', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('arquivamento.cadastro.estante.edit', $this->estante->id))
    ->assertOk()
    ->assertSeeLivewire(EstanteLivewireUpdate::class);
})->with([
    Permissao::EstanteView->value,
    Permissao::EstanteUpdate->value,
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $sala = Sala::factory()->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.numero', 1)
    ->set('estante.apelido', '1')
    ->set('estante.sala_id', $sala->id)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::EstanteUpdate->value);
    concederPermissao(Permissao::PrateleiraDelete->value);

    $prateleira = Prateleira::factory()->for($this->estante, 'estante')->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
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

test('localidades estão disponíveis para seleção', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Localidade::factory(10)->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->assertCount('localidades', 11);
});

test('atribui null ao prédio, ao andar, à sala e disponibiliza novos prédios ao selecionar uma localidade', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $localidade = Localidade::factory()->has(Predio::factory(10), 'predios')->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('localidade_id', $localidade->id)
    ->assertSet('estante.predio_id', null)
    ->assertSet('estante.andar_id', null)
    ->assertSet('estante.sala_id', null)
    ->assertCount('predios', 10);
});

test('atribui null ao andar, à sala e disponibiliza novos andares ao selecionar um prédio', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $predio = Predio::factory()->has(Andar::factory(10), 'andares')->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('predio_id', $predio->id)
    ->assertSet('estante.andar_id', null)
    ->assertSet('estante.sala_id', null)
    ->assertCount('andares', 10);
});

test('atribui null à sala e disponibiliza novas salas ao selecionar um andar', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $andar = Andar::factory()->has(Sala::factory(10), 'salas')->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('andar_id', $andar->id)
    ->assertSet('estante.sala_id', null)
    ->assertCount('salas', 10);
});

test('pais são pré-selecionados', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $this->estante->load('sala.andar.predio.localidade');

    Sala::factory(4)->for($this->estante->sala->andar, 'andar')->create();
    Andar::factory(8)->for($this->estante->sala->andar->predio, 'predio')->create();
    Predio::factory(2)->for($this->estante->sala->andar->predio->localidade, 'localidade')->create();
    Localidade::factory(15)->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->assertCount('localidades', 16)
    ->assertSet('localidade_id', $this->estante->sala->andar->predio->localidade->id)
    ->assertCount('predios', 3)
    ->assertSet('predio_id', $this->estante->sala->andar->predio->id)
    ->assertCount('andares', 9)
    ->assertSet('andar_id', $this->estante->sala->andar->id)
    ->assertCount('salas', 5);
});

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    $sala = Sala::factory()->create();

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->set('modo_edicao', true)
    ->set('estante.numero', 99)
    ->set('estante.apelido', '99')
    ->set('estante.descricao', 'foo bar')
    ->set('estante.sala_id', $sala->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->estante->refresh();

    expect($this->estante->numero)->toBe(99)
    ->and($this->estante->apelido)->toBe('99')
    ->and($this->estante->descricao)->toBe('foo bar')
    ->and($this->estante->sala_id)->toBe($sala->id);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::EstanteUpdate->value);

    Livewire::test(EstanteLivewireUpdate::class, ['id' => $this->estante->id])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'prateleira',
            'apelido',
            'qtd_caixas',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('EstanteLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(EstanteLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
