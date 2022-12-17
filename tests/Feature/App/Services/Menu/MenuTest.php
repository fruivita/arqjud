<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Permissao;
use App\Services\Menu\Menu;
use Database\Seeders\PerfilPermissaoSeeder;
use Database\Seeders\PerfilSeeder;
use Database\Seeders\PermissaoSeeder;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();
});

// Caminho feliz
test('make retorna o objeto da classe', function () {
    expect(Menu::make())->toBeInstanceOf(Menu::class);
});

test('menu é gerado de acordo com as permissões do usuário', function (string $permissao, string $grupo, string $icone, string $href, string $texto) {
    concederPermissao($permissao);

    $menu = Menu::make()->gerar();

    expect($menu)->toBe([[
        'nome' => $grupo,
        'links' => [
            ['icone' => $icone, 'href' => $href, 'texto' => $texto, 'ativo' => false],
        ],
    ]]);
})->with([
    [Permissao::SOLICITACAO_EXTERNA_CREATE, fn () => __('Solicitações de processos'), 'signpost', fn () => route('solicitacao.create'), fn () => __('Solicitar')],
    [Permissao::SOLICITACAO_EXTERNA_VIEW_ANY, fn () => __('Solicitações de processos'), 'signpost-2', fn () => route('solicitacao.index'), fn () => __('Solicitações')],
    [Permissao::LOCALIDADE_VIEW_ANY, fn () => __('Cadastros'), 'pin-map', fn () => route('cadastro.localidade.index'), fn () => __('Localidades')],
    [Permissao::PREDIO_VIEW_ANY, fn () => __('Cadastros'), 'buildings', fn () => route('cadastro.predio.index'), fn () => __('Prédios')],
    [Permissao::ANDAR_VIEW_ANY, fn () => __('Cadastros'), 'layers', fn () => route('cadastro.andar.index'), fn () => __('Andares')],
    [Permissao::SALA_VIEW_ANY, fn () => __('Cadastros'), 'door-closed', fn () => route('cadastro.sala.index'), fn () => __('Salas')],
    [Permissao::ESTANTE_VIEW_ANY, fn () => __('Cadastros'), 'bookshelf', fn () => route('cadastro.estante.index'), fn () => __('Estantes')],
    [Permissao::PRATELEIRA_VIEW_ANY, fn () => __('Cadastros'), 'list-nested', fn () => route('cadastro.prateleira.index'), fn () => __('Prateleiras')],
    [Permissao::CAIXA_VIEW_ANY, fn () => __('Cadastros'), 'box2', fn () => route('cadastro.caixa.index'), fn () => __('Caixas')],
    [Permissao::VOLUME_CAIXA_VIEW_ANY, fn () => __('Cadastros'), 'boxes', fn () => route('cadastro.volume-caixa.index'), fn () => __('Volumes das caixas')],
    [Permissao::PROCESSO_VIEW_ANY, fn () => __('Cadastros'), 'journal-bookmark', fn () => route('cadastro.processo.index'), fn () => __('Processos')],
    [Permissao::MOVER_PROCESSO_CREATE, fn () => __('Movimentações'), 'boxes', fn () => route('movimentacao.entre-caixas.create'), fn () => __('Entre caixas')],
    [Permissao::GUIA_VIEW_ANY, fn () => __('Atendimentos'), 'files', fn () => route('atendimento.guia.index'), fn () => __('Guias')],
]);

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

    expect($menu[0]['nome'])->toBe(__('Solicitações de processos'))
        ->and($menu[0]['links'])->toHaveCount(2)
        ->and($menu[1]['nome'])->toBe(__('Cadastros'))
        ->and($menu[1]['links'])->toHaveCount(9)
        ->and($menu[2]['nome'])->toBe(__('Movimentações'))
        ->and($menu[2]['links'])->toHaveCount(1)
        ->and($menu[3]['nome'])->toBe(__('Atendimentos'))
        ->and($menu[3]['links'])->toHaveCount(1);
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
    ['solicitacao.index', 'solicitacao.index'],
    ['solicitacao.create', 'solicitacao.create'],
    ['cadastro.predio.index', 'cadastro.predio.index'],
    ['cadastro.andar.index', 'cadastro.andar.index'],
    ['cadastro.sala.index', 'cadastro.sala.index'],
    ['cadastro.estante.index', 'cadastro.estante.index'],
    ['cadastro.prateleira.index', 'cadastro.prateleira.index'],
    ['cadastro.caixa.index', 'cadastro.caixa.index'],
    ['cadastro.volume-caixa.index', 'cadastro.volume-caixa.index'],
    ['cadastro.processo.index', 'cadastro.processo.index'],
    ['movimentacao.entre-caixas.create', 'movimentacao.entre-caixas.create'],
    ['atendimento.guia.index', 'atendimento.guia.index'],
]);
