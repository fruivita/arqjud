<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Delegation\DelegationLivewireIndex;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->department = Department::factory()->create();

    $this->user = login('foo');
    $this->user->department_id = $this->department->id;
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot access delegation page without being authenticated', function () {
    logout();

    get(route('authorization.delegations.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access department delegations listing route', function () {
    get(route('authorization.permission.index'))
    ->assertForbidden();
});

test('cannot render department delegations listing component without specific permission', function () {
    Livewire::test(DelegationLivewireIndex::class)->assertForbidden();
});

test('user cannot delegate role, if delegated role is higher in application', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('user cannot delegate role to user from another department', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $department_a = Department::factory()->create();
    $user_bar = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::OBSERVER,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::OBSERVER)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('user cannot remove non-existent delegation', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::OBSERVER,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_bar)
    ->assertForbidden();

    expect($user_bar->role_id)->toBe(Role::OBSERVER)
    ->and($user_bar->role_granted_by)->toBeNull();
});

test('user cannot remove higher role user delegation', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id,
        'old_role_id' => Role::OBSERVER,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_taz)
    ->assertForbidden();

    expect($user_taz->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_taz->role_granted_by)->toBe($user_bar->id)
    ->and($user_taz->old_role_id)->toBe(Role::OBSERVER);
});

test('user cannot remove user delegation from another department', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $department_a = Department::factory()->create();
    $user_bar = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::ADMINISTRATOR,
    ]);
    $user_taz = User::factory()->create([
        'department_id' => $department_a->id,
        'role_id' => Role::ADMINISTRATOR,
        'role_granted_by' => $user_bar->id,
        'old_role_id' => Role::OBSERVER,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_taz)
    ->assertForbidden();

    expect($user_taz->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_taz->role_granted_by)->toBe($user_bar->id)
    ->and($user_taz->old_role_id)->toBe(Role::OBSERVER);
});

// Happy path
test('with specific permission it is possible to render the department delegations listing component', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    get(route('authorization.delegations.index'))
    ->assertOk()
    ->assertSeeLivewire(DelegationLivewireIndex::class);
});

test('pagination returns the expected amount of users', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    User::factory(30)->for($this->department, 'department')->create();

    Livewire::test(DelegationLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('users', 25);
});

test('displays only the users from the same department', function () {
    grantPermission(PermissionType::DelegationViewAny->value);

    User::factory(30)->create();
    User::factory(5)->for($this->department, 'department')->create();

    Livewire::test(DelegationLivewireIndex::class)
    ->assertCount('users', 6);
});

test('user can delegate role within the same department if delegated role is lower in application', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::ORDINARY,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertHasNoErrors()
    ->assertOk();

    expect($user_bar->role_id)->toBe(Role::BUSINESSMANAGER)
    ->and($user_bar->role_granted_by)->toBe($this->user->id)
    ->and($user_bar->old_role_id)->toBe(Role::ORDINARY);
});

test('user can remove user delegation from the same department, with the same or lower role, even delegated by someone else', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::OBSERVER,
    ]);

    $user_baz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::BUSINESSMANAGER,
        'role_granted_by' => $this->user->id,
        'old_role_id' => Role::OBSERVER,
    ]);

    $user_taz = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::OBSERVER,
        'role_granted_by' => $user_bar->id,
        'old_role_id' => Role::ORDINARY,
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->call('destroy', $user_baz)
    ->assertHasNoErrors()
    ->assertOk()
    ->call('destroy', $user_taz)
    ->assertHasNoErrors()
    ->assertOk();

    expect($user_baz->role_id)->toBe(Role::OBSERVER)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_baz->old_role_id)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull()
    ->and($user_taz->old_role_id)->toBeNull();
});

test("delegation assigns authenticated user role and revocation assigns the previous user's role", function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    $user_bar = User::factory()->create([
        'department_id' => $this->department->id,
        'role_id' => Role::OBSERVER,
    ]);

    $livewire = Livewire::test(DelegationLivewireIndex::class)
    ->call('create', $user_bar)
    ->assertHasNoErrors()
    ->assertOk();

    expect($user_bar->role_id)->toBe(Role::BUSINESSMANAGER)
    ->and($user_bar->role_granted_by)->toBe($this->user->id)
    ->and($user_bar->old_role_id)->toBe(Role::OBSERVER);

    $livewire
    ->call('destroy', $user_bar)
    ->assertHasNoErrors()
    ->assertOk();

    expect($user_bar->role_id)->toBe(Role::OBSERVER)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_bar->old_role_id)->toBeNull();
});

test('search returns expected results', function () {
    grantPermission(PermissionType::DelegationViewAny->value);
    grantPermission(PermissionType::DelegationCreate->value);

    User::factory()->create([
        'name' => 'fulano bar',
        'username' => 'bar baz',
        'department_id' => $this->department->id,
    ]);

    User::factory()->create([
        'name' => 'fulano foo bazz',
        'username' => 'taz',
        'department_id' => $this->department->id,
    ]);

    // will not be displayed, because its from another department
    User::factory()
    ->for(Department::factory(), 'department')
    ->create([
        'name' => 'another department fulano foo bazz',
        'username' => 'another taz',
    ]);

    Livewire::test(DelegationLivewireIndex::class)
    ->set('term', 'taz')
    ->assertCount('users', 1)
    ->set('term', 'fulano')
    ->assertCount('users', 2);
});
