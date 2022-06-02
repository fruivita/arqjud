<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Building;
use App\Models\Floor;
use App\Policies\BuildingPolicy;
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
test('user without permission cannot list buildings records', function () {
    expect((new BuildingPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a building', function () {
    expect((new BuildingPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a building', function () {
    expect((new BuildingPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a building', function () {
    expect((new BuildingPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a building', function () {
    $building = Building::factory()->create();
    $building->loadCount('floors');

    expect((new BuildingPolicy())->delete($this->user, $building))->toBeFalse();
});

test('building with floors cannot be delete', function () {
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()
    ->has(Floor::factory(2), 'floors')
    ->create();
    $building->loadCount('floors');

    expect((new BuildingPolicy())->delete($this->user, $building))->toBeFalse();
});

// Happy path
test('permission to list buildings records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BuildingViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BuildingPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BuildingPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BuildingViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BuildingPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BuildingPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a building is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BuildingView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BuildingPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BuildingPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BuildingView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BuildingPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BuildingPolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a building is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BuildingCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BuildingPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BuildingPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BuildingCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BuildingPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BuildingPolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a building is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BuildingUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BuildingPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BuildingPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BuildingUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BuildingPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BuildingPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a building is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();
    $building->loadCount('floors');

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BuildingPolicy())->delete($this->user, $building))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BuildingPolicy())->delete($this->user, $building))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BuildingDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BuildingPolicy())->delete($this->user, $building))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BuildingPolicy())->delete($this->user, $building))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list buildings records', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    expect((new BuildingPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a building', function () {
    grantPermission(PermissionType::BuildingView->value);

    expect((new BuildingPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a building', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    expect((new BuildingPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a building', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    expect((new BuildingPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a building', function () {
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();
    $building->loadCount('floors');

    expect((new BuildingPolicy())->delete($this->user, $building))->toBeTrue();
});

test('building without floors can be deleted', function () {
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();
    $building->loadCount('floors');

    expect((new BuildingPolicy())->delete($this->user, $building))->toBeTrue();
});
