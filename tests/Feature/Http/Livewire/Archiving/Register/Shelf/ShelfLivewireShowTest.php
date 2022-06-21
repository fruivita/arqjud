<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireShow;
use App\Models\Box;
use App\Models\Shelf;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('cannot individually view a shelf without being authenticated', function () {
    logout();

    get(route('archiving.register.shelf.show', $this->shelf->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual shelf view route', function () {
    get(route('archiving.register.shelf.show', $this->shelf->id))
    ->assertForbidden();
});

test('cannot render individual shelf view component without specific permission', function () {
    Livewire::test(ShelfLivewireShow::class, ['id' => $this->shelf->id])
    ->assertForbidden();
});

// Happy path
test('renders individual shelf view component with specific permission', function () {
    grantPermission(PermissionType::ShelfView->value);

    get(route('archiving.register.shelf.show', $this->shelf->id))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireShow::class);
});

test('pagination returns the amount of boxes expected', function () {
    grantPermission(PermissionType::ShelfView->value);

    Box::factory(30)->for($this->shelf, 'shelf')->create();

    Livewire::test(ShelfLivewireShow::class, ['id' => $this->shelf->id])
    ->set('per_page', 25)
    ->assertCount('boxes', 25);
});

test('individually view a shelf with specific permission', function () {
    grantPermission(PermissionType::ShelfView->value);

    get(route('archiving.register.shelf.show', $this->shelf->id))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireShow::class);
});

test('ShelfLivewireShow uses trait', function () {
    expect(
        collect(class_uses(ShelfLivewireShow::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
