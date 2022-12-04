<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permissao;
use App\Models\Perfil;
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
    [Permissao::LOCALIDADE_VIEW_ANY, fn () => __('Cadastro'), 'pin-map', fn () => route('cadastro.localidade.index'), fn () => __('Localidades')],
    [Permissao::PREDIO_VIEW_ANY, fn () => __('Cadastro'), 'buildings', fn () => route('cadastro.predio.index'), fn () => __('Prédios')],
    [Permissao::ANDAR_VIEW_ANY, fn () => __('Cadastro'), 'layers', fn () => route('cadastro.andar.index'), fn () => __('Andares')],
    [Permissao::SALA_VIEW_ANY, fn () => __('Cadastro'), 'door-closed', fn () => route('cadastro.sala.index'), fn () => __('Salas')],
    [Permissao::ESTANTE_VIEW_ANY, fn () => __('Cadastro'), 'bookshelf', fn () => route('cadastro.estante.index'), fn () => __('Estantes')],
    [Permissao::PRATELEIRA_VIEW_ANY, fn () => __('Cadastro'), 'list-nested', fn () => route('cadastro.prateleira.index'), fn () => __('Prateleiras')],
    [Permissao::CAIXA_VIEW_ANY, fn () => __('Cadastro'), 'box2', fn () => route('cadastro.caixa.index'), fn () => __('Caixas')],
    [Permissao::VOLUME_CAIXA_VIEW_ANY, fn () => __('Cadastro'), 'boxes', fn () => route('cadastro.volumeCaixa.index'), fn () => __('Volumes das caixas')],
    [Permissao::PROCESSO_VIEW_ANY, fn () => __('Cadastro'), 'journal-bookmark', fn () => route('cadastro.processo.index'), fn () => __('Processos')],
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

    expect($menu[0]['nome'])->toBe(__('Cadastro'))
        ->and($menu[0]['links'])->toHaveCount(9);
});

test('identifica o menu ativo corretamente', function ($rota, $menu_ativo) {
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
    ['cadastro.localidade.index', 'cadastro.localidade.index'],
    ['cadastro.predio.index', 'cadastro.predio.index'],
    ['cadastro.andar.index', 'cadastro.andar.index'],
    ['cadastro.sala.index', 'cadastro.sala.index'],
    ['cadastro.estante.index', 'cadastro.estante.index'],
    ['cadastro.prateleira.index', 'cadastro.prateleira.index'],
    ['cadastro.caixa.index', 'cadastro.caixa.index'],
    ['cadastro.volumeCaixa.index', 'cadastro.volumeCaixa.index'],
    ['cadastro.processo.index', 'cadastro.processo.index'],
]);
