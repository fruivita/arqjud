<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireIndex;
use App\Models\Floor;
use App\Models\Room;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->floor = Floor::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list floor records without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor records listing route', function () {
    get(route('archiving.register.floor.index'))
    ->assertForbidden();
});

test('cannot render listing component from floor records without specific permission', function () {
    Livewire::test(FloorLivewireIndex::class)->assertForbidden();
});

test('cannot set the floor record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    Livewire::test(FloorLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->floor->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the floor record which will be deleted if it has rooms', function () {
    grantPermission(PermissionType::FloorViewAny->value);
    grantPermission(PermissionType::FloorDelete->value);

    Room::factory()->for($this->floor, 'floor')->create();

    Livewire::test(FloorLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->floor->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a floor record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::FloorViewAny->value);
    grantPermission(PermissionType::FloorDelete->value);

    $component = Livewire::test(FloorLivewireIndex::class)
    ->call('setToDelete', $this->floor->id)
    ->assertOk();

    revokePermission(PermissionType::FloorDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Floor::where('id', $this->floor->id)->exists())->toBeTrue();
});

test('cannot delete a floor record if it has rooms', function () {
    grantPermission(PermissionType::FloorViewAny->value);
    grantPermission(PermissionType::FloorDelete->value);

    $component = Livewire::test(FloorLivewireIndex::class)
    ->call('setToDelete', $this->floor->id)
    ->assertOk();

    Room::factory()->for($this->floor, 'floor')->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Floor::where('id', $this->floor->id)->exists())->toBeTrue();
});

// Happy path
test('pagination returns the amount of expected floor records', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    Floor::factory(30)->create();

    Livewire::test(FloorLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('floors', 25);
});

test('lists floor records with specific permission', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    get(route('archiving.register.floor.index'))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    $this->floor->delete();

    Floor::factory()->create(['number' => 10]);
    Floor::factory()->create(['number' => 210]); // contains 10
    Floor::factory()->create(['number' => 20]);

    Livewire::test(FloorLivewireIndex::class)
    ->set('term', '210')
    ->assertCount('floors', 1)
    ->set('term', '10')
    ->assertCount('floors', 2)
    ->set('term', '')
    ->assertCount('floors', 3);
});

test('emits feedback event when deleting a floor record', function () {
    grantPermission(PermissionType::FloorViewAny->value);
    grantPermission(PermissionType::FloorDelete->value);

    Livewire::test(FloorLivewireIndex::class)
    ->call('setToDelete', $this->floor->id)
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

test('defines the floor record that will be deleted with specific permission if it has no rooms', function () {
    grantPermission(PermissionType::FloorViewAny->value);
    grantPermission(PermissionType::FloorDelete->value);

    Livewire::test(FloorLivewireIndex::class)
    ->call('setToDelete', $this->floor->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->floor->id);
});

test('delete a floor record with specific permission if it has no rooms', function () {
    grantPermission(PermissionType::FloorViewAny->value);
    grantPermission(PermissionType::FloorDelete->value);

    expect(Floor::where('id', $this->floor->id)->exists())->toBeTrue();

    Livewire::test(FloorLivewireIndex::class)
    ->call('setToDelete', $this->floor->id)
    ->assertOk()
    ->call('destroy', $this->floor->id)
    ->assertOk();

    expect(Floor::where('id', $this->floor->id)->doesntExist())->toBeTrue();
});

test('FloorLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(FloorLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
