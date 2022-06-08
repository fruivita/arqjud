<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireCreate;
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

    $this->room = Room::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a stand record without being authenticated', function () {
    logout();

    get(route('archiving.register.stand.create', $this->room))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access stand record creation route', function () {
    get(route('archiving.register.stand.create', $this->room))
    ->assertForbidden();
});

test('cannot render stand record creation component without specific permission', function () {
    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.number', '')
    ->call('store')
    ->assertHasErrors(['stand.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['stand.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.number', 0)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'between'])
    ->set('stand.number', 100001)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'between']);
});

test('number and room_id must be unique', function () {
    grantPermission(PermissionType::StandCreate->value);

    Stand::factory()->create(['number' => 1, 'room_id' => $this->room->id]);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.number', 1)
    ->call('store')
    ->assertHasErrors(['stand.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.description', '')
    ->call('store')
    ->assertHasNoErrors(['stand.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['stand.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['stand.description' => 'max']);
});

// Happy path
test('renders stand record creation component with specific permission', function () {
    grantPermission(PermissionType::StandCreate->value);

    get(route('archiving.register.stand.create', $this->room))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireCreate::class);
});

test('emits feedback event when creates a stand record', function () {
    grantPermission(PermissionType::StandCreate->value);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.number', 1)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('creates a stand record with specific permission', function () {
    grantPermission(PermissionType::StandCreate->value);

    expect(Stand::count())->toBe(0);

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.number', 1)
    ->set('stand.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $stand = Stand::with('room')->first();

    expect($stand->number)->toBe(1)
    ->and($stand->description)->toBe('foo bar')
    ->and($stand->room->id)->toBe($this->room->id);
});

test('reset to a blank model after the stand is created', function () {
    grantPermission(PermissionType::StandCreate->value);

    $blank = new Stand();

    Livewire::test(StandLivewireCreate::class, ['room' => $this->room])
    ->set('stand.number', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('stand', $blank);
});
