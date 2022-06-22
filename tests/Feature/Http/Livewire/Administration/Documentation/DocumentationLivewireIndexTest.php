<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Administration\Documentation\DocumentationLivewireIndex;
use App\Models\Documentation;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('cannot list application documentation records without being authenticated', function () {
    logout();

    get(route('administration.doc.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access application documentation records listing route', function () {
    get(route('administration.doc.index'))
    ->assertForbidden();
});

test('cannot render listing component from application documentation records without specific permission', function () {
    Livewire::test(DocumentationLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of expected application documentation records', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    Documentation::factory(30)->create();

    Livewire::test(DocumentationLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('docs', 25);
});

test('lists application documentation records with specific permission', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);

    get(route('administration.doc.index'))
    ->assertOk()
    ->assertSeeLivewire(DocumentationLivewireIndex::class);
});

test('emits feedback event when deleting an application documentation record', function () {
    grantPermission(PermissionType::DocumentationViewAny->value);
    grantPermission(PermissionType::DocumentationDelete->value);

    $doc = Documentation::factory()->create(['app_route_name' => 'foo']);

    Livewire::test(DocumentationLivewireIndex::class)
    ->call('setToDelete', $doc->id)
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
