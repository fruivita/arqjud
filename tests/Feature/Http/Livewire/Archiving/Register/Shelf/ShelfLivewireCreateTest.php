<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireCreate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Site;
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

    get(route('archiving.register.shelf.create', $this->stand))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access shelf record creation route', function () {
    get(route('archiving.register.shelf.create', $this->stand))
    ->assertForbidden();
});

test('cannot render shelf record creation component without specific permission', function () {
    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.number', '')
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.number', 0)
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'between'])
    ->set('shelf.number', 100001)
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'between']);
});

test('number and stand_id must be unique', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Shelf::factory()->create(['number' => 1, 'stand_id' => $this->stand->id]);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.number', 1)
    ->call('store')
    ->assertHasErrors(['shelf.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.description', '')
    ->call('store')
    ->assertHasNoErrors(['shelf.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['shelf.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['shelf.description' => 'max']);
});

// Happy path
test('renders shelf record creation component with specific permission', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    get(route('archiving.register.shelf.create', $this->stand))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireCreate::class);
});

test('emits feedback event when creates a shelf record', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.number', 1)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('creates a shelf record with specific permission', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    expect(Shelf::count())->toBe(0);

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.number', 1)
    ->set('shelf.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $shelf = Shelf::with('stand')->first();

    expect($shelf->number)->toBe(1)
    ->and($shelf->description)->toBe('foo bar')
    ->and($shelf->stand->id)->toBe($this->stand->id);
});

test('reset to a blank model after the shelf is created', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    $blank = new Shelf();

    Livewire::test(ShelfLivewireCreate::class, ['stand' => $this->stand])
    ->set('shelf.number', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('shelf', $blank);
});
