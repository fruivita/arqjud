<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireIndex;
use App\Models\Box;
use App\Models\Shelf;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->shelf = Shelf::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list shelf records without being authenticated', function () {
    logout();

    get(route('archiving.register.shelf.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access shelf records listing route', function () {
    get(route('archiving.register.shelf.index'))
    ->assertForbidden();
});

test('cannot render listing component from shelf records without specific permission', function () {
    Livewire::test(ShelfLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of expected shelf records', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    Shelf::factory(30)->create();

    Livewire::test(ShelfLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('shelves', 25);
});

test('lists shelf records with specific permission', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    get(route('archiving.register.shelf.index'))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    $this->shelf->delete();

    Shelf::factory()->create(['number' => 10]);
    Shelf::factory()->create(['number' => 210]); // contains 10
    Shelf::factory()->create(['number' => 20]);

    Livewire::test(ShelfLivewireIndex::class)
    ->set('term', '210')
    ->assertCount('shelves', 1)
    ->set('term', '10')
    ->assertCount('shelves', 2)
    ->set('term', '')
    ->assertCount('shelves', 3);
});

test('emits feedback event when deleting a shelf record', function () {
    grantPermission(PermissionType::ShelfViewAny->value);
    grantPermission(PermissionType::ShelfDelete->value);

    Livewire::test(ShelfLivewireIndex::class)
    ->call('setToDelete', $this->shelf->id)
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

test('ShelfLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(ShelfLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
