<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Stand;
use App\Models\Room;
use App\Policies\RoomPolicy;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('user without permission cannot list rooms records', function () {
    expect((new RoomPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a room', function () {
    expect((new RoomPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a room', function () {
    expect((new RoomPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a room', function () {
    expect((new RoomPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a room', function () {
    $room = Room::factory()->create();
    $room->loadCount('stands');

    expect((new RoomPolicy())->delete($this->user, $room))->toBeFalse();
});

test('room with stands cannot be delete', function () {
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()
    ->has(Stand::factory(2), 'stands')
    ->create();
    $room->loadCount('stands');

    expect((new RoomPolicy())->delete($this->user, $room))->toBeFalse();
});

// Happy path
test('user with permission can list rooms records', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    expect((new RoomPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a room', function () {
    grantPermission(PermissionType::RoomView->value);

    expect((new RoomPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a room', function () {
    grantPermission(PermissionType::RoomCreate->value);

    expect((new RoomPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a room', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    expect((new RoomPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a room', function () {
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();
    $room->loadCount('stands');

    expect((new RoomPolicy())->delete($this->user, $room))->toBeTrue();
});

test('room without stands can be deleted', function () {
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();
    $room->loadCount('stands');

    expect((new RoomPolicy())->delete($this->user, $room))->toBeTrue();
});
