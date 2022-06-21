<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireShow;
use App\Models\Shelf;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->stand = Stand::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot individually view a stand without being authenticated', function () {
    logout();

    get(route('archiving.register.stand.show', $this->stand->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual stand view route', function () {
    get(route('archiving.register.stand.show', $this->stand->id))
    ->assertForbidden();
});

test('cannot render individual stand view component without specific permission', function () {
    Livewire::test(StandLivewireShow::class, ['id' => $this->stand->id])
    ->assertForbidden();
});

// Happy path
test('renders individual stand view component with specific permission', function () {
    grantPermission(PermissionType::StandView->value);

    get(route('archiving.register.stand.show', $this->stand->id))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireShow::class);
});

test('pagination returns the amount of shelves expected', function () {
    grantPermission(PermissionType::StandView->value);

    Shelf::factory(30)->for($this->stand, 'stand')->create();

    Livewire::test(StandLivewireShow::class, ['id' => $this->stand->id])
    ->set('per_page', 25)
    ->assertCount('shelves', 25);
});

test('individually view a stand with specific permission', function () {
    grantPermission(PermissionType::StandView->value);

    get(route('archiving.register.stand.show', $this->stand->id))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireShow::class);
});

test('StandLivewireShow uses trait', function () {
    expect(
        collect(class_uses(StandLivewireShow::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
