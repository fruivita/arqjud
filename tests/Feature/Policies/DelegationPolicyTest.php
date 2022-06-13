<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Policies\DelegationPolicy;
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
test("user without permission cannot list their department's delegations", function () {
    expect((new DelegationPolicy())->viewAny($this->user))->toBeFalse();
});

test('user cannot delegate role, if delegated role is higher in application', function () {
    $department_a = Department::factory()->create();
    $department_b = Department::factory()->create();

    $user_bar = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    $this->user->department_id = $department_a->id;
    $this->user->role_id = Role::ORDINARY;
    $this->user->save();

    grantPermission(PermissionType::DelegationCreate->value);

    expect((new DelegationPolicy())->create($this->user, $user_bar))->toBeFalse();
});

test('user cannot delegate role to user from another department', function () {
    $department_a = Department::factory()->create();
    $department_b = Department::factory()->create();

    $this->user->department_id = $department_a->id;
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::ORDINARY,
    ]);

    expect((new DelegationPolicy())->create($this->user, $user_bar))->toBeFalse();
});

test('user cannot delegate role without specific permission', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    expect((new DelegationPolicy())->create($this->user, $user_bar))->toBeFalse();
});

test('user cannot delegate delegated role', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->save();

    grantPermission(PermissionType::DelegationCreate->value);

    logout();

    $user_bar = login('bar');

    $user_bar->department_id = $department->id;
    $user_bar->role_id = Role::ADMINISTRATOR;
    $user_bar->role_granted_by = $this->user->id;
    $user_bar->old_role_id = Role::BUSINESSMANAGER;
    $user_bar->save();

    $user_taz = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::OBSERVER,
    ]);

    expect((new DelegationPolicy())->create($user_bar, $user_taz))->toBeFalse();
});

test('user cannot remove non-existent delegation', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    expect((new DelegationPolicy())->delete($this->user, $user_bar))->toBeFalse();
});

test('user cannot remove higher role delegation', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id,
    ]);

    expect((new DelegationPolicy())->delete($this->user, $user_taz))->toBeFalse();
});

test('user cannot remove user delegation from another department', function () {
    $department_a = Department::factory()->create();
    $department_b = Department::factory()->create();

    $this->user->department_id = $department_a->id;
    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::BUSINESSMANAGER,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $department_b->id,
        'role_id' => Role::BUSINESSMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    expect((new DelegationPolicy())->delete($this->user, $user_taz))->toBeFalse();
});

// Happy path
test('user can list delegations from his department if it has permission', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    expect((new DelegationPolicy())->viewAny($this->user))->toBeTrue();
});

test('user can delegate role within the same department, if delegated role is lower in application and has permission', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::ORDINARY,
    ]);

    expect((new DelegationPolicy())->create($this->user, $user_bar))->toBeTrue();
});

test('user can remove user delegation from the same department, with the same or lower role, even delegated by someone else', function () {
    $department = Department::factory()->create();

    $this->user->department_id = $department->id;
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    $user_bar = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    $user_baz = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::BUSINESSMANAGER,
        'role_granted_by' => $user_bar->id,
    ]);

    $user_taz = User::factory()->create([
        'department_id' => $department->id,
        'role_id' => Role::OBSERVER,
        'role_granted_by' => $user_bar->id,
    ]);

    expect((new DelegationPolicy())->delete($this->user, $user_baz))->toBeTrue()
    ->and((new DelegationPolicy())->delete($this->user, $user_taz))->toBeTrue();
});
