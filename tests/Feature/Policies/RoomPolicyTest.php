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
test('permission to list rooms records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoomViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RoomPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RoomPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoomViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RoomPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RoomPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a room is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoomView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RoomPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RoomPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoomView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RoomPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RoomPolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a room is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoomCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RoomPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RoomPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoomCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RoomPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RoomPolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a room is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoomUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RoomPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RoomPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoomUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RoomPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RoomPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a room is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();
    $room->loadCount('stands');

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new RoomPolicy())->delete($this->user, $room))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new RoomPolicy())->delete($this->user, $room))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::RoomDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new RoomPolicy())->delete($this->user, $room))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new RoomPolicy())->delete($this->user, $room))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

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
