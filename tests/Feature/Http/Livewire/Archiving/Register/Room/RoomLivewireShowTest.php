<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireShow;
use App\Models\Box;
use App\Models\Room;
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

    get(route('archiving.register.room.show', $this->room))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual room view route', function () {
    get(route('archiving.register.room.show', $this->room))
    ->assertForbidden();
});

test('cannot render individual room view component without specific permission', function () {
    Livewire::test(RoomLivewireShow::class, ['room' => $this->room])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::RoomView->value);

    Livewire::test(RoomLivewireShow::class, ['room' => $this->room])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual room view component with specific permission', function () {
    grantPermission(PermissionType::RoomView->value);

    get(route('archiving.register.room.show', $this->room))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireShow::class);
});

test('pagination returns the amount of boxes expected', function () {
    grantPermission(PermissionType::RoomView->value);

    Box::factory(120)
    ->for($this->room, 'room')
    ->create();

    Livewire::test(RoomLivewireShow::class, ['room' => $this->room])
    ->assertCount('boxes', 10)
    ->set('per_page', 10)
    ->assertCount('boxes', 10)
    ->set('per_page', 25)
    ->assertCount('boxes', 25)
    ->set('per_page', 50)
    ->assertCount('boxes', 50)
    ->set('per_page', 100)
    ->assertCount('boxes', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::RoomView->value);

    Livewire::test(RoomLivewireShow::class, ['room' => $this->room])
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

test('individually view a room with specific permission', function () {
    grantPermission(PermissionType::RoomView->value);

    get(route('archiving.register.room.show', $this->room))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireShow::class);
});

test('next and previous are available when viewing individual rooms, even when dealing with the first or last record', function () {
    $this->room->delete();
    grantPermission(PermissionType::RoomView->value);

    $first = Room::factory()->create(['number' => 10]);
    $second = Room::factory()->create(['number' => 20]);
    $last = Room::factory()->create(['number' => 30]);

    // has previous and next
    Livewire::test(RoomLivewireShow::class, ['room' => $second])
    ->assertSet('previous', $first->id)
    ->assertSet('next', $last->id);

    // only has next
    Livewire::test(RoomLivewireShow::class, ['room' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', $second->id);

    // has only previous
    Livewire::test(RoomLivewireShow::class, ['room' => $last])
    ->assertSet('previous', $second->id)
    ->assertSet('next', null);
});
