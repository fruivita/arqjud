<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireCreate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->building = Building::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a floor record without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.create', $this->building))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor record creation route', function () {
    get(route('archiving.register.floor.create', $this->building))
    ->assertForbidden();
});

test('cannot render floor record creation component without specific permission', function () {
    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->assertForbidden();
});

test('cannot set the floor record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $floor = Floor::factory()->create();

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->assertOk()
    ->call('markToDelete', $floor->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the floor record which will be deleted if it has rooms', function () {
    grantPermission(PermissionType::FloorCreate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->create();

    Room::factory()
    ->for($floor, 'floor')
    ->create();

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->assertOk()
    ->call('markToDelete', $floor->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a floor record without specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->create();

    $component = Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->call('markToDelete', $floor->id)
    ->assertOk();

    revokePermission(PermissionType::FloorDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Floor::where('id', $floor->id)->exists())->toBeTrue();
});

test('cannot delete a floor record if it has rooms', function () {
    grantPermission(PermissionType::FloorCreate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->create();

    $component = Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->call('markToDelete', $floor->id)
    ->assertOk();

    Room::factory()
    ->for($floor, 'floor')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Floor::where('id', $floor->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('number is required', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.number', '')
    ->call('store')
    ->assertHasErrors(['floor.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['floor.number' => 'integer']);
});

test('number must be between -100 and 300', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.number', -101)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'between'])
    ->set('floor.number', 301)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'between']);
});

test('number and building_id must be unique', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Floor::factory()->create(['number' => 1, 'building_id' => $this->building->id]);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.number', 1)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.description', '')
    ->call('store')
    ->assertHasNoErrors(['floor.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['floor.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['floor.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected floor records', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Floor::factory(120)
    ->for($this->building, 'building')
    ->create();

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->assertCount('floors', 10)
    ->set('per_page', 10)
    ->assertCount('floors', 10)
    ->set('per_page', 25)
    ->assertCount('floors', 25)
    ->set('per_page', 50)
    ->assertCount('floors', 50)
    ->set('per_page', 100)
    ->assertCount('floors', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
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

test('renders floor record creation component with specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);

    get(route('archiving.register.floor.create', $this->building))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireCreate::class);
});

test('emits feedback event when creates a floor record', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.number', 1)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a floor record', function () {
    grantPermission(PermissionType::FloorCreate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->create();

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->call('markToDelete', $floor->id)
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

test('creates a floor record with specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);

    expect(Floor::count())->toBe(0);

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.number', 1)
    ->set('floor.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $floor = Floor::with('building')->first();

    expect($floor->number)->toBe(1)
    ->and($floor->description)->toBe('foo bar')
    ->and($floor->building->id)->toBe($this->building->id);
});

test('reset to a blank model after the floor is created', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $blank = new Floor();

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->set('floor.number', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('floor', $blank);
});

test('defines the floor record that will be deleted with specific permission if it has no rooms', function () {
    grantPermission(PermissionType::FloorCreate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->create();

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->call('markToDelete', $floor->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $floor->id);
});

test('delete a floor record with specific permission if it has no rooms', function () {
    grantPermission(PermissionType::FloorCreate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->create();

    Livewire::test(FloorLivewireCreate::class, ['building' => $this->building])
    ->call('markToDelete', $floor->id)
    ->assertOk()
    ->call('destroy', $floor->id)
    ->assertOk();

    expect(Floor::where('id', $floor->id)->doesntExist())->toBeTrue();
});
