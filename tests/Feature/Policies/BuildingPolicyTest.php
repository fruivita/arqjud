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
