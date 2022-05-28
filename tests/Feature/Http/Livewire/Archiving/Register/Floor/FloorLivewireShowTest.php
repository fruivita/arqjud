<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireShow;
use App\Models\Floor;
use App\Models\Room;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('cannot individually view a floor without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.show', $this->floor))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual floor view route', function () {
    get(route('archiving.register.floor.show', $this->floor))
    ->assertForbidden();
});

test('cannot render individual floor view component without specific permission', function () {
    Livewire::test(FloorLivewireShow::class, ['floor' => $this->floor])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::FloorView->value);

    Livewire::test(FloorLivewireShow::class, ['floor' => $this->floor])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual floor view component with specific permission', function () {
    grantPermission(PermissionType::FloorView->value);

    get(route('archiving.register.floor.show', $this->floor))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireShow::class);
});

test('pagination returns the amount of rooms expected', function () {
    grantPermission(PermissionType::FloorView->value);

    Room::factory(120)
    ->for($this->floor, 'floor')
    ->create();

    Livewire::test(FloorLivewireShow::class, ['floor' => $this->floor])
    ->assertCount('rooms', 10)
    ->set('per_page', 10)
    ->assertCount('rooms', 10)
    ->set('per_page', 25)
    ->assertCount('rooms', 25)
    ->set('per_page', 50)
    ->assertCount('rooms', 50)
    ->set('per_page', 100)
    ->assertCount('rooms', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::FloorView->value);

    Livewire::test(FloorLivewireShow::class, ['floor' => $this->floor])
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

test('individually view a floor with specific permission', function () {
    grantPermission(PermissionType::FloorView->value);

    get(route('archiving.register.floor.show', $this->floor))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireShow::class);
});

test('next and previous are available when viewing individual floors, even when dealing with the first or last record', function () {
    $this->floor->delete();
    grantPermission(PermissionType::FloorView->value);

    $first = Floor::factory()->create(['number' => 10]);
    $second = Floor::factory()->create(['number' => 20]);
    $last = Floor::factory()->create(['number' => 30]);

    // has previous and next
    Livewire::test(FloorLivewireShow::class, ['floor' => $second])
    ->assertSet('previous', $first->id)
    ->assertSet('next', $last->id);

    // only has next
    Livewire::test(FloorLivewireShow::class, ['floor' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', $second->id);

    // has only previous
    Livewire::test(FloorLivewireShow::class, ['floor' => $last])
    ->assertSet('previous', $second->id)
    ->assertSet('next', null);
});
