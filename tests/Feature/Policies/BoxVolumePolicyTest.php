<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\BoxVolumePolicy;
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
test('user without permission cannot list box volumes records', function () {
    expect((new BoxVolumePolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a box volume', function () {
    expect((new BoxVolumePolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a box volume', function () {
    expect((new BoxVolumePolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a box volume', function () {
    expect((new BoxVolumePolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a box volume', function () {
    expect((new BoxVolumePolicy())->delete($this->user))->toBeFalse();
});

// Happy path
test('permission to list box volumes records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxVolumeViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxVolumePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxVolumePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxVolumeViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxVolumePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxVolumePolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a box volume is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxVolumeView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxVolumePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxVolumePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxVolumeView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxVolumePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxVolumePolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a box volume is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxVolumeCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxVolumePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxVolumePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxVolumeCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxVolumePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxVolumePolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a box volume is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxVolumeUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxVolumePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxVolumePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxVolumeUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxVolumePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxVolumePolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a box volume is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxVolumeDelete->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxVolumePolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxVolumePolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxVolumeDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxVolumePolicy())->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxVolumePolicy())->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list box volumes records', function () {
    grantPermission(PermissionType::BoxVolumeViewAny->value);

    expect((new BoxVolumePolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a box volume', function () {
    grantPermission(PermissionType::BoxVolumeView->value);

    expect((new BoxVolumePolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a box volume', function () {
    grantPermission(PermissionType::BoxVolumeCreate->value);

    expect((new BoxVolumePolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a box volume', function () {
    grantPermission(PermissionType::BoxVolumeUpdate->value);

    expect((new BoxVolumePolicy())->update($this->user))->toBeTrue();
});
