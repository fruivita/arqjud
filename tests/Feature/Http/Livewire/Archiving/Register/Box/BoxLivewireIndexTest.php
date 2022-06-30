<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireIndex;
use App\Models\Box;
use App\Models\BoxVolume;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list boxes without being authenticated', function () {
    logout();

    get(route('archiving.register.box.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access boxes listing route', function () {
    get(route('archiving.register.box.index'))
    ->assertForbidden();
});

test('cannot render boxes listing component without specific permission', function () {
    Livewire::test(BoxLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of boxes expected', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Box::factory(30)->create();

    Livewire::test(BoxLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('boxes', 25);
});

test('list boxes with specific permission', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    get(route('archiving.register.box.index'))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Box::factory()->create(['number' => '100', 'year' => '2015']);
    Box::factory()->create(['number' => '120152', 'year' => '2020']);
    Box::factory()->create(['number' => '200', 'year' => '2020']);

    Livewire::test(BoxLivewireIndex::class)
    ->set('term', '120152')
    ->assertCount('boxes', 1)
    ->set('term', '2015')
    ->assertCount('boxes', 2)
    ->set('term', '2020')
    ->assertCount('boxes', 2)
    ->set('term', '')
    ->assertCount('boxes', 3);
});

test('emits feedback event when deleting a box record', function () {
    grantPermission(PermissionType::BoxViewAny->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->create();

    Livewire::test(BoxLivewireIndex::class)
    ->call('setToDelete', $box->id)
    ->call('destroy')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => null,
        'timeout' => 3000,
    ]);
});

test('preferencias estão definidas', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Livewire::test(BoxLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'caixa',
            'ano',
            'qtd_volumes',
            'localidade',
            'predio',
            'andar',
            'sala',
            'estante',
            'prateleira',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('BoxLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(BoxLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
