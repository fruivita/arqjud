<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Shelf;
use App\Models\Stand;
use App\Policies\StandPolicy;
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
test('user without permission cannot list stands records', function () {
    expect((new StandPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a stand', function () {
    expect((new StandPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a stand', function () {
    expect((new StandPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a stand', function () {
    expect((new StandPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a stand', function () {
    $stand = Stand::factory()->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeFalse();
});

test('stand with shelves cannot be deleted', function () {
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()
    ->has(Shelf::factory(2), 'shelves')
    ->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeFalse();
});

// Happy path
test('user with permission can list stands records', function () {
    grantPermission(PermissionType::StandViewAny->value);

    expect((new StandPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a stand', function () {
    grantPermission(PermissionType::StandView->value);

    expect((new StandPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a stand', function () {
    grantPermission(PermissionType::StandCreate->value);

    expect((new StandPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a stand', function () {
    grantPermission(PermissionType::StandUpdate->value);

    expect((new StandPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a stand', function () {
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeTrue();
});

test('stand without shelves can be deleted', function () {
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeTrue();
});
