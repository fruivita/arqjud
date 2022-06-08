<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireShow;
use App\Models\Room;
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

    get(route('archiving.register.stand.show', $this->stand))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual stand view route', function () {
    get(route('archiving.register.stand.show', $this->stand))
    ->assertForbidden();
});

test('cannot render individual stand view component without specific permission', function () {
    Livewire::test(StandLivewireShow::class, ['stand' => $this->stand])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::StandView->value);

    Livewire::test(StandLivewireShow::class, ['stand' => $this->stand])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual stand view component with specific permission', function () {
    grantPermission(PermissionType::StandView->value);

    get(route('archiving.register.stand.show', $this->stand))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireShow::class);
});

test('pagination returns the amount of shelves expected', function () {
    grantPermission(PermissionType::StandView->value);

    Shelf::factory(120)
    ->for($this->stand, 'stand')
    ->create();

    Livewire::test(StandLivewireShow::class, ['stand' => $this->stand])
    ->assertCount('shelves', 10)
    ->set('per_page', 10)
    ->assertCount('shelves', 10)
    ->set('per_page', 25)
    ->assertCount('shelves', 25)
    ->set('per_page', 50)
    ->assertCount('shelves', 50)
    ->set('per_page', 100)
    ->assertCount('shelves', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::StandView->value);

    Livewire::test(StandLivewireShow::class, ['stand' => $this->stand])
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

test('individually view a stand with specific permission', function () {
    grantPermission(PermissionType::StandView->value);

    get(route('archiving.register.stand.show', $this->stand))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireShow::class);
});
