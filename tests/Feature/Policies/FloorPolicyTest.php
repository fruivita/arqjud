<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\FloorPolicy;
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
test('user without permission cannot list floors records', function () {
    expect((new FloorPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a floor', function () {
    expect((new FloorPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a floor', function () {
    expect((new FloorPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a floor', function () {
    expect((new FloorPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a floor', function () {
    expect((new FloorPolicy())->delete($this->user))->toBeFalse();
});

// Happy path
test('permission to list floors records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::FloorViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new FloorPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new FloorPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::FloorViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new FloorPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new FloorPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a floor is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::FloorView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new FloorPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new FloorPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::FloorView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new FloorPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new FloorPolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a floor is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::FloorCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new FloorPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new FloorPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::FloorCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new FloorPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new FloorPolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a floor is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::FloorUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new FloorPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new FloorPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::FloorUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new FloorPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new FloorPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a floor is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::FloorDelete->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new FloorPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new FloorPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::FloorDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new FloorPolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new FloorPolicy())->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list floors records', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    expect((new FloorPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a floor', function () {
    grantPermission(PermissionType::FloorView->value);

    expect((new FloorPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a floor', function () {
    grantPermission(PermissionType::FloorCreate->value);

    expect((new FloorPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a floor', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    expect((new FloorPolicy())->update($this->user))->toBeTrue();
});
