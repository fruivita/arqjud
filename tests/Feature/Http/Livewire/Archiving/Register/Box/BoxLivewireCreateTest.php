<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireCreate;
use App\Models\Box;
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

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a box record without being authenticated', function () {
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
test('site_id is optional', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('site_id', '')
    ->call('store')
    ->assertHasNoErrors(['site_id']);
});

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

test('building_id is optional', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('building_id', '')
    ->call('store')
    ->assertHasNoErrors(['building_id']);
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

test('floor_id is optional', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('floor_id', '')
    ->call('store')
    ->assertHasNoErrors(['floor_id']);
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

test('box.room_id is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.room_id', '')
    ->call('store')
    ->assertHasErrors(['box.room_id' => 'required']);
});

test('box.room_id must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.room_id', 'foo')
    ->call('store')
    ->assertHasErrors(['box.room_id' => 'integer']);
});

test('box.room_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.room_id', 10)
    ->call('store')
    ->assertHasErrors(['box.room_id' => 'exists']);
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

test('box.year is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.year', '')
    ->call('store')
    ->assertHasErrors(['box.year' => 'required']);
});

test('box.year must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.year', 'foo')
    ->call('store')
    ->assertHasErrors(['box.year' => 'integer']);
});

test('box.year must be between 1900 and the current year', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.year', 1899)
    ->call('store')
    ->assertHasErrors(['box.year' => 'between'])
    ->set('box.year', now()->addYear()->format('Y'))
    ->call('store')
    ->assertHasErrors(['box.year' => 'between']);
});

test('box.year is validated in real time', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.year', 1900)
    ->assertHasNoErrors()
    ->set('box.year', 1889)
    ->assertHasErrors(['box.year' => 'between']);
});

test('box.number is required', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.number', '')
    ->call('store')
    ->assertHasErrors(['box.number' => 'required']);
});

test('box.number must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.number', 'foo')
    ->call('store')
    ->assertHasErrors(['box.number' => 'integer']);
});

test('box.number must be greater then 1', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.number', 0)
    ->call('store')
    ->assertHasErrors(['box.number' => 'min']);
});

test('box.number and year must be unique', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Box::factory()->create(['year' => 2020, 'number' => 10]);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.year', 2020)
    ->set('box.number', 10)
    ->call('store')
    ->assertHasErrors(['box.number' => 'unique']);
});

test('box.stand is optional', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.stand', '')
    ->call('store')
    ->assertHasNoErrors(['box.stand']);
});

test('box.stand must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.stand', 'foo')
    ->call('store')
    ->assertHasErrors(['box.stand' => 'integer']);
});

test('box.stand must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.stand', 0)
    ->call('store')
    ->assertHasErrors(['box.stand' => 'between'])
    ->set('box.stand', 1001)
    ->call('store')
    ->assertHasErrors(['box.stand' => 'between']);
});

test('box.shelf is optional', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.shelf', '')
    ->call('store')
    ->assertHasNoErrors(['box.shelf']);
});

test('box.shelf must be an integer', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.shelf', 'foo')
    ->call('store')
    ->assertHasErrors(['box.shelf' => 'integer']);
});

test('box.shelf must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.shelf', 0)
    ->call('store')
    ->assertHasErrors(['box.shelf' => 'between'])
    ->set('box.shelf', 1001)
    ->call('store')
    ->assertHasErrors(['box.shelf' => 'between']);
});

test('box.description is optional', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.description', '')
    ->call('store')
    ->assertHasNoErrors(['box.description']);
});

test('box.description must be a string', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['box.description' => 'string']);
});

test('box.description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BoxCreate->value);

    Livewire::test(BoxLivewireCreate::class)
    ->set('box.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['box.description' => 'max']);
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
    ->set('box.year', 2000)
    ->set('box.number', 10)
    ->set('box.stand', 15)
    ->set('box.shelf', 5)
    ->set('volumes', 2)
    ->set('box.room_id', $room->id)
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
    ->set('box.year', 2020)
    ->assertSet('box.number', 11)
    ->set('box.year', 2021)
    ->assertSet('box.number', 1);
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
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.stand', 15)
    ->set('box.shelf', 5)
    ->set('volumes', 1)
    ->set('box.room_id', $room->id)
    ->call('store')
    ->assertOk();

    expect(Box::count())->toBe(1);
});

test('without permission to create box volumes, volumes property is ignored and only one volume is created for the box.', function () {
    grantPermission(PermissionType::BoxCreate->value);

    $room = Room::factory()->create();

    expect(Box::count())->toBe(0);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 1)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.stand', 15)
    ->set('box.shelf', 5)
    ->set('volumes', 20)
    ->set('box.room_id', $room->id)
    ->call('store')
    ->assertOk();

    $box = Box::with('volumes')->first();

    expect($box->volumes)->toHaveCount(1)
    ->and($box->volumes->first()->number)->toBe(1);
});

test('creates the amount of boxes defined', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxCreateMany->value);

    $room = Room::factory()->create();

    expect(Box::count())->toBe(0);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 10)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.stand', 15)
    ->set('box.shelf', 5)
    ->set('box.description', 'foo bar')
    ->set('volumes', 1)
    ->set('box.room_id', $room->id)
    ->call('store')
    ->assertOk();

    $boxes = Box::withCount('volumes')
            ->with('room')
            ->get();

    $box = $boxes->random();

    $first = $boxes->first();

    $last = $boxes->last();

    expect(Box::count())->toBe(10)
    ->and($box->year)->toBe(2000)
    ->and($first->number)->toBe(55)
    ->and($last->number)->toBe(64)
    ->and($box->stand)->toBe(15)
    ->and($box->shelf)->toBe(5)
    ->and($box->description)->toBe('foo bar')
    ->and($box->volumes_count)->toBe(1)
    ->and($box->room->id)->toBe($room->id);
});

test('creates the amount of volumes defined', function () {
    grantPermission(PermissionType::BoxCreate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    $room = Room::factory()->create();

    expect(Box::count())->toBe(0);

    Livewire::test(BoxLivewireCreate::class)
    ->set('amount', 1)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.stand', 15)
    ->set('box.shelf', 5)
    ->set('volumes', 20)
    ->set('box.room_id', $room->id)
    ->call('store')
    ->assertOk();

    $box = Box::with('volumes')->first();

    expect($box->volumes)->toHaveCount(20)
    ->and($box->volumes->first()->number)->toBe(1)
    ->and($box->volumes->last()->number)->toBe(20);
});
