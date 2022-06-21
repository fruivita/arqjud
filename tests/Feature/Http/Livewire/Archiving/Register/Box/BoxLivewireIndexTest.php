<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireIndex;
use App\Models\Box;
use App\Models\BoxVolume;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
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
test('cannot list boxes without being authenticated', function () {
    logout();

    get(route('archiving.register.box.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access boxes listing route', function () {
    get(route('archiving.register.box.index'))
    ->assertForbidden();
});

test('cannot render boxes listing component without specific permission', function () {
    Livewire::test(BoxLivewireIndex::class)->assertForbidden();
});

test('cannot set the box record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Livewire::test(BoxLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->box->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the box record which will be deleted if it has volumes', function () {
    grantPermission(PermissionType::BoxViewAny->value);
    grantPermission(PermissionType::BoxDelete->value);

    BoxVolume::factory()->for($this->box, 'box')->create();

    Livewire::test(BoxLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->box->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a box record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::BoxViewAny->value);
    grantPermission(PermissionType::BoxDelete->value);

    $component = Livewire::test(BoxLivewireIndex::class)
    ->call('setToDelete', $this->box->id)
    ->assertOk();

    revokePermission(PermissionType::BoxDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Box::where('id', $this->box->id)->exists())->toBeTrue();
});

test('cannot delete a box record if it has volumes', function () {
    grantPermission(PermissionType::BoxViewAny->value);
    grantPermission(PermissionType::BoxDelete->value);

    $component = Livewire::test(BoxLivewireIndex::class)
    ->call('setToDelete', $this->box->id)
    ->assertOk();

    BoxVolume::factory()->for($this->box, 'box')->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Box::where('id', $this->box->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Livewire::test(BoxLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('searchable term must be a string', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Livewire::test(BoxLivewireIndex::class)
    ->set('term', ['foo'])
    ->assertHasErrors(['term' => 'string']);
});

test('searchable term must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Livewire::test(BoxLivewireIndex::class)
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

test('searchable term is validated in real time', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Livewire::test(BoxLivewireIndex::class)
    ->set('term', Str::random(50))
    ->assertHasNoErrors()
    ->set('term', Str::random(51))
    ->assertHasErrors(['term' => 'max']);
});

// Happy path
test('pagination returns the amount of boxes expected', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    Box::factory(120)->create();

    Livewire::test(BoxLivewireIndex::class)
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
    grantPermission(PermissionType::BoxViewAny->value);

    Livewire::test(BoxLivewireIndex::class)
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

test('list boxes with specific permission', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    get(route('archiving.register.box.index'))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    $this->box->delete();

    Box::factory()->create([
        'number' => '100',
        'year' => '2015',
    ]);

    Box::factory()->create([
        'number' => '120152', // contains 2015
        'year' => '2020',
    ]);

    Box::factory()->create([
        'number' => '200',
        'year' => '2020',
    ]);

    Livewire::test(BoxLivewireIndex::class)
    ->set('term', '120152')
    ->assertCount('boxes', 1)
    ->set('term', '2015')
    ->assertCount('boxes', 2)
    ->set('term', '2020')
    ->assertCount('boxes', 2)
    ->set('term', '')
    ->assertCount('boxes', 3);
});

test('emits feedback event when deleting a box record', function () {
    grantPermission(PermissionType::BoxViewAny->value);
    grantPermission(PermissionType::BoxDelete->value);

    Livewire::test(BoxLivewireIndex::class)
    ->call('setToDelete', $this->box->id)
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

test('defines the box record that will be deleted with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::BoxViewAny->value);
    grantPermission(PermissionType::BoxDelete->value);

    Livewire::test(BoxLivewireIndex::class)
    ->call('setToDelete', $this->box->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->box->id);
});

test('delete a box record with specific permission if it has no shelves', function () {
    grantPermission(PermissionType::BoxViewAny->value);
    grantPermission(PermissionType::BoxDelete->value);

    expect(Box::where('id', $this->box->id)->exists())->toBeTrue();

    Livewire::test(BoxLivewireIndex::class)
    ->call('setToDelete', $this->box->id)
    ->assertOk()
    ->call('destroy', $this->box->id)
    ->assertOk();

    expect(Box::where('id', $this->box->id)->doesntExist())->toBeTrue();
});

test('BoxLivewireIndex uses the withsorting trait', function () {
    expect(
        collect(class_uses(BoxLivewireIndex::class))
        ->contains(\App\Http\Livewire\Traits\WithSorting::class)
    )->toBeTrue();
});
