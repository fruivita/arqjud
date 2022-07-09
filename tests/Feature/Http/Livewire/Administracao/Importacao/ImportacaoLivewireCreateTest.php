<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Importacao;
use App\Enums\Permissao;
use App\Http\Livewire\Administracao\Importacao\ImportacaoLivewireCreate;
use App\Jobs\ImportarEstruturaCorporativa;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('administracao.importacao.create'))->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('administracao.importacao.create'))->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(ImportacaoLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('importacoes é obrigatório', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);

    Livewire::test(ImportacaoLivewireCreate::class)
    ->set('importacoes', [])
    ->call('store')
    ->assertHasErrors(['importacoes' => 'required']);
});

test('importacoes precisa ser um array', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);

    Livewire::test(ImportacaoLivewireCreate::class)
    ->set('importacoes', Importacao::Corporativo->value)
    ->call('store')
    ->assertHasErrors(['importacoes' => 'array']);
});

test('não aceita nada fora das opções oferecidas', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);

    Livewire::test(ImportacaoLivewireCreate::class)
    ->set('importacoes', ['foo'])
    ->call('store')
    ->assertHasErrors(['importacoes' => 'in']);
});

// Caminho feliz
test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);

    get(route('administracao.importacao.create'))
    ->assertOk()
    ->assertSeeLivewire(ImportacaoLivewireCreate::class);
});

test('dispara o job ImportarEstruturaCorporativa', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);
    Bus::fake();

    Livewire::test(ImportacaoLivewireCreate::class)
    ->set('importacoes', [Importacao::Corporativo->value])
    ->call('store');

    Bus::assertDispatched(ImportarEstruturaCorporativa::class);
});

test('dispara todos os jobs', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);
    Bus::fake();

    Livewire::test(ImportacaoLivewireCreate::class)
    ->set('importacoes', [Importacao::Corporativo->value])
    ->call('store');

    Bus::assertDispatched(ImportarEstruturaCorporativa::class);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);

    Livewire::test(ImportacaoLivewireCreate::class)
    ->assertSet('importacoes', []);
});

test('emite evento de feedback ao requisitar a importação com sucesso', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);

    Livewire::test(ImportacaoLivewireCreate::class)
    ->set('importacoes', [Importacao::Corporativo->value])
    ->call('store')
    ->assertDispatchedBrowserEvent('notificacao', [
        'tipo' => Feedback::Sucesso->value,
        'icone' => Feedback::Sucesso->icone(),
        'cabecalho' => Feedback::Sucesso->nome(),
        'mensagem' => __('A importação dos dados solicitada foi escalonada para execução. Em alguns minutos, os dados estarão disponíveis.'),
        'duracao' => 10000,
    ]);
});
