<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Prateleira\PrateleiraLivewireUpdate;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
use App\Models\Predio;
use App\Models\Andar;
use App\Models\Sala;
use App\Models\Prateleira;
use App\Models\Localidade;
use App\Models\Estante;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->prateleira = Prateleira::factory()->create(['numero' => 2]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.prateleira.edit', $this->prateleira->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.prateleira.edit', $this->prateleira->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::PrateleiraView->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('número é obrigatório', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.numero', '')
    ->call('update')
    ->assertHasErrors(['prateleira.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.numero', ['foo'])
    ->call('update')
    ->assertHasErrors(['prateleira.numero' => 'integer']);
});

test('número precisa estar entre 1 e 100000', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.numero', 0)
    ->call('update')
    ->assertHasErrors(['prateleira.numero' => 'between'])
    ->set('prateleira.numero', 100001)
    ->call('update')
    ->assertHasErrors(['prateleira.numero' => 'between']);
});

test('número e estante_id precisam ser únicos', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $estante = Estante::factory()->create();
    Prateleira::factory()->create(['numero' => 99, 'estante_id' => $estante->id]);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.numero', 99)
    ->set('prateleira.estante_id', $estante->id)
    ->call('update')
    ->assertHasErrors(['prateleira.numero' => 'unique']);
});

test('apelido é opcional', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.apelido', '')
    ->call('update')
    ->assertHasNoErrors(['prateleira.apelido']);
});

test('apelido precisa ser uma string', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.apelido', ['foo'])
    ->call('update')
    ->assertHasErrors(['prateleira.apelido' => 'string']);
});

test('apelido precisa ter no máximo 100 caracteres', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.apelido', Str::random(101))
    ->call('update')
    ->assertHasErrors(['prateleira.apelido' => 'max']);
});

test('apelido e estante_id precisam ser únicos', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $estante = Estante::factory()->create();
    Prateleira::factory()->create(['apelido' => 99, 'estante_id' => $estante->id]);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.apelido', '99')
    ->set('prateleira.estante_id', $estante->id)
    ->call('update')
    ->assertHasErrors(['prateleira.apelido' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['prateleira.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.descricao', ['foo'])
    ->call('update')
    ->assertHasErrors(['prateleira.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['prateleira.descricao' => 'max']);
});

test('localidade_id é obrigatório', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', '')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'required']);
});

test('localidade_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 'foo')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('localidade_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'exists']);
});

test('localidade_id é validado em tempo real', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', $localidade->id)
    ->assertHasNoErrors()
    ->set('localidade_id', 'foo')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('predio_id é obrigatório', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('predio_id', '')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'required']);
});

test('predio_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 'foo')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('predio_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['predio_id' => 'exists']);
});

test('predio_id é validado em tempo real', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $predio = Predio::factory()->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('predio_id', $predio->id)
    ->assertHasNoErrors()
    ->set('predio_id', 'foo')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('andar_id é obrigatório', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('andar_id', '')
    ->call('update')
    ->assertHasErrors(['andar_id' => 'required']);
});

test('andar_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('andar_id', 'foo')
    ->call('update')
    ->assertHasErrors(['andar_id' => 'integer']);
});

test('andar_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('andar_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['andar_id' => 'exists']);
});

test('andar_id é validado em tempo real', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $andar = Andar::factory()->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('andar_id', $andar->id)
    ->assertHasNoErrors()
    ->set('andar_id', 'foo')
    ->assertHasErrors(['andar_id' => 'integer']);
});

test('sala_id é obrigatório', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('sala_id', '')
    ->call('update')
    ->assertHasErrors(['sala_id' => 'required']);
});

test('sala_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('sala_id', 'foo')
    ->call('update')
    ->assertHasErrors(['sala_id' => 'integer']);
});

test('sala_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('sala_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['sala_id' => 'exists']);
});

test('sala_id é validado em tempo real', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $sala = Sala::factory()->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('sala_id', $sala->id)
    ->assertHasNoErrors()
    ->set('sala_id', 'foo')
    ->assertHasErrors(['sala_id' => 'integer']);
});

test('estante_id é obrigatório', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.estante_id', '')
    ->call('update')
    ->assertHasErrors(['prateleira.estante_id' => 'required']);
});

test('estante_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.estante_id', 'foo')
    ->call('update')
    ->assertHasErrors(['prateleira.estante_id' => 'integer']);
});

test('estante_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.estante_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['prateleira.estante_id' => 'exists']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Caixa::factory(30)->for($this->prateleira, 'prateleira')->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('caixas', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('arquivamento.cadastro.prateleira.edit', $this->prateleira->id))
    ->assertOk()
    ->assertSeeLivewire(PrateleiraLivewireUpdate::class);
})->with([
    Permissao::PrateleiraView->value,
    Permissao::PrateleiraUpdate->value
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $estante = Estante::factory()->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.numero', 1)
    ->set('prateleira.apelido', '1')
    ->set('prateleira.estante_id', $estante->id)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);
    concederPermissao(Permissao::CaixaDelete->value);

    $caixa = Caixa::factory()->for($this->prateleira, 'prateleira')->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
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

test('localidades estão disponíveis para seleção', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Localidade::factory(10)->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->assertCount('localidades', 11);
});

test('atribui null ao prédio, ao andar, à sala, à estante e disponibiliza novos prédios ao selecionar uma localidade', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $localidade = Localidade::factory()->has(Predio::factory(10), 'predios')->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('localidade_id', $localidade->id)
    ->assertSet('prateleira.predio_id', null)
    ->assertSet('prateleira.andar_id', null)
    ->assertSet('prateleira.sala_id', null)
    ->assertSet('prateleira.estante_id', null)
    ->assertCount('predios', 10);
});

test('atribui null ao andar, à sala, à estante e disponibiliza novos andares ao selecionar um prédio', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $predio = Predio::factory()->has(Andar::factory(10), 'andares')->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('predio_id', $predio->id)
    ->assertSet('prateleira.andar_id', null)
    ->assertSet('prateleira.sala_id', null)
    ->assertSet('prateleira.estante_id', null)
    ->assertCount('andares', 10);
});

test('atribui null à sala, à estente e disponibiliza novas salas ao selecionar um andar', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $andar = Andar::factory()->has(Sala::factory(10), 'salas')->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('andar_id', $andar->id)
    ->assertSet('prateleira.sala_id', null)
    ->assertSet('prateleira.estante_id', null)
    ->assertCount('salas', 10);
});

test('atribui null à estente e disponibiliza novas salas ao selecionar uma sala', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $sala = Sala::factory()->has(Estante::factory(10), 'estantes')->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('sala_id', $sala->id)
    ->assertSet('prateleira.estante_id', null)
    ->assertCount('estantes', 10);
});

test('pais são pré-selecionados', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $this->prateleira->load('estante.sala.andar.predio.localidade');

    Estante::factory(3)->for($this->prateleira->estante->sala, 'sala')->create();
    Sala::factory(4)->for($this->prateleira->estante->sala->andar, 'andar')->create();
    Andar::factory(8)->for($this->prateleira->estante->sala->andar->predio, 'predio')->create();
    Predio::factory(2)->for($this->prateleira->estante->sala->andar->predio->localidade, 'localidade')->create();
    Localidade::factory(15)->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->assertCount('localidades', 16)
    ->assertSet('localidade_id', $this->prateleira->estante->sala->andar->predio->localidade->id)
    ->assertCount('predios', 3)
    ->assertSet('predio_id', $this->prateleira->estante->sala->andar->predio->id)
    ->assertCount('andares', 9)
    ->assertSet('andar_id', $this->prateleira->estante->sala->andar->id)
    ->assertCount('salas', 5)
    ->assertSet('sala_id', $this->prateleira->estante->sala->id)
    ->assertCount('estantes', 4);
});

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    $estante = Estante::factory()->create();

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->set('modo_edicao', true)
    ->set('prateleira.numero', 99)
    ->set('prateleira.apelido', '99')
    ->set('prateleira.descricao', 'foo bar')
    ->set('prateleira.estante_id', $estante->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->prateleira->refresh();

    expect($this->prateleira->numero)->toBe(99)
    ->and($this->prateleira->apelido)->toBe('99')
    ->and($this->prateleira->descricao)->toBe('foo bar')
    ->and($this->prateleira->estante_id)->toBe($estante->id);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::PrateleiraUpdate->value);

    Livewire::test(PrateleiraLivewireUpdate::class, ['id' => $this->prateleira->id])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'caixa',
            'ano',
            'qtd_volumes',
            'acoes',
        ],
        'por_pagina' => 10
    ]);
});

test('PrateleiraLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(PrateleiraLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
