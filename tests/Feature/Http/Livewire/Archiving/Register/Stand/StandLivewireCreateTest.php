<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireCreate;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->room = Room::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a stand record without being authenticated', function () {
    logout();

    get(route('archiving.register.stand.create', $this->room->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access stand record creation route', function () {
    get(route('archiving.register.stand.create', $this->room->id))
    ->assertForbidden();
});

test('cannot render stand record creation component without specific permission', function () {
    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', '')
    ->call('store')
    ->assertHasErrors(['stand.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['stand.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 0)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'between'])
    ->set('stand.number', 100001)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'between']);
});

test('number and room_id must be unique', function () {
    grantPermission(PermissionType::StandCreate->value);

    Stand::factory()->create(['number' => 99, 'room_id' => $this->room->id]);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 99)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'unique']);
});

test('alias is optional', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.alias', '')
    ->call('store')
    ->assertHasNoErrors(['stand.alias']);
});

test('alias must be a string', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.alias', ['foo'])
    ->call('store')
    ->assertHasErrors(['stand.alias' => 'string']);
});

test('alias must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.alias', Str::random(101))
    ->call('store')
    ->assertHasErrors(['stand.alias' => 'max']);
});

test('alias and room_id must be unique', function () {
    grantPermission(PermissionType::StandCreate->value);

    Stand::factory()->create(['alias' => '99', 'room_id' => $this->room->id]);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.alias', '99')
    ->call('store')
    ->assertHasErrors(['stand.alias' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.description', '')
    ->call('store')
    ->assertHasNoErrors(['stand.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['stand.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['stand.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected stand records', function () {
    grantPermission(PermissionType::StandCreate->value);

    Stand::factory(30)->for($this->room, 'room')->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('stands', 25);
});

test('renders stand record creation component with specific permission', function () {
    grantPermission(PermissionType::StandCreate->value);

    get(route('archiving.register.stand.create', $this->room->id))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireCreate::class);
});

test('emits feedback event when creates a stand record', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 1)
    ->set('stand.alias', '1')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a stand record', function () {
    grantPermission(PermissionType::StandCreate->value);
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->call('setToDelete', $stand->id)
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

test('creates a stand record with specific permission', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 99)
    ->set('stand.alias', '99')
    ->set('stand.description', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $stand = Stand::with('room')->first();

    expect($stand->number)->toBe(99)
    ->and($stand->alias)->toBe('99')
    ->and($stand->description)->toBe('foo bar')
    ->and($stand->room->id)->toBe($this->room->id);
});

test('when creating a stand, a default shelf is also created', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 99)
    ->set('stand.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $stand = Stand::with('shelves')->first();
    $shelf = $stand->shelves()->first();

    expect($stand->number)->toBe(99)
    ->and($stand->description)->toBe('foo bar')
    ->and($stand->room_id)->toBe($this->room->id)
    ->and($shelf->number)->toBe(0)
    ->and($shelf->alias)->toBe(__('Uninformed'))
    ->and($shelf->stand_id)->toBe($stand->id)
    ->and($shelf->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
});

test('reset to a blank model after the stand is created', function () {
    grantPermission(PermissionType::StandCreate->value);

    $blank = new Stand();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 1)
    ->set('stand.alias', '1º')
    ->call('store')
    ->assertOk()
    ->assertSet('stand', $blank);
});

test('preferencias estão definidas', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'estante',
            'apelido',
            'qtd_prateleiras',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('StandLivewireCreate uses trait', function () {
    expect(
        collect(class_uses(StandLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ConverteStringVaziaEmNull::class,
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
