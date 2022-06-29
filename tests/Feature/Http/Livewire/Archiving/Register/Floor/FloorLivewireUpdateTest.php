<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireUpdate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->floor = Floor::factory()->create(['number' => 2]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a floor record without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.edit', $this->floor->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor record edit route', function () {
    get(route('archiving.register.floor.edit', $this->floor->id))
    ->assertForbidden();
});

test('cannot render floor record edit component without specific permission', function () {
    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->assertForbidden();
});

test('cannot update floor if edit mode is disabled', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('cannot update floor without specific permission', function () {
    grantPermission(PermissionType::FloorView->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.number', '')
    ->call('update')
    ->assertHasErrors(['floor.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.number', ['foo'])
    ->call('update')
    ->assertHasErrors(['floor.number' => 'integer']);
});

test('number must be between -100 and 300', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.number', -101)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'between'])
    ->set('floor.number', 301)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'between']);
});

test('number and building_id must be unique', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();
    Floor::factory()->create(['number' => 99, 'building_id' => $building->id]);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.number', 99)
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'unique']);
});

test('alias is optional', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.alias', '')
    ->call('update')
    ->assertHasNoErrors(['floor.alias']);
});

test('alias must be a string', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.alias', ['foo'])
    ->call('update')
    ->assertHasErrors(['floor.alias' => 'string']);
});

test('alias must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.alias', Str::random(101))
    ->call('update')
    ->assertHasErrors(['floor.alias' => 'max']);
});

test('alias and building_id must be unique', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();
    Floor::factory()->create(['alias' => '99', 'building_id' => $building->id]);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.alias', '99')
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertHasErrors(['floor.alias' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.description', '')
    ->call('update')
    ->assertHasNoErrors(['floor.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['floor.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['floor.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('site_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.building_id', '')
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.building_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'exists']);
});

// Happy path
test('pagination returns the amount of expected rooms records', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Room::factory(30)->for($this->floor, 'floor')->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('per_page', 25)
    ->assertCount('rooms', 25);
});

test('renders edit floor record component with view or update permission', function ($permission) {
    grantPermission($permission);

    get(route('archiving.register.floor.edit', $this->floor->id))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireUpdate::class);
})->with([
    PermissionType::FloorView->value,
    PermissionType::FloorUpdate->value
]);

test('emits feedback event when update a floor record', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.number', 1)
    ->set('floor.alias', '1º')
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a room record', function () {
    grantPermission(PermissionType::FloorUpdate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->for($this->floor, 'floor')->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->call('setToDelete', $room->id)
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

test('sites are available for selection in floor update', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Site::factory(10)->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->assertCount('sites', 11);
});

test('sets the selected building to null and makes new buildings available when selecting a site', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $site = Site::factory()->has(Building::factory(10), 'buildings')->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('site_id', $site->id)
    ->assertSet('floor.building_id', null)
    ->assertCount('buildings', 10);
});

test('sites and buildings are pre-defined according to the edit floor', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $this->floor->load('building.site');

    Building::factory(2)->for($this->floor->building->site, 'site')->create();
    Site::factory(15)->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->assertCount('sites', 16)
    ->assertSet('site_id', $this->floor->building->site->id)
    ->assertCount('buildings', 3);
});

test('update a floor record with specific permission', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->set('modo_edicao', true)
    ->set('floor.number', 99)
    ->set('floor.alias', '99º')
    ->set('floor.description', 'foo bar')
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->floor->refresh();

    expect($this->floor->number)->toBe(99)
    ->and($this->floor->alias)->toBe('99º')
    ->and($this->floor->description)->toBe('foo bar')
    ->and($this->floor->building_id)->toBe($building->id);
});

test('colunas ocultáveis estão pré-definidas', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['id' => $this->floor->id])
    ->assertSet('colunas', [
        'sala',
        'qtd_estantes',
        'acoes',
    ]);
});

test('FloorLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(FloorLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\ConverteStringVaziaEmNull::class,
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
