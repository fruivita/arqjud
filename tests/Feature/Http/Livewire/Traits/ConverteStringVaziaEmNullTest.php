<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireUpdate;
use App\Models\Floor;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->floor = Floor::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Happy path
test('converte para null strings vazias, inclusive com diversos espaços em branco, se for aplicável', function ($string, $esperado) {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.alias', $string)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->floor->refresh();

    expect($this->floor->alias)->toBe($esperado);
})->with([
    [''     , null],  // vazio
    ['     ', null],  // vazio pois haverá trim
    ['20º'  , '20º'], // não conversível, pois um valor válido
]);
