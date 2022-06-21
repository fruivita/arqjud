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

    get(route('archiving.register.floor.show', $this->floor->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual floor view route', function () {
    get(route('archiving.register.floor.show', $this->floor->id))
    ->assertForbidden();
});

test('cannot render individual floor view component without specific permission', function () {
    Livewire::test(FloorLivewireShow::class, ['id' => $this->floor->id])
    ->assertForbidden();
});

// Happy path
test('renders individual floor view component with specific permission', function () {
    grantPermission(PermissionType::FloorView->value);

    get(route('archiving.register.floor.show', $this->floor->id))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireShow::class);
});

test('pagination returns the amount of rooms expected', function () {
    grantPermission(PermissionType::FloorView->value);

    Room::factory(30)->for($this->floor, 'floor')->create();

    Livewire::test(FloorLivewireShow::class, ['id' => $this->floor->id])
    ->set('per_page', 25)
    ->assertCount('rooms', 25);
});

test('individually view a floor with specific permission', function () {
    grantPermission(PermissionType::FloorView->value);

    get(route('archiving.register.floor.show', $this->floor->id))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireShow::class);
});

test('FloorLivewireShow uses trait', function () {
    expect(
        collect(class_uses(FloorLivewireShow::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
