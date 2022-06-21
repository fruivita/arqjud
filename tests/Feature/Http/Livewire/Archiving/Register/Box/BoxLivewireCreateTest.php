<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireCreate;
use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Shelf;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
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
test('cannot create a box record without being authenticated', function () {
    logout();

    get(route('archiving.register.box.create', $this->shelf->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access box record creation route', function () {
    get(route('archiving.register.box.create', $this->shelf->id))
    ->assertForbidden();
});

test('cannot render box record creation component without specific permission', function () {
    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->assertForbidden();
});

test('cannot set the box record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $box = Box::factory()->create();

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->assertOk()
    ->call('markToDelete', $box->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the box record which will be deleted if it has volumes', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->create();

    BoxVolume::factory()->for($box, 'box')->create();

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->assertOk()
    ->call('markToDelete', $box->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a box record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->create();

    $component = Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->call('markToDelete', $box->id)
    ->assertOk();

    revokePermission(PermissionType::BoxDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Box::where('id', $box->id)->exists())->toBeTrue();
});

test('cannot delete a box record if it has volumes', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->create();

    $component = Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->call('markToDelete', $box->id)
    ->assertOk();

    BoxVolume::factory()->for($box, 'box')->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Box::where('id', $box->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('amount is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', '')
    ->call('store')
    ->assertHasErrors(['amount' => 'required']);
});

test('amount must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', 'foo')
    ->call('store')
    ->assertHasErrors(['amount' => 'integer']);
});

test('amount must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', 0)
    ->call('store')
    ->assertHasErrors(['amount' => 'between'])
    ->set('amount', 1001)
    ->call('store')
    ->assertHasErrors(['amount' => 'between']);
});

test('year is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.year', '')
    ->call('store')
    ->assertHasErrors(['box.year' => 'required']);
});

test('year must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.year', 'foo')
    ->call('store')
    ->assertHasErrors(['box.year' => 'integer']);
});

test('year must be between 1900 and the current year', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.year', 1899)
    ->call('store')
    ->assertHasErrors(['box.year' => 'between'])
    ->set('box.year', now()->addYear()->format('Y'))
    ->call('store')
    ->assertHasErrors(['box.year' => 'between']);
});

test('year is validated in real time', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.year', 1900)
    ->assertHasNoErrors()
    ->set('box.year', 1889)
    ->assertHasErrors(['box.year' => 'between']);
});

test('number is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.number', '')
    ->call('store')
    ->assertHasErrors(['box.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.number', 'foo')
    ->call('store')
    ->assertHasErrors(['box.number' => 'integer']);
});

test('number must be greater then 1', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.number', 0)
    ->call('store')
    ->assertHasErrors(['box.number' => 'min']);
});

test('number and year must be unique', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Box::factory()->create(['year' => 2020, 'number' => 10]);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.year', 2020)
    ->set('box.number', 10)
    ->call('store')
    ->assertHasErrors(['box.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.description', '')
    ->call('store')
    ->assertHasNoErrors(['box.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['box.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['box.description' => 'max']);
});

test('volumes is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('volumes', '')
    ->call('store')
    ->assertHasErrors(['volumes' => 'required']);
});

test('volumes must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('volumes', 'foo')
    ->call('store')
    ->assertHasErrors(['volumes' => 'integer']);
});

test('volumes must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('volumes', 0)
    ->call('store')
    ->assertHasErrors(['volumes' => 'between'])
    ->set('volumes', 1001)
    ->call('store')
    ->assertHasErrors(['volumes' => 'between']);
});

// Happy path
test('pagination returns the amount of expected boxes records', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Box::factory(120)->for($this->shelf, 'shelf')->create();

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
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
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
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

test('renders box record creation component with specific permission', function () {
    grantPermission(PermissionType::BoxCreate->value);

    get(route('archiving.register.box.create', $this->shelf->id))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireCreate::class);
});

test('emits feedback event when creates a box record', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', 1)
    ->set('box.year', 2000)
    ->set('box.number', 10)
    ->set('volumes', 2)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a box record', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->create();

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->call('markToDelete', $box->id)
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

test('suggests the next box number (max number + 1) according to the selected year', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Box::factory()->create(['year' => 2020, 'number' => 21]);
    Box::factory()->create(['year' => 2020, 'number' => 111]);
    Box::factory()->create(['year' => 2020, 'number' => 20]);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.year', 2020)
    ->assertSet('box.number', 112)
    ->set('box.year', 2021)
    ->assertSet('box.number', 1);
});

test('default quantity for create boxes is 1', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->assertSet('amount', 1);
});

test('default quantity for volumes boxes is 1', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->assertSet('volumes', 1);
});

test('without permission to create multiples, amount is ignored and only one box is created', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', 10)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('volumes', 1)
    ->call('store')
    ->assertOk();

    expect(Box::count())->toBe(1);
});

test('without permission to create box volumes, volumes property is ignored and only one volume is created for the box', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', 1)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('volumes', 20)
    ->call('store')
    ->assertOk();

    $box = Box::with('volumes')->first();

    expect($box->volumes)->toHaveCount(1)
    ->and($box->volumes->first()->number)->toBe(1);
});

test('creates the amount of boxes defined', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxCreateMany->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', 10)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.description', 'foo bar')
    ->set('volumes', 1)
    ->call('store')
    ->assertOk();

    $boxes = Box::withCount('volumes')
            ->with('shelf')
            ->get();

    $box = $boxes->random();

    $first = $boxes->first();

    $last = $boxes->last();

    expect(Box::count())->toBe(10)
    ->and($box->year)->toBe(2000)
    ->and($first->number)->toBe(55)
    ->and($last->number)->toBe(64)
    ->and($box->description)->toBe('foo bar')
    ->and($box->volumes_count)->toBe(1)
    ->and($box->shelf->id)->toBe($this->shelf->id);
});

test('creates the amount of volumes defined', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('amount', 1)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('volumes', 20)
    ->call('store')
    ->assertOk();

    $box = Box::with('volumes')->first();

    expect($box->volumes)->toHaveCount(20)
    ->and($box->volumes->first()->number)->toBe(1)
    ->and($box->volumes->last()->number)->toBe(20);
});

test('reset to a blank model after the box is created', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $blank = new Box();

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->call('store')
    ->assertOk()
    ->assertSet('box', $blank);
});

test('defines the box record that will be deleted with specific permission if it has no volumes', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->create();

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->call('markToDelete', $box->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $box->id);
});

test('delete a box record with specific permission if it has no volumes', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->create();

    Livewire::test(BoxLivewireCreate::class, ['id' => $this->shelf->id])
    ->call('markToDelete', $box->id)
    ->assertOk()
    ->call('destroy', $box->id)
    ->assertOk();

    expect(Box::where('id', $box->id)->doesntExist())->toBeTrue();
});

test('BoxLivewireCreate uses the withsorting trait', function () {
    expect(
        collect(class_uses(BoxLivewireCreate::class))
        ->contains(\App\Http\Livewire\Traits\WithSorting::class)
    )->toBeTrue();
});
