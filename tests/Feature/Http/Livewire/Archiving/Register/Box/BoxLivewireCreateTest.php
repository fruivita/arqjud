<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireCreate;
use App\Models\Box;
use App\Models\BoxVolume;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('it is not possible to create a box record without being authenticated', function () {
    logout();

    get(route('archiving.register.box.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access box record creation route', function () {
    get(route('archiving.register.box.create'))
    ->assertForbidden();
});

test('cannot render box record creation component without specific permission', function () {
    Livewire::test(BoxLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('site_id must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('site_id', 'foo')
    ->call('store')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('site_id', 10)
    ->call('store')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $site = Site::factory()->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('building_id', 'foo')
    ->call('store')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('building_id', 10)
    ->call('store')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $building = Building::factory()->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('floor_id', 'foo')
    ->call('store')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('floor_id', 10)
    ->call('store')
    ->assertHasErrors(['floor_id' => 'exists']);
});

test('floor_id is validated in real time', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $floor = Floor::factory()->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('floor_id', $floor->id)
    ->assertHasNoErrors()
    ->set('floor_id', 'foo')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('room_id is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('room_id', '')
    ->call('store')
    ->assertHasErrors(['room_id' => 'required']);
});

test('room_id must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('room_id', 'foo')
    ->call('store')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('room_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('room_id', 10)
    ->call('store')
    ->assertHasErrors(['room_id' => 'exists']);
});

test('room_id is validated in real time', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $room = Room::factory()->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('room_id', $room->id)
    ->assertHasNoErrors()
    ->set('room_id', 'foo')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('amount is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', '')
    ->call('store')
    ->assertHasErrors(['amount' => 'required']);
});

test('amount must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 'foo')
    ->call('store')
    ->assertHasErrors(['amount' => 'integer']);
});

test('amount must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 0)
    ->call('store')
    ->assertHasErrors(['amount' => 'between'])
    ->set('amount', 1001)
    ->call('store')
    ->assertHasErrors(['amount' => 'between']);
});

test('year is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('year', '')
    ->call('store')
    ->assertHasErrors(['year' => 'required']);
});

test('year must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('year', 'foo')
    ->call('store')
    ->assertHasErrors(['year' => 'integer']);
});

test('year must be between 1900 and the current year', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('year', 1899)
    ->call('store')
    ->assertHasErrors(['year' => 'between'])
    ->set('year', now()->addYear()->format('Y'))
    ->call('store')
    ->assertHasErrors(['year' => 'between']);
});

test('year is validated in real time', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('year', 1900)
    ->assertHasNoErrors()
    ->set('year', 1889)
    ->assertHasErrors(['year' => 'between']);
});

test('number is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('number', '')
    ->call('store')
    ->assertHasErrors(['number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('number', 'foo')
    ->call('store')
    ->assertHasErrors(['number' => 'integer']);
});

test('number must be greater then 1', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('number', 0)
    ->call('store')
    ->assertHasErrors(['number' => 'min']);
});

test('number and year must be unique', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Box::factory()->create(['year' => 2020, 'number' => 10]);

    Livewire::test(BoxLivewireCreate::class)
    ->set('year', 2020)
    ->set('number', 10)
    ->call('store')
    ->assertHasErrors(['number' => 'unique']);
});

test('stand must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('stand', 'foo')
    ->call('store')
    ->assertHasErrors(['stand' => 'integer']);
});

test('stand must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('stand', 0)
    ->call('store')
    ->assertHasErrors(['stand' => 'between'])
    ->set('stand', 1001)
    ->call('store')
    ->assertHasErrors(['stand' => 'between']);
});

test('shelf must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('shelf', 'foo')
    ->call('store')
    ->assertHasErrors(['shelf' => 'integer']);
});

test('shelf must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('shelf', 0)
    ->call('store')
    ->assertHasErrors(['shelf' => 'between'])
    ->set('shelf', 1001)
    ->call('store')
    ->assertHasErrors(['shelf' => 'between']);
});

test('volumes is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('volumes', '')
    ->call('store')
    ->assertHasErrors(['volumes' => 'required']);
});

test('volumes must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('volumes', 'foo')
    ->call('store')
    ->assertHasErrors(['volumes' => 'integer']);
});

test('volumes must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('volumes', 0)
    ->call('store')
    ->assertHasErrors(['volumes' => 'between'])
    ->set('volumes', 1001)
    ->call('store')
    ->assertHasErrors(['volumes' => 'between']);
});

// Happy path
test('renders box record creation component with specific permission', function () {
    grantPermission(PermissionType::BoxCreate->value);

    get(route('archiving.register.box.create'))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireCreate::class);
});

test('emits feedback event when creates a box record', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $room = Room::factory()->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 1)
    ->set('year', 2000)
    ->set('number', 10)
    ->set('stand', 15)
    ->set('shelf', 5)
    ->set('volumes', 2)
    ->set('room_id', $room->id)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in box creation', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Site::factory(10)->create();

    Livewire::test(BoxLivewireCreate::class)
    ->assertCount('sites', 10);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('floors are available by selecting a building', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $building = Building::factory()
    ->has(Floor::factory(10), 'floors')
    ->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('building_id', $building->id)
    ->assertCount('floors', 10);
});

test('rooms are available by selecting a floor', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $floor = Floor::factory()
    ->has(Room::factory(10), 'rooms')
    ->create();

    Livewire::test(BoxLivewireCreate::class)
    ->set('floor_id', $floor->id)
    ->assertCount('rooms', 10);
});

test('suggests the next box number (max number + 1) according to the selected year', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Box::factory()->create(['year' => 2020, 'number' => 10]);

    Livewire::test(BoxLivewireCreate::class)
    ->set('year', 2020)
    ->assertSet('number', 11)
    ->set('year', 2021)
    ->assertSet('number', 1);
});

test('default quantity for create boxes is 1', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->assertSet('amount', 1);
});

test('default quantity for volumes boxes is 1', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->assertSet('volumes', 1);
});

test('without permission to create multiples, amount is ignored and only one box is created.', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $room = Room::factory()->create();

    expect(Box::count())->toBe(0);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 10)
    ->set('year', 2000)
    ->set('number', 55)
    ->set('stand', 15)
    ->set('shelf', 5)
    ->set('volumes', 2)
    ->set('room_id', $room->id)
    ->call('store')
    ->assertOk();

    expect(Box::count())->toBe(1);
});

// test('without permission to create volumes, volumes property is ignored and only one volume is created.', function () {
//     grantPermission(PermissionType::BoxCreate->value);

//     $room = Room::factory()->create();

//     expect(Box::count())->toBe(0);

//     Livewire::test(BoxLivewireCreate::class)
//     ->set('amount', 10)
//     ->set('year', 2000)
//     ->set('number', 55)
//     ->set('stand', 15)
//     ->set('shelf', 5)
//     ->set('volumes', 2)
//     ->set('room_id', $room->id)
//     ->call('store')
//     ->assertOk();

//     expect(Box::count())->toBe(1);
// });

test('creates the amount of boxes defined', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxCreateMany->value);

    $room = Room::factory()->create();

    expect(Box::count())->toBe(0);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 10)
    ->set('year', 2000)
    ->set('number', 55)
    ->set('stand', 15)
    ->set('shelf', 5)
    ->set('volumes', 2)
    ->set('room_id', $room->id)
    ->call('store')
    ->assertOk();

    expect(Box::count())->toBe(10);
});

test('creates the amount of volumes defined', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxCreateMany->value);

    $room = Room::factory()->create();

    expect(Box::count())->toBe(0);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 1)
    ->set('year', 2000)
    ->set('number', 55)
    ->set('stand', 15)
    ->set('shelf', 5)
    ->set('volumes', 20)
    ->set('room_id', $room->id)
    ->call('store')
    ->assertOk();

    $box = Box::with('volumes')->first();

    expect($box->volumes)->toHaveCount(20)
    ->and($box->volumes->first()->number)->toBe(1)
    ->and($box->volumes->last()->number)->toBe(20);
});
