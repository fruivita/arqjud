<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireShow;
use App\Models\Room;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('cannot individually view a room without being authenticated', function () {
    logout();

    get(route('archiving.register.room.show', $this->room->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual room view route', function () {
    get(route('archiving.register.room.show', $this->room->id))
    ->assertForbidden();
});

test('cannot render individual room view component without specific permission', function () {
    Livewire::test(RoomLivewireShow::class, ['id' => $this->room->id])
    ->assertForbidden();
});

// Happy path
test('renders individual room view component with specific permission', function () {
    grantPermission(PermissionType::RoomView->value);

    get(route('archiving.register.room.show', $this->room->id))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireShow::class);
});

test('pagination returns the amount of stands expected', function () {
    grantPermission(PermissionType::RoomView->value);

    Stand::factory(30)->for($this->room, 'room')->create();

    Livewire::test(RoomLivewireShow::class, ['id' => $this->room->id])
    ->set('per_page', 25)
    ->assertCount('stands', 25);
});

test('individually view a room with specific permission', function () {
    grantPermission(PermissionType::RoomView->value);

    get(route('archiving.register.room.show', $this->room->id))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireShow::class);
});

test('RoomLivewireShow uses trait', function () {
    expect(
        collect(class_uses(RoomLivewireShow::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
