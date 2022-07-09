<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Caixa\CaixaLivewireUpdate;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use App\Models\VolumeCaixa;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->caixa = Caixa::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('arquivamento.cadastro.caixa.edit', $this->caixa->id))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('arquivamento.cadastro.caixa.edit', $this->caixa->id))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->assertForbidden();
});

test('não cria um volume da caixa sem permissão', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->call('storeVolume')
    ->assertForbidden();

    expect($this->caixa->volumes()->doesntExist())->toBeTrue();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::CaixaView->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('localidade_id é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', '')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'required']);
});

test('localidade_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 'foo')
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('localidade_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['localidade_id' => 'exists']);
});

test('localidade_id é validado em tempo real', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $localidade = Localidade::factory()->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('localidade_id', $localidade->id)
    ->assertHasNoErrors()
    ->set('localidade_id', 'foo')
    ->assertHasErrors(['localidade_id' => 'integer']);
});

test('predio_id é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('predio_id', '')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'required']);
});

test('predio_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 'foo')
    ->call('update')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('predio_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('predio_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['predio_id' => 'exists']);
});

test('predio_id é validado em tempo real', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $predio = Predio::factory()->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('predio_id', $predio->id)
    ->assertHasNoErrors()
    ->set('predio_id', 'foo')
    ->assertHasErrors(['predio_id' => 'integer']);
});

test('andar_id é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('andar_id', '')
    ->call('update')
    ->assertHasErrors(['andar_id' => 'required']);
});

test('andar_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('andar_id', 'foo')
    ->call('update')
    ->assertHasErrors(['andar_id' => 'integer']);
});

test('andar_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('andar_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['andar_id' => 'exists']);
});

test('andar_id é validado em tempo real', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $andar = Andar::factory()->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('andar_id', $andar->id)
    ->assertHasNoErrors()
    ->set('andar_id', 'foo')
    ->assertHasErrors(['andar_id' => 'integer']);
});

test('sala_id é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('sala_id', '')
    ->call('update')
    ->assertHasErrors(['sala_id' => 'required']);
});

test('sala_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('sala_id', 'foo')
    ->call('update')
    ->assertHasErrors(['sala_id' => 'integer']);
});

test('sala_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('sala_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['sala_id' => 'exists']);
});

test('sala_id é validado em tempo real', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $sala = Sala::factory()->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('sala_id', $sala->id)
    ->assertHasNoErrors()
    ->set('sala_id', 'foo')
    ->assertHasErrors(['sala_id' => 'integer']);
});

test('estante_id é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('estante_id', '')
    ->call('update')
    ->assertHasErrors(['estante_id' => 'required']);
});

test('estante_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('estante_id', 'foo')
    ->call('update')
    ->assertHasErrors(['estante_id' => 'integer']);
});

test('estante_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('estante_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['estante_id' => 'exists']);
});

test('estante_id é validado em tempo real', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $estante = Estante::factory()->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('estante_id', $estante->id)
    ->assertHasNoErrors()
    ->set('estante_id', 'foo')
    ->assertHasErrors(['estante_id' => 'integer']);
});

test('prateleira_id é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.prateleira_id', '')
    ->call('update')
    ->assertHasErrors(['caixa.prateleira_id' => 'required']);
});

test('prateleira_id precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.prateleira_id', 'foo')
    ->call('update')
    ->assertHasErrors(['caixa.prateleira_id' => 'integer']);
});

test('prateleira_id precisa existir previamente no banco de dados', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.prateleira_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['caixa.prateleira_id' => 'exists']);
});

test('ano é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.ano', '')
    ->call('update')
    ->assertHasErrors(['caixa.ano' => 'required']);
});

test('ano precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.ano', 'foo')
    ->call('update')
    ->assertHasErrors(['caixa.ano' => 'integer']);
});

test('ano precisa ser entre 1900 e o ano atual', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.ano', 1899)
    ->call('update')
    ->assertHasErrors(['caixa.ano' => 'between'])
    ->set('caixa.ano', now()->addYear()->format('Y'))
    ->call('update')
    ->assertHasErrors(['caixa.ano' => 'between']);
});

test('número é obrigatório', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.numero', '')
    ->call('update')
    ->assertHasErrors(['caixa.numero' => 'required']);
});

test('número precisa ser um inteiro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.numero', 'foo')
    ->call('update')
    ->assertHasErrors(['caixa.numero' => 'integer']);
});

test('número precisa ser maior ou igual a 1', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.numero', 0)
    ->call('update')
    ->assertHasErrors(['caixa.numero' => 'min']);
});

test('número e ano precisam ser únicos', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Caixa::factory()->create(['ano' => 2020, 'numero' => 10]);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.ano', 2020)
    ->set('caixa.numero', 10)
    ->call('update')
    ->assertHasErrors(['caixa.numero' => 'unique']);
});

test('descrição é opcional', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.descricao', '')
    ->call('update')
    ->assertHasNoErrors(['caixa.descricao']);
});

test('descrição precisa ser uma string', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.descricao', ['foo'])
    ->call('update')
    ->assertHasErrors(['caixa.descricao' => 'string']);
});

test('descrição precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.descricao', Str::random(256))
    ->call('update')
    ->assertHasErrors(['caixa.descricao' => 'max']);
});

test('numero do volume da caixa precisa estar entre 1 e 1000', function () {
    concederPermissao(Permissao::CaixaUpdate->value);
    concederPermissao(Permissao::VolumeCaixaCreate->value);

    VolumeCaixa::factory()->for($this->caixa, 'caixa')->create(['numero' => 1000]);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->call('storeVolume')
    ->assertHasErrors(['volume' => 'between']);
});

// Caminho feliz
test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    VolumeCaixa::factory(30)->for($this->caixa, 'caixa')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('volumes', 25);
});

test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('arquivamento.cadastro.caixa.edit', $this->caixa->id))
    ->assertOk()
    ->assertSeeLivewire(CaixaLivewireUpdate::class);
})->with([
    Permissao::CaixaView->value,
    Permissao::CaixaUpdate->value,
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $prateleira = Prateleira::factory()->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.ano', 2000)
    ->set('caixa.numero', 10)
    ->set('caixa.prateleira_id', $prateleira->id)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao criar um volume de caixa', function () {
    concederPermissao(Permissao::CaixaUpdate->value);
    concederPermissao(Permissao::VolumeCaixaCreate->value);

    VolumeCaixa::factory()->for($this->caixa, 'caixa')->create(['numero' => 10]);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->call('storeVolume')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notificacao', [
        'tipo' => Feedback::Sucesso->value,
        'icone' => Feedback::Sucesso->icone(),
        'cabecalho' => Feedback::Sucesso->nome(),
        'mensagem' => '11', // 10 + 1 (número de volumes de caixa criadas)
        'duracao' => 3000,
    ]);
});

test('emite evento de feedback ao excluir um volume de caixa', function () {
    concederPermissao(Permissao::CaixaUpdate->value);
    concederPermissao(Permissao::VolumeCaixaDelete->value);

    $volume = VolumeCaixa::factory()->for($this->caixa, 'caixa')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->call('marcarParaExcluir', $volume->id)
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
    concederPermissao(Permissao::CaixaUpdate->value);

    Localidade::factory(10)->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->assertCount('localidades', 11);
});

test('atribui null ao prédio, ao andar, à sala, à estante, à prateleira e disponibiliza novos prédios ao selecionar uma localidade', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $localidade = Localidade::factory()->has(Predio::factory(10), 'predios')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('localidade_id', $localidade->id)
    ->assertSet('caixa.predio_id', null)
    ->assertSet('caixa.andar_id', null)
    ->assertSet('caixa.sala_id', null)
    ->assertSet('caixa.estante_id', null)
    ->assertSet('caixa.prateleira_id', null)
    ->assertCount('predios', 10);
});

test('atribui null ao andar, à sala, à estante, à prateleira e disponibiliza novos andares ao selecionar um prédio', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $predio = Predio::factory()->has(Andar::factory(10), 'andares')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('predio_id', $predio->id)
    ->assertSet('caixa.andar_id', null)
    ->assertSet('caixa.sala_id', null)
    ->assertSet('caixa.estante_id', null)
    ->assertSet('caixa.prateleira_id', null)
    ->assertCount('andares', 10);
});

test('atribui null à sala, à estante, à prateleira e disponibiliza novos salas ao selecionar um andar', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $andar = Andar::factory()->has(Sala::factory(10), 'salas')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('andar_id', $andar->id)
    ->assertSet('caixa.sala_id', null)
    ->assertSet('caixa.estante_id', null)
    ->assertSet('caixa.prateleira_id', null)
    ->assertCount('salas', 10);
});

test('atribui null à estante, à prateleira e disponibiliza novos estantes ao selecionar uma sala', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $sala = Sala::factory()->has(Estante::factory(10), 'estantes')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('sala_id', $sala->id)
    ->assertSet('caixa.estante_id', null)
    ->assertSet('caixa.prateleira_id', null)
    ->assertCount('estantes', 10);
});

test('atribui null à prateleira e disponibiliza novos prateleiras ao selecionar uma estante', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $estante = Estante::factory()->has(Prateleira::factory(10), 'prateleiras')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('estante_id', $estante->id)
    ->assertSet('caixa.prateleira_id', null)
    ->assertCount('prateleiras', 10);
});

test('pais são pré-selecionados', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $this->caixa->load('prateleira.estante.sala.andar.predio.localidade');

    Prateleira::factory(5)->for($this->caixa->prateleira->estante, 'estante')->create();
    Estante::factory(3)->for($this->caixa->prateleira->estante->sala, 'sala')->create();
    Sala::factory(4)->for($this->caixa->prateleira->estante->sala->andar, 'andar')->create();
    Andar::factory(8)->for($this->caixa->prateleira->estante->sala->andar->predio, 'predio')->create();
    Predio::factory(2)->for($this->caixa->prateleira->estante->sala->andar->predio->localidade, 'localidade')->create();
    Localidade::factory(15)->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->assertCount('localidades', 16)
    ->assertSet('localidade_id', $this->caixa->prateleira->estante->sala->andar->predio->localidade->id)
    ->assertCount('predios', 3)
    ->assertSet('predio_id', $this->caixa->prateleira->estante->sala->andar->predio->id)
    ->assertCount('andares', 9)
    ->assertSet('andar_id', $this->caixa->prateleira->estante->sala->andar->id)
    ->assertCount('salas', 5)
    ->assertSet('sala_id', $this->caixa->prateleira->estante->sala->id)
    ->assertCount('estantes', 4)
    ->assertSet('estante_id', $this->caixa->prateleira->estante->id)
    ->assertCount('prateleiras', 6);
});

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    $prateleira = Prateleira::factory()->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->set('modo_edicao', true)
    ->set('caixa.ano', 2000)
    ->set('caixa.numero', 55)
    ->set('caixa.descricao', 'foo bar')
    ->set('caixa.prateleira_id', $prateleira->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->caixa->refresh();

    expect($this->caixa->ano)->toBe(2000)
    ->and($this->caixa->numero)->toBe(55)
    ->and($this->caixa->descricao)->toBe('foo bar')
    ->and($this->caixa->prateleira_id)->toBe($prateleira->id);
});

test('cria um volume de caixa com permissão', function () {
    concederPermissao(Permissao::CaixaUpdate->value);
    concederPermissao(Permissao::VolumeCaixaCreate->value);

    VolumeCaixa::factory()->for($this->caixa, 'caixa')->create(['numero' => 10]);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->call('storeVolume')
    ->assertOk();

    $volume_caixa = $this->caixa->volumes()->firstWhere('numero', 11);

    expect($volume_caixa->apelido)->toBe('Vol. 11');
});

test('exclui um volume de caixa com permissão', function () {
    concederPermissao(Permissao::CaixaUpdate->value);
    concederPermissao(Permissao::VolumeCaixaDelete->value);

    $volume = VolumeCaixa::factory()->for($this->caixa, 'caixa')->create();

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->call('marcarParaExcluir', $volume->id)
    ->call('destroy')
    ->assertOk();

    expect(VolumeCaixa::where('id', $volume->id)->doesntExist())->toBeTrue();
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::CaixaUpdate->value);

    Livewire::test(CaixaLivewireUpdate::class, ['id' => $this->caixa->id])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'volume',
            'apelido',
            'acoes',
        ],
        'por_pagina' => 10,
    ]);
});

test('CaixaLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(CaixaLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
