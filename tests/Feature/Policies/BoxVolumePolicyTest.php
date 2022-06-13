<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\BoxVolumePolicy;
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
