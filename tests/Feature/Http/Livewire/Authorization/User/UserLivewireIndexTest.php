<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\User\UserLivewireIndex;
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

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list users without being authenticated', function () {
    logout();

    get(route('authorization.user.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access users listing route without specific permission', function () {
    get(route('authorization.user.index'))
    ->assertForbidden();
});

test('cannot render users listing component without specific permission', function () {
    Livewire::test(UserLivewireIndex::class)->assertForbidden();
});

test('cannot update user if show_edit_modal is disabled', function () {
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('update')
    ->assertForbidden();
});

test('cannot display user edit modal without specific permission', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertSet('show_edit_modal', false)
    ->assertForbidden();
});

test('cannot update a user without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $livewire = Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertSet('show_edit_modal', true);

    revokePermission(PermissionType::UserUpdate->value);

    $livewire
    ->call('update')
    ->assertForbidden();
});

test('roles are not available if modal cannot be loaded', function () {
    grantPermission(PermissionType::UserViewAny->value);

    expect(Role::count())->toBeGreaterThan(1);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('roles', null)
    ->call('edit', $this->user->id)
    ->assertSet('roles', null);

    expect(Role::count())->toBeGreaterThan(1);
});

test("cannot update user's role of higher level", function () {
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    logout();
    login('bar');

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', Role::ADMINISTRATOR)
    ->call('update')
    ->assertForbidden();

    $this->user->refresh();

    expect($this->user->role_id)->toBe(Role::BUSINESSMANAGER);
});

// Rules
test('id of the role that will be associated with the user must previously exist in the database', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', 2)
    ->call('update')
    ->assertHasErrors(['editing.role_id' => 'exists']);
});

test('id of the role that will be associated with the user is mandatory', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', '')
    ->call('update')
    ->assertHasErrors(['editing.role_id' => 'required']);
});

// Happy path
test('renders listing component of users with view any or update permission', function ($permission) {
    grantPermission($permission);

    get(route('authorization.user.index'))
    ->assertOk()
    ->assertSeeLivewire(UserLivewireIndex::class);
})->with([
    PermissionType::UserViewAny->value,
    PermissionType::UserUpdate->value
]);

test('pagination returns the expected amount of users', function () {
    grantPermission(PermissionType::UserViewAny->value);

    User::factory(30)->create();

    Livewire::test(UserLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('users', 25);
});

test('display user edit modal with specific permission', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->call('edit', $this->user->id)
    ->assertOk()
    ->assertSet('show_edit_modal', true);
});

test('only roles of the same or lower level are available', function () {
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('roles', null)
    ->call('edit', $this->user->id)
    ->assertCount('roles', 3);

    expect(Role::count())->toBe(4);
});

test('emits feedback event when updating a user', function () {
    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('updates a user with specific permission', function () {
    logout();
    $user = login('bar');

    $user->role_id = Role::BUSINESSMANAGER;
    $user->save();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $this->user->refresh();

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user)
    ->assertSet('editing.role_id', Role::ORDINARY)
    ->set('editing.role_id', Role::OBSERVER)
    ->call('update')
    ->assertOk();

    $this->user->refresh();

    expect($this->user->role->id)->toBe(Role::OBSERVER);
});

test('role update removes eventual delegation', function () {
    $department = Department::factory()->create();
    logout();

    $bar = login('bar');

    $bar->role_id = Role::ADMINISTRATOR;
    $bar->department_id = $department->id;
    $bar->save();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    $this->user->role_id = Role::ADMINISTRATOR;
    $this->user->department_id = $department->id;
    $this->user->role_granted_by = $bar->id;
    $this->user->old_role_id = Role::OBSERVER;
    $this->user->save();

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user)
    ->assertSet('editing.role_id', Role::ADMINISTRATOR)
    ->assertSet('editing.role_granted_by', $bar->id)
    ->assertSet('editing.old_role_id', Role::OBSERVER)
    ->set('editing.role_id', Role::BUSINESSMANAGER)
    ->call('update')
    ->assertOk();

    $this->user->refresh();

    expect($this->user->role_id)->toBe(Role::BUSINESSMANAGER)
    ->and($this->user->role_granted_by)->toBeNull()
    ->and($this->user->old_role_id)->toBeNull();
});

test('search returns expected results', function () {
    grantPermission(PermissionType::UserViewAny->value);

    User::factory()->create([
        'name' => 'namefoo',
        'username' => 'userbar',
    ]);

    User::factory()->create([
        'name' => 'namebaz',
        'username' => 'userloren',
    ]);

    User::factory()->create([
        'name' => 'nameloren',
        'username' => 'userdolor',
    ]);

    Livewire::test(UserLivewireIndex::class)
    ->set('term', 'mefoo')
    ->assertCount('users', 1)
    ->set('term', 'lore')
    ->assertCount('users', 2)
    ->set('term', '')
    ->assertCount('users', User::count());
});

test("can update user's role of the same level", function () {
    $this->user->role_id = Role::BUSINESSMANAGER;
    $this->user->save();

    logout();
    $user = login('bar');

    $user->role_id = Role::BUSINESSMANAGER;
    $user->save();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', Role::OBSERVER)
    ->call('update')
    ->assertOk();

    $this->user->refresh();

    expect($this->user->role_id)->toBe(Role::OBSERVER);
});

test("can update user's role of the lower level", function () {
    $this->user->role_id = Role::OBSERVER;
    $this->user->save();

    logout();
    $user = login('bar');

    $user->role_id = Role::BUSINESSMANAGER;
    $user->save();

    grantPermission(PermissionType::UserViewAny->value);
    grantPermission(PermissionType::UserUpdate->value);

    Livewire::test(UserLivewireIndex::class)
    ->call('edit', $this->user->id)
    ->set('editing.role_id', Role::ORDINARY)
    ->call('update')
    ->assertOk();

    $this->user->refresh();

    expect($this->user->role_id)->toBe(Role::ORDINARY);
});

test('valores iniciais do componente estão definidos', function () {
    grantPermission(PermissionType::UserViewAny->value);

    Livewire::test(UserLivewireIndex::class)
    ->assertSet('show_edit_modal', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'nome',
            'usuario',
            'perfil',
            'delegante',
            'acoes',
        ],
        'por_pagina' => 10
    ]);
});

test('UserLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(UserLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
