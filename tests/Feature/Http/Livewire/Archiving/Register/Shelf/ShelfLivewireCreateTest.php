<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireCreate;
use App\Models\Box;
use App\Models\Shelf;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
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
test('cannot create a shelf record without being authenticated', function () {
    logout();

    get(route('archiving.register.shelf.create', $this->stand->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access shelf record creation route', function () {
    get(route('archiving.register.shelf.create', $this->stand->id))
    ->assertForbidden();
});

test('cannot render shelf record creation component without specific permission', function () {
    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.number', '')
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.number', 0)
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'between'])
    ->set('shelf.number', 100001)
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'between']);
});

test('number and stand_id must be unique', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Shelf::factory()->create(['number' => 99, 'stand_id' => $this->stand->id]);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.number', 99)
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.description', '')
    ->call('store')
    ->assertHasNoErrors(['shelf.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['shelf.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['shelf.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected shelf records', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Shelf::factory(30)->for($this->stand, 'stand')->create();

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('per_page', 25)
    ->assertCount('shelves', 25);
});

test('renders shelf record creation component with specific permission', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    get(route('archiving.register.shelf.create', $this->stand->id))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireCreate::class);
});

test('emits feedback event when creates a shelf record', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.number', 1)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a shelf record', function () {
    grantPermission(PermissionType::ShelfCreate->value);
    grantPermission(PermissionType::ShelfDelete->value);

    $shelf = Shelf::factory()->create();

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->call('setToDelete', $shelf->id)
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

test('creates a shelf record with specific permission', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.number', 99)
    ->set('shelf.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $shelf = Shelf::with('stand')->first();

    expect($shelf->number)->toBe(99)
    ->and($shelf->description)->toBe('foo bar')
    ->and($shelf->stand->id)->toBe($this->stand->id);
});

test('reset to a blank model after the shelf is created', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    $blank = new Shelf();

    Livewire::test(ShelfLivewireCreate::class, ['id' => $this->stand->id])
    ->set('shelf.number', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('shelf', $blank);
});

test('ShelfLivewireCreate uses trait', function () {
    expect(
        collect(class_uses(ShelfLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
