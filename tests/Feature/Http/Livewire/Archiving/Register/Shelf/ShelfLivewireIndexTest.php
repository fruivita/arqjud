<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireIndex;
use App\Models\Box;
use App\Models\Shelf;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->shelf = Shelf::factory()->create();
    $this->shelf->load('stand.room.floor.building.site');

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list shelf records without being authenticated', function () {
    logout();

    get(route('archiving.register.shelf.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access shelf records listing route', function () {
    get(route('archiving.register.shelf.index'))
    ->assertForbidden();
});

test('cannot render listing component from shelf records without specific permission', function () {
    Livewire::test(ShelfLivewireIndex::class)->assertForbidden();
});

test('cannot set the shelf record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    Livewire::test(ShelfLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $this->shelf->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the shelf record which will be deleted if it has boxes', function () {
    grantPermission(PermissionType::ShelfViewAny->value);
    grantPermission(PermissionType::ShelfDelete->value);

    Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    Livewire::test(ShelfLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $this->shelf->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a shelf record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::ShelfViewAny->value);
    grantPermission(PermissionType::ShelfDelete->value);

    $component = Livewire::test(ShelfLivewireIndex::class)
    ->call('markToDelete', $this->shelf->id)
    ->assertOk();

    revokePermission(PermissionType::ShelfDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Shelf::where('id', $this->shelf->id)->exists())->toBeTrue();
});

test('cannot delete a shelf record if it has boxes', function () {
    grantPermission(PermissionType::ShelfViewAny->value);
    grantPermission(PermissionType::ShelfDelete->value);

    $component = Livewire::test(ShelfLivewireIndex::class)
    ->call('markToDelete', $this->shelf->id)
    ->assertOk();

    Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Shelf::where('id', $this->shelf->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    Livewire::test(ShelfLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the amount of expected shelf records', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    Shelf::factory(120)->create();

    Livewire::test(ShelfLivewireIndex::class)
    ->assertCount('shelves', 10)
    ->set('per_page', 10)
    ->assertCount('shelves', 10)
    ->set('per_page', 25)
    ->assertCount('shelves', 25)
    ->set('per_page', 50)
    ->assertCount('shelves', 50)
    ->set('per_page', 100)
    ->assertCount('shelves', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    Livewire::test(ShelfLivewireIndex::class)
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

test('lists shelf records with specific permission', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    get(route('archiving.register.shelf.index'))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireIndex::class);
});

test('emits feedback event when deleting a shelf record', function () {
    grantPermission(PermissionType::ShelfViewAny->value);
    grantPermission(PermissionType::ShelfDelete->value);

    Livewire::test(ShelfLivewireIndex::class)
    ->call('markToDelete', $this->shelf->id)
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

test('defines the shelf record that will be deleted with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::ShelfViewAny->value);
    grantPermission(PermissionType::ShelfDelete->value);

    Livewire::test(ShelfLivewireIndex::class)
    ->call('markToDelete', $this->shelf->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->shelf->id);
});

test('delete a shelf record with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::ShelfViewAny->value);
    grantPermission(PermissionType::ShelfDelete->value);

    expect(Shelf::where('id', $this->shelf->id)->exists())->toBeTrue();

    Livewire::test(ShelfLivewireIndex::class)
    ->call('markToDelete', $this->shelf->id)
    ->assertOk()
    ->call('destroy', $this->shelf->id)
    ->assertOk();

    expect(Shelf::where('id', $this->shelf->id)->doesntExist())->toBeTrue();
});
