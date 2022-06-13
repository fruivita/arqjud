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

    get(route('archiving.register.shelf.show', $this->shelf))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual shelf view route', function () {
    get(route('archiving.register.shelf.show', $this->shelf))
    ->assertForbidden();
});

test('cannot render individual shelf view component without specific permission', function () {
    Livewire::test(ShelfLivewireShow::class, ['shelf' => $this->shelf])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::ShelfView->value);

    Livewire::test(ShelfLivewireShow::class, ['shelf' => $this->shelf])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual shelf view component with specific permission', function () {
    grantPermission(PermissionType::ShelfView->value);

    get(route('archiving.register.shelf.show', $this->shelf))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireShow::class);
});

test('pagination returns the amount of boxes expected', function () {
    grantPermission(PermissionType::ShelfView->value);

    Box::factory(120)
    ->for($this->shelf, 'shelf')
    ->create();

    Livewire::test(ShelfLivewireShow::class, ['shelf' => $this->shelf])
    ->assertCount('boxes', 10)
    ->set('per_page', 10)
    ->assertCount('boxes', 10)
    ->set('per_page', 25)
    ->assertCount('boxes', 25)
    ->set('per_page', 50)
    ->assertCount('boxes', 50)
    ->set('per_page', 100)
    ->assertCount('boxes', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::ShelfView->value);

    Livewire::test(ShelfLivewireShow::class, ['shelf' => $this->shelf])
    ->assertSessionMissing('per_page')
    ->set('per_page', 10)
    ->assertSessionHas('per_page', 10)
    ->set('per_page', 25)
    ->assertSessionHas('per_page', 25)
    ->set('per_page', 50)
    ->assertSessionHas('per_page', 50)
    ->set('per_page', 100)
    ->assertSessionHas('per_page', 100);
});

test('individually view a shelf with specific permission', function () {
    grantPermission(PermissionType::ShelfView->value);

    get(route('archiving.register.shelf.show', $this->shelf))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireShow::class);
});
