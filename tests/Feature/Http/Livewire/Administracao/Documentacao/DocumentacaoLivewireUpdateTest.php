<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Administracao\Documentacao\DocumentacaoLivewireUpdate;
use App\Models\Documentacao;
use App\Rules\RotaExiste;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->documentacao = Documentacao::factory()->create(['app_link' => 'administracao.log.index']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('administracao.documentacao.edit', $this->documentacao))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('administracao.documentacao.edit', $this->documentacao))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->assertForbidden();
});

test('não atualiza o registro sem habilitar o modo de edição', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('não atualiza o registro sem permissão', function () {
    concederPermissao(Permissao::DocumentacaoView->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('app link é obrigatório', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.app_link', '')
    ->call('update')
    ->assertHasErrors(['documentacao.app_link' => 'required']);
});

test('app link precisa ser uma string', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.app_link', ['bar'])
    ->call('update')
    ->assertHasErrors(['documentacao.app_link' => 'string']);
});

test('app link precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.app_link', Str::random(256))
    ->call('update')
    ->assertHasErrors(['documentacao.app_link' => 'max']);
});

test('app link precisa existir na aplicação', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.app_link', 'foo')
    ->call('update')
    ->assertHasErrors(['documentacao.app_link' => RotaExiste::class]);
});

test('app link precisa ser único', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    $doc = Documentacao::factory()->create(['app_link' => 'autorizacao.usuario.index']);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $doc])
    ->set('modo_edicao', true)
    ->set('documentacao.app_link', 'administracao.log.index')
    ->call('update')
    ->assertHasErrors(['documentacao.app_link' => 'unique']);
});

test('doc link é opcional', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.doc_link', '')
    ->call('update')
    ->assertHasNoErrors(['documentacao.doc_link']);
});

test('doc link precisa ser uma string', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.doc_link', ['foo'])
    ->call('update')
    ->assertHasErrors(['documentacao.doc_link' => 'string']);
});

test('doc link precisa ter no máximo 255 caracteres', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.doc_link', Str::random(256))
    ->call('update')
    ->assertHasErrors(['documentacao.doc_link' => 'max']);
});

test('doc link precisa ser uma url válida', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.doc_link', 'foo')
    ->call('update')
    ->assertHasErrors(['documentacao.doc_link' => 'url']);
});

// Caminho feliz
test('renderiza o componente com permissão', function ($permissao) {
    concederPermissao($permissao);

    get(route('administracao.documentacao.edit', $this->documentacao))
    ->assertOk()
    ->assertSeeLivewire(DocumentacaoLivewireUpdate::class);
})->with([
    Permissao::DocumentacaoView->value,
    Permissao::DocumentacaoUpdate->value,
]);

test('emite evento de feedback ao atualizar um registro', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('atualiza um registro com permissão', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->set('modo_edicao', true)
    ->set('documentacao.app_link', 'administracao.log.index')
    ->set('documentacao.doc_link', 'http://valid-url.com')
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->documentacao->refresh();

    expect($this->documentacao->app_link)->toBe('administracao.log.index')
    ->and($this->documentacao->doc_link)->toBe('http://valid-url.com');
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::DocumentacaoUpdate->value);

    Livewire::test(DocumentacaoLivewireUpdate::class, ['documentacao' => $this->documentacao])
    ->assertSet('modo_edicao', false);
});

test('DocumentacaoLivewireUpdate usa trait', function () {
    expect(
        collect(class_uses(DocumentacaoLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ComFeedback::class,
        ])
    )->toBeTrue();
});
