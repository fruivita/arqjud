<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireCreate;
use App\Models\Building;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a site record without being authenticated', function () {
    logout();

    get(route('archiving.register.site.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access site record creation route', function () {
    get(route('archiving.register.site.create'))
    ->assertForbidden();
});

test('cannot render site record creation component without specific permission', function () {
    Livewire::test(SiteLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('name is required', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', '')
    ->call('store')
    ->assertHasErrors(['site.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', ['foo'])
    ->call('store')
    ->assertHasErrors(['site.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', Str::random(101))
    ->call('store')
    ->assertHasErrors(['site.name' => 'max']);
});

test('name must be unique', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.description', '')
    ->call('store')
    ->assertHasNoErrors(['site.description']);
});

test('description must be an string', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['site.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['site.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected site records', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Site::factory(30)->create();

    Livewire::test(SiteLivewireCreate::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('sites', 25);
});

test('renders site record creation component with specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    get(route('archiving.register.site.create'))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireCreate::class);
});

test('emits feedback event when creates a site record', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a site record', function () {
    grantPermission(PermissionType::SiteCreate->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();

    Livewire::test(SiteLivewireCreate::class)
    ->call('setToDelete', $site->id)
    ->call('destroy')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => null,
        'timeout' => 3000,
    ]);
});

test('creates a site record with specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->set('site.description', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $site = Site::first();

    expect($site->name)->toBe('foo')
    ->and($site->description)->toBe('foo bar');
});

test('reset to a blank model after the site is created', function () {
    grantPermission(PermissionType::SiteCreate->value);

    $blank = new Site();

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertOk()
    ->assertSet('site', $blank);
});

test('preferencias estão definidas', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'localidade',
            'qtd_predios',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('SiteLivewireCreate uses trait', function () {
    expect(
        collect(class_uses(SiteLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
