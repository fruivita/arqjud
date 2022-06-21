<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireShow;
use App\Models\Box;
use App\Models\BoxVolume;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->box = Box::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot individually view a box without being authenticated', function () {
    logout();

    get(route('archiving.register.box.show', $this->box->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual box view route', function () {
    get(route('archiving.register.box.show', $this->box->id))
    ->assertForbidden();
});

test('cannot render individual box view component without specific permission', function () {
    Livewire::test(BoxLivewireShow::class, ['id' => $this->box->id])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BoxView->value);

    Livewire::test(BoxLivewireShow::class, ['id' => $this->box->id])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual role view component with specific permission', function () {
    grantPermission(PermissionType::BoxView->value);

    get(route('archiving.register.box.show', $this->box->id))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireShow::class);
});

test('pagination returns the amount of expected box volumes records', function () {
    grantPermission(PermissionType::BoxView->value);

    BoxVolume::factory(120)->for($this->box, 'box')->create();

    Livewire::test(BoxLivewireShow::class, ['id' => $this->box->id])
    ->assertCount('volumes', 10)
    ->set('per_page', 10)
    ->assertCount('volumes', 10)
    ->set('per_page', 25)
    ->assertCount('volumes', 25)
    ->set('per_page', 50)
    ->assertCount('volumes', 50)
    ->set('per_page', 100)
    ->assertCount('volumes', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::BoxView->value);

    Livewire::test(BoxLivewireShow::class, ['id' => $this->box->id])
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

test('individually view a box with specific permission', function () {
    grantPermission(PermissionType::BoxView->value);

    get(route('archiving.register.box.show', $this->box->id))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireShow::class);
});

test('BoxLivewireShow uses the withsorting trait', function () {
    expect(
        collect(class_uses(BoxLivewireShow::class))
        ->contains(\App\Http\Livewire\Traits\WithSorting::class)
    )->toBeTrue();
});
