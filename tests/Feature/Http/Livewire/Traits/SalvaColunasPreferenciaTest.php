<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireIndex;
use App\Models\Building;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->building = Building::factory()->create();

    login('foo');

    grantPermission(PermissionType::BuildingViewAny->value);

    $this->chave = authenticatedUser()->username . "BuildingLivewireIndex";
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('armazena em cache todas as colunas disponíveis para exibição', function () {
    $colunas = ['predio', 'qtd_andares', 'localidade', 'acoes'];

    Livewire::test(BuildingLivewireIndex::class)
    ->call('salvarPreferencia')
    ->assertHasNoErrors()
    ->assertOk();

    expect(cache()->get($this->chave))->toBe($colunas);
});

test('armazena em cache as colunas que o usuário definiu para serem exibidas', function () {
    $definidas_pelo_usuario = ['predio', 'qtd_andares', 'acoes'];

    Livewire::test(BuildingLivewireIndex::class)
    ->set('colunas', $definidas_pelo_usuario)
    ->call('salvarPreferencia')
    ->assertHasNoErrors()
    ->assertOk();

    expect(cache()->get($this->chave))->toBe($definidas_pelo_usuario);
});

test('ao carregar o componente, se houver não cache, as colunas padrão estarão disponíveis para visualização', function () {
    $colunas_padrao = ['predio', 'qtd_andares', 'localidade', 'acoes'];

    Livewire::test(BuildingLivewireIndex::class)
    ->assertSet('colunas', $colunas_padrao)
    ->assertHasNoErrors()
    ->assertOk();
});

test('ao carregar o componente, se houver cache, ele será utilizado para definir as colunas visíveis', function () {
    testTime()->freeze();

    $colunas_em_cache = ['predio', 'qtd_andares'];
    cache()->put($this->chave, $colunas_em_cache, now()->addYear());

    Livewire::test(BuildingLivewireIndex::class)
    ->assertSet('colunas', $colunas_em_cache)
    ->assertHasNoErrors()
    ->assertOk();

    expect(cache()->get($this->chave))->toBe($colunas_em_cache);
});

test('o cache é armazenado por um ano', function () {
    $componente = Livewire::test(BuildingLivewireIndex::class);

    expect(cache()->missing($this->chave))->toBeTrue();

    testTime()->freeze();
    $componente->call('salvarPreferencia');
    testTime()->addYears(1);

    // cache ainda exite após um ano
    expect(cache()->has($this->chave))->toBeTrue();

    // expira cache
    testTime()->addSeconds(1);
    expect(cache()->missing($this->chave))->toBeTrue();
});
