<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ConfigurationPolicy;
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
test('user without permission cannot individually view a configuration', function () {
    expect((new ConfigurationPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot update a configuration', function () {
    expect((new ConfigurationPolicy())->update($this->user))->toBeFalse();
});

// Happy path
test('user with permission can individually view a configuration', function () {
    grantPermission(PermissionType::ConfigurationView->value);

    expect((new ConfigurationPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can individually update a configuration', function () {
    grantPermission(PermissionType::ConfigurationUpdate->value);

    expect((new ConfigurationPolicy())->update($this->user))->toBeTrue();
});
