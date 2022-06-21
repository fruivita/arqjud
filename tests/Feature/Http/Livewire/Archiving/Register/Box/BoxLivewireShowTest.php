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

// Happy path
test('renders individual role view component with specific permission', function () {
    grantPermission(PermissionType::BoxView->value);

    get(route('archiving.register.box.show', $this->box->id))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireShow::class);
});

test('pagination returns the amount of expected box volumes records', function () {
    grantPermission(PermissionType::BoxView->value);

    BoxVolume::factory(30)->for($this->box, 'box')->create();

    Livewire::test(BoxLivewireShow::class, ['id' => $this->box->id])
    ->set('per_page', 25)
    ->assertCount('volumes', 25);
});

test('individually view a box with specific permission', function () {
    grantPermission(PermissionType::BoxView->value);

    get(route('archiving.register.box.show', $this->box->id))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireShow::class);
});

test('BoxLivewireShow uses trait', function () {
    expect(
        collect(class_uses(BoxLivewireShow::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
