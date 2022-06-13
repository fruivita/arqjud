<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\SimulationPolicy;
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
test('user without permission cannot create a simulation', function () {
    expect((new SimulationPolicy())->create($this->user))->toBeFalse();
});

test('user cannot simultaneously create two simulations in the same session', function () {
    grantPermission(PermissionType::SimulationCreate->value);
    session()->put('simulated', 'bar');

    expect((new SimulationPolicy())->create($this->user))->toBeFalse();
});

test('user cannot undo a simulation if it does not exist in their session', function () {
    expect((new SimulationPolicy())->delete($this->user))->toBeFalse();
});

// Happy path
test('user with permission can create a simulation', function () {
    grantPermission(PermissionType::SimulationCreate->value);

    expect((new SimulationPolicy())->create($this->user))->toBeTrue();
});

test('user can undo a simulation if it exists in their session', function () {
    session()->put('simulator', 'bar');

    expect((new SimulationPolicy())->delete($this->user))->toBeTrue();
});
