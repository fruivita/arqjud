<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireCreate;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->room = Room::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a stand record without being authenticated', function () {
    logout();

    get(route('archiving.register.stand.create', $this->room->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access stand record creation route', function () {
    get(route('archiving.register.stand.create', $this->room->id))
    ->assertForbidden();
});

test('cannot render stand record creation component without specific permission', function () {
    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->assertForbidden();
});

test('cannot set the stand record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::StandCreate->value);

    $stand = Stand::factory()->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->assertOk()
    ->call('markToDelete', $stand->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the stand record which will be deleted if it has shelves', function () {
    grantPermission(PermissionType::StandCreate->value);
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();

    Shelf::factory()->for($stand, 'stand')->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->assertOk()
    ->call('markToDelete', $stand->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a stand record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::StandCreate->value);
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();

    $component = Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->call('markToDelete', $stand->id)
    ->assertOk();

    revokePermission(PermissionType::StandDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Stand::where('id', $stand->id)->exists())->toBeTrue();
});

test('cannot delete a stand record if it has shelves', function () {
    grantPermission(PermissionType::StandCreate->value);
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();

    $component = Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->call('markToDelete', $stand->id)
    ->assertOk();

    Shelf::factory()->for($stand, 'stand')->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Stand::where('id', $stand->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('number is required', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', '')
    ->call('store')
    ->assertHasErrors(['stand.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['stand.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 0)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'between'])
    ->set('stand.number', 100001)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'between']);
});

test('number and room_id must be unique', function () {
    grantPermission(PermissionType::StandCreate->value);

    Stand::factory()->create(['number' => 99, 'room_id' => $this->room->id]);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 99)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.description', '')
    ->call('store')
    ->assertHasNoErrors(['stand.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['stand.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['stand.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected stand records', function () {
    grantPermission(PermissionType::StandCreate->value);

    Stand::factory(120)->for($this->room, 'room')->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->assertCount('stands', 10)
    ->set('per_page', 10)
    ->assertCount('stands', 10)
    ->set('per_page', 25)
    ->assertCount('stands', 25)
    ->set('per_page', 50)
    ->assertCount('stands', 50)
    ->set('per_page', 100)
    ->assertCount('stands', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
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

test('renders stand record creation component with specific permission', function () {
    grantPermission(PermissionType::StandCreate->value);

    get(route('archiving.register.stand.create', $this->room->id))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireCreate::class);
});

test('emits feedback event when creates a stand record', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 1)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a stand record', function () {
    grantPermission(PermissionType::StandCreate->value);
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->call('markToDelete', $stand->id)
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

test('creates a stand record with specific permission', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 99)
    ->set('stand.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $stand = Stand::with('room')->first();

    expect($stand->number)->toBe(99)
    ->and($stand->description)->toBe('foo bar')
    ->and($stand->room->id)->toBe($this->room->id);
});

test('when creating a stand, a default shelf is also created', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 99)
    ->set('stand.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $stand = Stand::with('shelves')->first();
    $shelf = $stand->shelves()->first();

    expect($stand->number)->toBe(99)
    ->and($stand->description)->toBe('foo bar')
    ->and($stand->room_id)->toBe($this->room->id)
    ->and($shelf->number)->toBe(0)
    ->and($shelf->stand_id)->toBe($stand->id)
    ->and($shelf->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
});

test('reset to a blank model after the stand is created', function () {
    grantPermission(PermissionType::StandCreate->value);

    $blank = new Stand();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->set('stand.number', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('stand', $blank);
});

test('defines the stand record that will be deleted with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::StandCreate->value);
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->call('markToDelete', $stand->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $stand->id);
});

test('delete a stand record with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::StandCreate->value);
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();

    Livewire::test(StandLivewireCreate::class, ['id' => $this->room->id])
    ->call('markToDelete', $stand->id)
    ->assertOk()
    ->call('destroy', $stand->id)
    ->assertOk();

    expect(Stand::where('id', $stand->id)->doesntExist())->toBeTrue();
});

test('StandLivewireCreate uses the withsorting trait', function () {
    expect(
        collect(class_uses(StandLivewireCreate::class))
        ->contains(\App\Http\Livewire\Traits\WithSorting::class)
    )->toBeTrue();
});
