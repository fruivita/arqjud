<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\RolePolicy;
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
test('user without permission cannot list roles', function () {
    expect((new RolePolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a role', function () {
    expect((new RolePolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot update a role', function () {
    expect((new RolePolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot view or update a role', function () {
    expect((new RolePolicy())->viewOrUpdate($this->user))->toBeFalse();
});

// Happy path
test('user with permission can list roles', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    expect((new RolePolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a role', function () {
    grantPermission(PermissionType::RoleView->value);

    expect((new RolePolicy())->view($this->user))->toBeTrue();
});

test('user with permission can individually update a role', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    expect((new RolePolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually view a role through view or update policy', function () {
    grantPermission(PermissionType::RoleView->value);

    expect((new RolePolicy())->viewOrUpdate($this->user))->toBeTrue();
});

test('user with permission can individually update a role through view or update policy', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    expect((new RolePolicy())->viewOrUpdate($this->user))->toBeTrue();
});
