<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;
use App\Enums\Permissao;
use App\Http\Livewire\Administracao\Log\LogLivewireIndex;
use App\Rules\ArquivoExiste;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use function Pest\Faker\faker;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    // cria os arquivos de log fake
    $this->arquivos_log = ['laravel-2020-10-30.log', 'laravel.log'];

    $this->disco_fake = Storage::fake('log-aplicacao');

    $conteudo_1 = collect();
    $conteudo_2 = collect();

    foreach (range(1, 110) as $counter) {
        $conteudo_1->push(faker()->sentence());

        if ($counter % 2 === 1) {
            $conteudo_2->push(faker()->sentence());
        }
    }

    $this->disco_fake->put($this->arquivos_log[0], $conteudo_1->join(PHP_EOL));
    $this->disco_fake->put($this->arquivos_log[1], $conteudo_2->join(PHP_EOL));

    login('foo');
});

afterEach(function () {
    $this->disco_fake = Storage::fake('log-aplicacao');

    logout();
});

// Autorização
test('não carrega página sem estar autenticado', function () {
    logout();

    get(route('administracao.log.index'))
    ->assertRedirect(route('login'));
});

test('autenticado mas sem permissão, a rota está indisponível', function () {
    get(route('administracao.log.index'))
    ->assertForbidden();
});

test('não renderiza o componente sem permissão', function () {
    Livewire::test(LogLivewireIndex::class)->assertForbidden();
});

test('não exclui o arquivo sem permissão', function () {
    concederPermissao(Permissao::LogViewAny->value);

    $this->disco_fake->assertExists($this->arquivos_log);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', $this->arquivos_log[0])
    ->call('destroy')
    ->assertForbidden();
});

test('não faz download sem permissão', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', $this->arquivos_log[0])
    ->call('download')
    ->assertForbidden();
});

// Falhas
test('se os valores de inciialização forem inválidos, eles serão definidos pela aplicação', function () {
    concederPermissao(Permissao::LogViewAny->value);

    // força o teste esperar por 1 segundo para alterar a data de criação do
    // arquivo no servidor de arquivos. Isso porque as funções travel e
    // testtime não alteram os valores no servidor, apenas no php.
    sleep(1);
    // altera o arquivo para ele ser o mais recente.
    $this->disco_fake->append($this->arquivos_log[1], 'foo');

    Livewire::test(LogLivewireIndex::class, [
        'arquivo_log' => 'foo.log',
    ])
    ->assertSet('arquivo_log', $this->arquivos_log[1]);
});

// Rules
test('arquivo de log é obrigatório', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', '') // possible values: 10/25/50/100
    ->assertHasErrors(['arquivo_log' => 'required']);
});

test('arquivo de log precisa ser uma string', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', ['bar'])
    ->assertHasErrors(['arquivo_log' => 'string']);
});

test('arquivo de log precisa respeitar o padrão dos arquivos de log do Laravel', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', 'foo.log')
    ->assertHasErrors(['arquivo_log' => 'regex']);
});

test('arquivo de log precisa existir no storage', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', 'laravel-1900-01-30.log')
    ->assertHasErrors(['arquivo_log' => ArquivoExiste::class]);
});

// Caminho feliz
test('paginação retorna o número de linhas esperada do arquivo de log', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('conteudo_arquivo', 25);
});

test('retorna a quantidade de arquivos de log esperada', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->assertCount('arquivos_log', 2);
});

test('arquivo de log é inicializado com o arquivo mais recentemente modificado', function () {
    concederPermissao(Permissao::LogViewAny->value);

    // força o teste esperar por 1 segundo para alterar a data de criação do
    // arquivo no servidor de arquivos. Isso porque as funções travel e
    // testtime não alteram os valores no servidor, apenas no php.
    sleep(1);
    // altera o arquivo para ele ser o mais recente.
    $this->disco_fake->append($this->arquivos_log[1], 'foo');

    Livewire::test(LogLivewireIndex::class, [
        'arquivo_log' => $this->arquivos_log[1],
    ])
    ->assertSet('arquivo_log', $this->arquivos_log[1]);
});

test('se os valores de inicialização forem válidos, eles serão usados', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class, [
        'arquivo_log' => $this->arquivos_log[0],
    ])
    ->assertSet('arquivo_log', $this->arquivos_log[0]);

    Livewire::test(LogLivewireIndex::class, [
        'arquivo_log' => $this->arquivos_log[1],
    ])
    ->assertSet('arquivo_log', $this->arquivos_log[1]);
});

test('emite evento de feedback ao excluir um arquivo', function () {
    concederPermissao(Permissao::LogViewAny->value);
    concederPermissao(Permissao::LogDelete->value);

    Livewire::test(LogLivewireIndex::class, [
        'arquivo_log' => $this->arquivos_log[1],
    ])
    ->call('destroy')
    ->assertEmitted('flash', Feedback::Sucesso, __('Sucesso!'));
});

test('mesmo sem arquivo para ser exibido, o componente é carregado sem erros', function () {
    concederPermissao(Permissao::LogViewAny->value);

    $this->disco_fake->delete($this->arquivos_log);

    $this->disco_fake->assertDirectoryEmpty('');

    Livewire::test(LogLivewireIndex::class)
    ->assertSet('arquivo_log', null)
    ->assertOk();
});

test('lista os arquivos de log com permissão', function () {
    concederPermissao(Permissao::LogViewAny->value);

    get(route('administracao.log.index'))
    ->assertOk()
    ->assertSeeLivewire(LogLivewireIndex::class);
});

test('exclui o arquivo de log com permissão', function () {
    concederPermissao(Permissao::LogViewAny->value);
    concederPermissao(Permissao::LogDelete->value);

    $this->disco_fake->assertExists($this->arquivos_log);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', $this->arquivos_log[0])
    ->call('destroy')
    ->assertHasNoErrors()
    ->assertOk();

    $this->disco_fake->assertMissing($this->arquivos_log[0]);
    $this->disco_fake->assertExists($this->arquivos_log[1]);
});

test('faz o download do arquivo de log com permissão', function () {
    concederPermissao(Permissao::LogViewAny->value);
    concederPermissao(Permissao::LogDownload->value);

    Livewire::test(LogLivewireIndex::class)
    ->set('arquivo_log', $this->arquivos_log[0])
    ->call('download')
    ->assertFileDownloaded($this->arquivos_log[0]);
});

test('valores iniciais do componente estão definidos', function () {
    concederPermissao(Permissao::LogViewAny->value);

    Livewire::test(LogLivewireIndex::class)
    ->assertSet('preferencias', [
        'por_pagina' => 10,
    ]);
});

test('LogLivewireIndex usa trait', function () {
    expect(
        collect(class_uses(LogLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\ComFeedback::class,
            \App\Http\Livewire\Traits\ComPaginacao::class,
            \App\Http\Livewire\Traits\ComPreferencias::class,
        ])
    )->toBeTrue();
});
