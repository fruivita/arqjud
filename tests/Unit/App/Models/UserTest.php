<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);
});

// Exceptions
test('throws an exception when trying to create users in duplicate, that is, with the same username or guid', function () {
    expect(
        fn () => User::factory(2)->create(['username' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => User::factory(2)->create(['guid' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create user with invalid field', function ($field, $value, $message) {
    expect(
        fn () => User::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['name', Str::random(256),     'Data too long for column'], // maximum 255 characters
    ['username', Str::random(21),  'Data too long for column'], // maximum 20 characters
    ['username', null,             'cannot be null'],           // required
    ['password', Str::random(256), 'Data too long for column'], // maximum 255 characters
    ['guid', Str::random(256),     'Data too long for column'], // maximum 255 characters
    ['domain', Str::random(256),   'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => User::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['role_id',         10, 'Cannot add or update a child row'], // nonexistent
    ['role_granted_by', 10, 'Cannot add or update a child row'], // nonexistent
    ['old_role_id',     10, 'Cannot add or update a child row'], // nonexistent
]);

// Happy path
test('create many users', function () {
    User::factory(30)->create();

    expect(User::count())->toBe(30);
});

test('optional user fields are accepted', function () {
    User::factory()->create(['name' => null]);

    expect(User::count())->toBe(1);
});

test('fields in their maximum size are accepted', function () {
    User::factory()->create([
        'name' => Str::random(255),
        'username' => Str::random(20),
        'password' => Str::random(255),
        'guid' => Str::random(255),
        'domain' => Str::random(255),
    ]);

    expect(User::count())->toBe(1);
});

test('one user has one role', function () {
    $role = Role::factory()->create();

    $user = User::factory()
    ->for($role, 'role')
    ->create();

    $user->load(['role']);

    expect($user->role)->toBeInstanceOf(Role::class);
});

test('one user can have one old role', function () {
    $role = Role::factory()->create();

    $user = User::factory()
    ->for($role, 'oldRole')
    ->create();

    $user->load(['oldRole']);

    expect($user->oldRole)->toBeInstanceOf(Role::class);
});

test('default user role is ordinary', function () {
    $user = User::create([
        'username' => 'foo',
    ]);

    $user->refresh();

    expect($user->role->id)->toBe(Role::ORDINARY);
});

test('if do not inform a department, the default department is "departmentless"', function () {
    $user = User::create([
        'username' => 'foo',
    ]);

    $user->refresh();

    expect($user->department->id)->toBe(Department::DEPARTMENTLESS);
});

test('user can delegate their role to several others, however the user can only receive a single delegation', function () {
    $delegated_amount = 3;

    $user_delegator = User::factory()->create(['role_id' => Role::ADMINISTRATOR]);

    User::factory(3)->create([
        'role_id' => Role::BUSINESSMANAGER,
        'role_granted_by' => $user_delegator->id,
        'old_role_id' => Role::OBSERVER,
    ]);

    $user_delegator->load(['delegatedUsers', 'delegator']);
    $user_delegated = User::with('delegator')
    ->where('role_granted_by', $user_delegator->id)
    ->get()
    ->random();

    expect($user_delegator->delegatedUsers)->toHaveCount($delegated_amount)
    ->and($user_delegator->delegator)->toBeNull()
    ->and($user_delegated->delegator->id)->toBe($user_delegator->id)
    ->and($user_delegated->delegatedUsers)->toHaveCount(0);
});

test('hasPermission tells whether or not the user has a certain permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    login('foo');

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeFalse();

    grantPermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeTrue();

    revokePermission(PermissionType::SimulationCreate->value);

    expect(authenticatedUser()->hasPermission(PermissionType::SimulationCreate))->toBeFalse();

    logout();
});

test('forHumans returns username formatted for display', function () {
    $samaccountname = 'foo';
    $user = login($samaccountname);

    expect($user->forHumans())->toBe($samaccountname);

    logout();
});

test('returns users using the defined default sort scope', function () {
    $first = ['name' => 'foo', 'username' => 'bar'];
    $second = ['name' => 'foo', 'username' => 'baz'];
    $third = ['name' => null, 'username' => 'barr'];
    $fourth = ['name' => null, 'username' => 'barz'];

    User::factory()->create($second);
    User::factory()->create($first);
    User::factory()->create($fourth);
    User::factory()->create($third);

    $users = User::defaultOrder()->get();

    expect($users->get(0)->username)->toBe($first['username'])
    ->and($users->get(1)->username)->toBe($second['username'])
    ->and($users->get(2)->username)->toBe($third['username'])
    ->and($users->get(3)->username)->toBe($fourth['username']);
});

test('search, with partial term or not, returns the expected values', function () {
    User::factory()->create(['username' => 'foo', 'name' => 'foo']);
    User::factory()->create(['username' => 'bar', 'name' => 'foo bar']);
    User::factory()->create(['username' => 'foo baz', 'name' => 'foo bar baz']);

    expect(User::search('fo')->get())->toHaveCount(3)
    ->and(User::search('bar')->get())->toHaveCount(2)
    ->and(User::search('az')->get())->toHaveCount(1)
    ->and(User::search('foo bar ba')->get())->toHaveCount(1)
    ->and(User::search('foo baz')->get())->toHaveCount(1);
});

test('method delegate grant to the informed user the same role and save his old role', function () {
    $user_foo = User::factory()->create([
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::OBSERVER,
    ]);

    $user_foo->delegate($user_bar);

    $user_bar->refresh();

    expect($user_bar->role_id)->toBe(Role::BUSINESSMANAGER)
    ->and($user_bar->role_granted_by)->toBe($user_foo->id)
    ->and($user_bar->old_role_id)->toBe(Role::OBSERVER);
});

test('revokeDelegation revokes the role of the user and return it to his previous role', function () {
    $user_foo = User::factory()->create([
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    $user_bar = User::factory()->create([
        'role_id' => Role::BUSINESSMANAGER,
        'role_granted_by' => $user_foo->id,
        'old_role_id' => Role::OBSERVER,
    ]);

    $user_bar->revokeDelegation();

    $user_foo->refresh();
    $user_bar->refresh();

    expect($user_foo->role_id)->toBe(Role::BUSINESSMANAGER)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_bar->role_id)->toBe(Role::OBSERVER)
    ->and($user_bar->role_granted_by)->toBeNull()
    ->and($user_bar->role_granted_by)->toBeNull();
});

test("updateAndRevokeDelegatedUsers updates the role, removes the user's delegations and the ones he made", function () {
    $user_foo = User::factory()->create([
        'role_id' => Role::BUSINESSMANAGER,
    ]);

    $user_baz = User::factory()->create([
        'role_id' => Role::BUSINESSMANAGER,
        'role_granted_by' => $user_foo->id,
        'old_role_id' => Role::OBSERVER,
    ]);

    $user_taz = User::factory()->create([
        'role_id' => Role::BUSINESSMANAGER,
        'role_granted_by' => $user_foo->id,
        'old_role_id' => Role::ORDINARY,
    ]);

    $user_foo->role_id = Role::ADMINISTRATOR;
    $user_foo->updateAndRevokeDelegatedUsers();

    $user_foo->refresh();
    $user_baz->refresh();
    $user_taz->refresh();

    expect($user_foo->role_id)->toBe(Role::ADMINISTRATOR)
    ->and($user_foo->role_granted_by)->toBeNull()
    ->and($user_foo->old_role_id)->toBeNull()
    ->and($user_baz->role_id)->toBe(Role::OBSERVER)
    ->and($user_baz->role_granted_by)->toBeNull()
    ->and($user_baz->old_role_id)->toBeNull()
    ->and($user_taz->role_id)->toBe(Role::ORDINARY)
    ->and($user_taz->role_granted_by)->toBeNull()
    ->and($user_taz->old_role_id)->toBeNull();
});

test('isSuperAdmin correctly identifies a superadmin', function () {
    $this->seed(ConfigurationSeeder::class);

    $user_bar = login('bar');
    $user_bar->refresh();

    expect($user_bar->isSuperAdmin())->toBeFalse();

    logout();

    $user_foo = login('dumb user');
    $user_foo->refresh();

    expect($user_foo->isSuperAdmin())->toBeTrue();
});

test('without configuration set, isSuperAdmin returns false for any user', function () {
    $user_bar = login('bar');
    $user_bar->refresh();

    expect($user_bar->isSuperAdmin())->toBeFalse();

    logout();

    $user_foo = login('dumb user');
    $user_foo->refresh();

    expect($user_foo->isSuperAdmin())->toBeFalse();
});

test('permissions() returns the id of all user permissions', function () {
    $user_bar = login('bar');
    $user_bar->refresh();

    expect($user_bar->permissions())->toBeEmpty();

    grantPermission(PermissionType::LogViewAny->value);
    grantPermission(PermissionType::SimulationCreate->value);

    expect($user_bar->permissions())->toContain(
        PermissionType::LogViewAny->value,
        PermissionType::SimulationCreate->value
    );
});

test("roleByDelegation check if the user's role was obtained by delegation or if it is an original role", function () {
    $user_foo = User::factory()->create();

    $user_bar = User::factory()->create();

    expect($user_bar->roleByDelegation())->toBeFalse();

    $user_bar->old_role_id = $user_bar->role_id;
    $user_bar->role_id = $user_foo->role_id;
    $user_bar->role_granted_by = $user_foo->id;
    $user_bar->save();

    expect($user_bar->roleByDelegation())->toBeTrue();
});
