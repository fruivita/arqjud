<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Services\Menu\Menu;
use Database\Seeders\PerfilPermissaoSeeder;
use Database\Seeders\PerfilSeeder;
use Database\Seeders\PermissaoSeeder;
use Illuminate\Support\Facades\Auth;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();
    Auth::login($this->usuario);
});

afterEach(function () {
    Auth::logout();
});

// Caminho feliz
test('make retorna o objeto da classe', function () {
    expect(Menu::make())->toBeInstanceOf(Menu::class);
});

test('menu é gerado de acordo com as permissões do usuário', function (string $permissao, string $grupo, string $icone, string $href, string $texto) {
    concederPermissao($permissao);

    $menu = Menu::make()->gerar();

    expect($menu)->toMatchArray([[
        'nome' => $grupo,
        'links' => [
            ['icone' => $icone, 'href' => $href, 'texto' => $texto, 'ativo' => false],
        ],
    ]]);
})->with([
    [Permissao::SOLICITACAO_VIEW_ANY, fn () => __('Atendimentos'), 'signpost-2', fn () => route('atendimento.solicitar-processo.index'), fn () => __('Solicitações')],
    [Permissao::SOLICITACAO_CREATE, fn () => __('Atendimentos'), 'signpost', fn () => route('atendimento.solicitar-processo.create'), fn () => __('Solicitar processo')],
    [Permissao::GUIA_VIEW_ANY, fn () => __('Atendimentos'), 'files', fn () => route('atendimento.guia.index'), fn () => __('Guias')],
    [Permissao::SOLICITACAO_EXTERNA_CREATE, fn () => __('Solicitações de processos'), 'signpost', fn () => route('solicitacao.create'), fn () => __('Solicitar')],
    [Permissao::SOLICITACAO_EXTERNA_VIEW_ANY, fn () => __('Solicitações de processos'), 'signpost-2', fn () => route('solicitacao.index'), fn () => __('Solicitações')],
    [Permissao::MOVER_PROCESSO_CREATE, fn () => __('Movimentações'), 'boxes', fn () => route('movimentacao.entre-caixas.create'), fn () => __('Entre caixas')],
    [Permissao::LOCALIDADE_VIEW_ANY, fn () => __('Cadastros'), 'pin-map', fn () => route('cadastro.localidade.index'), fn () => __('Localidades')],
    [Permissao::PREDIO_VIEW_ANY, fn () => __('Cadastros'), 'buildings', fn () => route('cadastro.predio.index'), fn () => __('Prédios')],
    [Permissao::ANDAR_VIEW_ANY, fn () => __('Cadastros'), 'layers', fn () => route('cadastro.andar.index'), fn () => __('Andares')],
    [Permissao::SALA_VIEW_ANY, fn () => __('Cadastros'), 'door-closed', fn () => route('cadastro.sala.index'), fn () => __('Salas')],
    [Permissao::ESTANTE_VIEW_ANY, fn () => __('Cadastros'), 'bookshelf', fn () => route('cadastro.estante.index'), fn () => __('Estantes')],
    [Permissao::PRATELEIRA_VIEW_ANY, fn () => __('Cadastros'), 'list-nested', fn () => route('cadastro.prateleira.index'), fn () => __('Prateleiras')],
    [Permissao::CAIXA_VIEW_ANY, fn () => __('Cadastros'), 'box2', fn () => route('cadastro.caixa.index'), fn () => __('Caixas')],
    [Permissao::PROCESSO_VIEW_ANY, fn () => __('Cadastros'), 'journal-bookmark', fn () => route('cadastro.processo.index'), fn () => __('Processos')],
    [Permissao::USUARIO_VIEW_ANY, fn () => __('Autorizações'), 'people', fn () => route('autorizacao.usuario.index'), fn () => __('Usuários')],
    [Permissao::PERFIL_VIEW_ANY, fn () => __('Administração'), 'award', fn () => route('administracao.perfil.index'), fn () => __('Perfis')],
    [Permissao::PERMISSAO_VIEW_ANY, fn () => __('Administração'), 'vector-pen', fn () => route('administracao.permissao.index'), fn () => __('Permissões')],
    [Permissao::LOTACAO_VIEW_ANY, fn () => __('Administração'), 'buildings', fn () => route('administracao.lotacao.index'), fn () => __('Lotações')],
    [Permissao::IMPORTACAO_CREATE, fn () => __('Administração'), 'usb-drive', fn () => route('administracao.importacao.create'), fn () => __('Importar dados')],
    [Permissao::LOG_VIEW_ANY, fn () => __('Administração'), 'file-earmark-text', fn () => route('administracao.log.index'), fn () => __('Logs de funcionamento')],
]);

test('menu é gerado de acordo com as permissões do usuário, inclusive se a permissão habilitar mais de 2 itens do menu', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    $menu = Menu::make()->gerar();

    expect($menu)->toMatchArray([[
        'nome' => __('Atendimentos'),
        'links' => [
            ['icone' => 'cart', 'href' => route('atendimento.entregar-processo.create'), 'texto' => __('Entregar processos'), 'ativo' => false],
            ['icone' => 'safe', 'href' => route('atendimento.receber-processo.create'), 'texto' => __('Receber processos'), 'ativo' => false],
        ],
    ]]);
});

test('sem permissão, o menu não possui nenhum item', function () {
    $menu = Menu::make()->gerar();

    expect($menu)->toBeEmpty();
});

test('administrador tem acesso a todos os itens do menu', function () {
    $this->seed([PermissaoSeeder::class, PerfilPermissaoSeeder::class]);
    $this->usuario
        ->perfil()->associate(Perfil::firstWhere('slug', Perfil::ADMINISTRADOR))
        ->save();

    $menu = Menu::make()->gerar();

    expect($menu[0]['nome'])->toBe(__('Atendimentos'))
        ->and($menu[0]['links'])->toHaveCount(5)
        ->and($menu[1]['nome'])->toBe(__('Solicitações de processos'))
        ->and($menu[1]['links'])->toHaveCount(2)
        ->and($menu[2]['nome'])->toBe(__('Movimentações'))
        ->and($menu[2]['links'])->toHaveCount(1)
        ->and($menu[3]['nome'])->toBe(__('Cadastros'))
        ->and($menu[3]['links'])->toHaveCount(8)
        ->and($menu[4]['nome'])->toBe(__('Autorizações'))
        ->and($menu[4]['links'])->toHaveCount(1)
        ->and($menu[5]['nome'])->toBe(__('Administração'))
        ->and($menu[5]['links'])->toHaveCount(5);
});

test('identifica o menu ativo corretamente', function (string $rota, string $menu_ativo) {
    $this->seed([PermissaoSeeder::class, PerfilPermissaoSeeder::class]);
    $this->usuario
        ->perfil()->associate(Perfil::firstWhere('slug', Perfil::ADMINISTRADOR))
        ->save();

    get(route($rota));

    $menu = collect(Menu::make()->gerar())
        ->pluck('links')
        ->flatten(1)
        ->where('ativo', true);

    expect($menu)->toHaveCount(1)
        ->and($menu->first()['href'])->toBe(route($menu_ativo));
})->with([
    ['atendimento.solicitar-processo.index', 'atendimento.solicitar-processo.index'],
    ['atendimento.solicitar-processo.create', 'atendimento.solicitar-processo.create'],
    ['atendimento.entregar-processo.create', 'atendimento.entregar-processo.create'],
    ['atendimento.receber-processo.create', 'atendimento.receber-processo.create'],
    ['atendimento.guia.index', 'atendimento.guia.index'],
    ['solicitacao.index', 'solicitacao.index'],
    ['solicitacao.create', 'solicitacao.create'],
    ['movimentacao.entre-caixas.create', 'movimentacao.entre-caixas.create'],
    ['cadastro.predio.index', 'cadastro.predio.index'],
    ['cadastro.andar.index', 'cadastro.andar.index'],
    ['cadastro.sala.index', 'cadastro.sala.index'],
    ['cadastro.estante.index', 'cadastro.estante.index'],
    ['cadastro.prateleira.index', 'cadastro.prateleira.index'],
    ['cadastro.caixa.index', 'cadastro.caixa.index'],
    ['cadastro.processo.index', 'cadastro.processo.index'],
    ['autorizacao.usuario.index', 'autorizacao.usuario.index'],
    ['administracao.perfil.index', 'administracao.perfil.index'],
    ['administracao.permissao.index', 'administracao.permissao.index'],
    ['administracao.lotacao.index', 'administracao.lotacao.index'],
    ['administracao.importacao.create', 'administracao.importacao.create'],
    ['administracao.log.index', 'administracao.log.index'],
]);
