<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\DocumentationPolicy;
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
test('user without permission cannot list documentation records', function () {
    expect((new DocumentationPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a documentation record', function () {
    expect((new DocumentationPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create documentation record', function () {
    expect((new DocumentationPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a documentation record', function () {
    expect((new DocumentationPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot view or update a documentation record', function () {
    expect((new DocumentationPolicy())->viewOrUpdate($this->user))->toBeFalse();
});

test('user without permission cannot delete a documentation record', function () {
    expect((new DocumentationPolicy())->delete($this->user))->toBeFalse();
});

// Happy path
test('user with permission can list documentation records', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    expect((new DocumentationPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a documentation record', function () {
    grantPermission(PermissionType::DocumentationView->value);

    expect((new DocumentationPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create documentation record', function () {
    grantPermission(PermissionType::DocumentationCreate->value);

    expect((new DocumentationPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a documentation record', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    expect((new DocumentationPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually view a documentation record through view or update policy', function () {
    grantPermission(PermissionType::DocumentationView->value);

    expect((new DocumentationPolicy())->viewOrUpdate($this->user))->toBeTrue();
});

test('user with permission can individually update a documentation record through view or update policy', function () {
    grantPermission(PermissionType::DocumentationUpdate->value);

    expect((new DocumentationPolicy())->viewOrUpdate($this->user))->toBeTrue();
});

test('user with permission can individually delete a documentation record', function () {
    grantPermission(PermissionType::DocumentationDelete->value);

    expect((new DocumentationPolicy())->delete($this->user))->toBeTrue();
});
