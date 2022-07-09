<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Administracao\Documentacao\DocumentacaoLivewireCreate;
use App\Models\Documentacao;
use App\Rules\RotaExiste;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
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

    get(route('administracao.documentacao.create'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('administracao.documentacao.create'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(DocumentacaoLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('app link é obrigatório', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', '')
    ->call('store')
    ->assertHasErrors(['documentacao.app_link' => 'required']);
});

test('app link precisa ser uma string', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', ['bar'])
    ->call('store')
    ->assertHasErrors(['documentacao.app_link' => 'string']);
});

test('app link precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', Str::random(256))
    ->call('store')
    ->assertHasErrors(['documentacao.app_link' => 'max']);
});

test('app link precisa existir na aplicação', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', 'foo')
    ->call('store')
    ->assertHasErrors(['documentacao.app_link' => RotaExiste::class]);
});

test('app link precisa ser único', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Documentacao::factory()->create(['app_link' => 'administracao.log.index']);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', 'administracao.log.index')
    ->call('store')
    ->assertHasErrors(['documentacao.app_link' => 'unique']);
});

test('doc link é opcional', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.doc_link', '')
    ->call('store')
    ->assertHasNoErrors(['documentacao.doc_link']);
});

test('doc link precisa ser uma string', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.doc_link', ['bar'])
    ->call('store')
    ->assertHasErrors(['documentacao.doc_link' => 'string']);
});

test('doc link precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.doc_link', Str::random(256))
    ->call('store')
    ->assertHasErrors(['documentacao.doc_link' => 'max']);
});

test('doc link precisa ser uma url válida', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.doc_link', 'foo')
    ->call('store')
    ->assertHasErrors(['documentacao.doc_link' => 'url']);
});

// Caminho feliz
test('renderiza o componente com permissão', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    get(route('administracao.documentacao.create'))
    ->assertOk()
    ->assertSeeLivewire(DocumentacaoLivewireCreate::class);
});

test('paginação retorna a quantidade de registros esperada', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Documentacao::factory(30)->create();

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('documentacoes', 25);
});

test('emite evento de feedback ao criar um registro', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', 'administracao.log.index')
    ->call('store')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('emite evento de feedback ao excluir um registro', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);
    concederPermissao(Permissao::DocumentacaoDelete->value);

    $doc = Documentacao::factory()->create();

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->call('marcarParaExcluir', $doc->id)
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
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', 'administracao.log.index')
    ->set('documentacao.doc_link', 'http://valid-url.com')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $documentacao = Documentacao::first();

    expect($documentacao->app_link)->toBe('administracao.log.index')
    ->and($documentacao->doc_link)->toBe('http://valid-url.com');
});

test('reseta para um modelo em branco após criar um registro', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    $branco = new Documentacao();

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->set('documentacao.app_link', 'administracao.log.index')
    ->set('documentacao.doc_link', 'http://valid-url.com')
    ->call('store')
    ->assertOk()
    ->assertSet('documentacao', $branco);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::DocumentacaoCreate->value);

    Livewire::test(DocumentacaoLivewireCreate::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'app_url',
            'doc_url',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('DocumentacaoLivewireCreate usa trait', function () {
    expect(
        collect(class_uses(DocumentacaoLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ComExclusao::class,
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComOrdenacao::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
