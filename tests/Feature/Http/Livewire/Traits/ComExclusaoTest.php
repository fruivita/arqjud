<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Localidade\LocalidadeLivewireIndex;
use App\Models\Localidade;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->localidade = Localidade::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não define o registro que será excluído sem permissão', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->assertOk()
    ->call('marcarParaExcluir', $this->localidade->id)
    ->assertForbidden()
    ->assertSet('exibir_modal_exclusao', false)
    ->assertSet('excluir', null);
});

test('não exclui o registro sem permissão', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    concederPermissao(Permissao::LocalidadeViewAny->value);
    concederPermissao(Permissao::LocalidadeDelete->value);

    $component = Livewire::test(LocalidadeLivewireIndex::class)
    ->call('marcarParaExcluir', $this->localidade->id)
    ->assertOk();

    revogaPermissao(Permissao::LocalidadeDelete->value);

    $component->call('destroy')
    ->assertForbidden();

    expect(Localidade::where('id', $this->localidade->id)->exists())->toBeTrue();
});

// Caminho feliz
test('emite feedback ao excluir um registro', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);
    concederPermissao(Permissao::LocalidadeDelete->value);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->call('marcarParaExcluir', $this->localidade->id)
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

test('define o registro que será excluido com permissão', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);
    concederPermissao(Permissao::LocalidadeDelete->value);

    Livewire::test(LocalidadeLivewireIndex::class)
    ->call('marcarParaExcluir', $this->localidade->id)
    ->assertOk()
    ->assertSet('exibir_modal_exclusao', true)
    ->assertSet('excluir.id', $this->localidade->id);
});

test('exclui o registro com permissão', function () {
    concederPermissao(Permissao::LocalidadeViewAny->value);
    concederPermissao(Permissao::LocalidadeDelete->value);

    expect(Localidade::where('id', $this->localidade->id)->exists())->toBeTrue();

    Livewire::test(LocalidadeLivewireIndex::class)
    ->call('marcarParaExcluir', $this->localidade->id)
    ->assertOk()
    ->call('destroy', $this->localidade->id)
    ->assertOk();

    expect(Localidade::where('id', $this->localidade->id)->doesntExist())->toBeTrue();
});
