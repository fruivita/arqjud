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

// Validação
test('não aceita paginação fora das opções oferecidas', function () {
    Livewire::test(BuildingLivewireIndex::class)
    ->set('preferencias.por_pagina', 33)
    ->call('salvarPreferencia')
    ->assertHasErrors(['preferencias.por_pagina' => 'in']);
});

test('não salva os valores em cache, caso a validação falhe', function () {
    expect(cache()->missing($this->chave))->toBeTrue();

    Livewire::test(BuildingLivewireIndex::class)
    ->set('preferencias.por_pagina', 33) // valores possíveis: 10/25/50/100
    ->call('salvarPreferencia')
    ->assertHasErrors(['preferencias.por_pagina' => 'in']);

    expect(cache()->missing($this->chave))->toBeTrue();
});

// Caminho feliz
test('armazena em cache as preferências que o usuário definiu', function () {
    $definidas_pelo_usuario = [
        'colunas' => ['predio', 'qtd_andares'],
        'por_pagina' => 50
    ];

    Livewire::test(BuildingLivewireIndex::class)
    ->set('preferencias', $definidas_pelo_usuario)
    ->call('salvarPreferencia')
    ->assertHasNoErrors()
    ->assertOk();

    expect(cache()->get($this->chave))->toBe($definidas_pelo_usuario);
});

test('ao carregar o componente, se houver não cache, as preferências padrão serão utilizadas', function () {
    $preferencias_padrao = [
        'colunas' => ['predio', 'qtd_andares', 'localidade', 'acoes'],
        'por_pagina' => 10
    ];

    Livewire::test(BuildingLivewireIndex::class)
    ->assertSet('preferencias', $preferencias_padrao)
    ->assertHasNoErrors()
    ->assertOk();
});

test('ao carregar o componente, se houver cache, ele será utilizado para definir as preferências', function () {
    testTime()->freeze();

    $preferencias_em_cache = [
        'colunas' => ['localidade', 'acoes'],
        'por_pagina' => 50
    ];
    cache()->put($this->chave, $preferencias_em_cache, now()->addYear());

    Livewire::test(BuildingLivewireIndex::class)
    ->assertSet('preferencias', $preferencias_em_cache)
    ->assertHasNoErrors()
    ->assertOk();

    expect(cache()->get($this->chave))->toBe($preferencias_em_cache);
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
