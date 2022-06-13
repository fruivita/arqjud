<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireIndex;
use App\Models\Shelf;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->stand = Stand::factory()->create();
    $this->stand->load('room.floor.building.site');

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list stand records without being authenticated', function () {
    logout();

    get(route('archiving.register.stand.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access stand records listing route', function () {
    get(route('archiving.register.stand.index'))
    ->assertForbidden();
});

test('cannot render listing component from stand records without specific permission', function () {
    Livewire::test(StandLivewireIndex::class)->assertForbidden();
});

test('cannot set the stand record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::StandViewAny->value);

    Livewire::test(StandLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $this->stand->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the stand record which will be deleted if it has shelves', function () {
    grantPermission(PermissionType::StandViewAny->value);
    grantPermission(PermissionType::StandDelete->value);

    Shelf::factory()
    ->for($this->stand, 'stand')
    ->create();

    Livewire::test(StandLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $this->stand->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a stand record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::StandViewAny->value);
    grantPermission(PermissionType::StandDelete->value);

    $component = Livewire::test(StandLivewireIndex::class)
    ->call('markToDelete', $this->stand->id)
    ->assertOk();

    revokePermission(PermissionType::StandDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Stand::where('id', $this->stand->id)->exists())->toBeTrue();
});

test('cannot delete a stand record if it has shelves', function () {
    grantPermission(PermissionType::StandViewAny->value);
    grantPermission(PermissionType::StandDelete->value);

    $component = Livewire::test(StandLivewireIndex::class)
    ->call('markToDelete', $this->stand->id)
    ->assertOk();

    Shelf::factory()
    ->for($this->stand, 'stand')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Stand::where('id', $this->stand->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::StandViewAny->value);

    Livewire::test(StandLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the amount of expected stand records', function () {
    grantPermission(PermissionType::StandViewAny->value);

    Stand::factory(120)->create();

    Livewire::test(StandLivewireIndex::class)
    ->assertCount('stands', 10)
    ->set('per_page', 10)
    ->assertCount('stands', 10)
    ->set('per_page', 25)
    ->assertCount('stands', 25)
    ->set('per_page', 50)
    ->assertCount('stands', 50)
    ->set('per_page', 100)
    ->assertCount('stands', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::StandViewAny->value);

    Livewire::test(StandLivewireIndex::class)
    ->assertSessionMissing('per_page')
    ->set('per_page', 10)
    ->assertSessionHas('per_page', 10)
    ->set('per_page', 25)
    ->assertSessionHas('per_page', 25)
    ->set('per_page', 50)
    ->assertSessionHas('per_page', 50)
    ->set('per_page', 100)
    ->assertSessionHas('per_page', 100);
});

test('lists stand records with specific permission', function () {
    grantPermission(PermissionType::StandViewAny->value);

    get(route('archiving.register.stand.index'))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireIndex::class);
});

test('emits feedback event when deleting a stand record', function () {
    grantPermission(PermissionType::StandViewAny->value);
    grantPermission(PermissionType::StandDelete->value);

    Livewire::test(StandLivewireIndex::class)
    ->call('markToDelete', $this->stand->id)
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

test('defines the stand record that will be deleted with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::StandViewAny->value);
    grantPermission(PermissionType::StandDelete->value);

    Livewire::test(StandLivewireIndex::class)
    ->call('markToDelete', $this->stand->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->stand->id);
});

test('delete a stand record with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::StandViewAny->value);
    grantPermission(PermissionType::StandDelete->value);

    expect(Stand::where('id', $this->stand->id)->exists())->toBeTrue();

    Livewire::test(StandLivewireIndex::class)
    ->call('markToDelete', $this->stand->id)
    ->assertOk()
    ->call('destroy', $this->stand->id)
    ->assertOk();

    expect(Stand::where('id', $this->stand->id)->doesntExist())->toBeTrue();
});
