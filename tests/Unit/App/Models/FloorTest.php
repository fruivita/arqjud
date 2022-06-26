<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create floors in duplicate, that is, with the same numbers/alias and building', function () {
    $building = Building::factory()->create();

    expect(
        fn () => Floor::factory(2)->create([
            'number' => 100,
            'building_id' => $building->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Floor::factory(2)->create([
            'alias' => 100,
            'building_id' => $building->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create floor with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Floor::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number',      -2147483649,      'Out of range'],             // min -2147483648
    ['number',      2147483648,       'Out of range'],             // max 2147483647
    ['number',     'foo',             'Incorrect integer value'],  // not convertible to integer
    ['number',      null,             'cannot be null'],           // required
    ['alias',       Str::random(101), 'Data too long for column'], // maximum 100 characters
    ['alias',       null,             'cannot be null'],           // required
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Floor::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['building_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['building_id', null, 'cannot be null'],                   // required
]);

// Happy path
test('create many floors', function () {
    Floor::factory(30)->create();

    expect(Floor::count())->toBe(30);
});

test('fields in their minimum size are accepted', function () {
    Floor::factory()->create(['number' => -2147483648]);

    expect(Floor::count())->toBe(1);
});

test('fields in their maximum size are accepted', function () {
    Floor::factory()->create([
        'number' => 2147483647,
        'alias' => Str::random(100),
        'description' => Str::random(255),
    ]);

    expect(Floor::count())->toBe(1);
});

test('zero is a valid value for the floor number', function () {
    Floor::factory()->create(['number' => 0]);

    $floor = Floor::first();

    expect($floor->number)->toBe(0);
});

test('optional fields are set', function () {
    Floor::factory()->create(['description' => null]);

    expect(Floor::count())->toBe(1);
});

test('createRoom save the room as a child of the floor and create a default stand and shelf', function () {
    $room = new Room();
    $room->number = 10;
    $room->description = 'foo';

    $floor = Floor::factory()->create();

    $floor->createRoom($room);

    $floor->load('rooms.stands.shelves');

    $room = $floor->rooms->first();

    $stand = $room->stands->first();

    $shelf = $stand->shelves->first();

    expect($room->number)->toBe('10')
    ->and($room->description)->toBe('foo')
    ->and($room->floor_id)->toBe($floor->id)
    ->and($stand->number)->toBe(0)
    ->and($stand->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'))
    ->and($stand->room_id)->toBe($room->id)
    ->and($shelf->number)->toBe(0)
    ->and($shelf->stand_id)->toBe($stand->id)
    ->and($shelf->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
});

test('one floor belongs to one building', function () {
    $building = Building::factory()->create();

    $floor = Floor::factory()
        ->for($building, 'building')
        ->create();

    $floor->load(['building']);

    expect($floor->building)->toBeInstanceOf(Building::class);
});

test('one floor has many rooms', function () {
    Floor::factory()
        ->has(Room::factory(3), 'rooms')
        ->create();

    $floor = Floor::with('rooms')->first();

    expect($floor->rooms)->toHaveCount(3);
});

test('parentLinks returns only edit parents routes sorted from most distant to closest relationship if root is false', function () {
    Floor::factory()->create();

    $floor = Floor::hierarchy()->first();

    expect($floor->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.edit', $floor->site_id),
        __('Building') => route('archiving.register.building.edit', $floor->building_id),
    ]);
});

test('parentLinks returns edit parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    Floor::factory()->create();

    $floor = Floor::hierarchy()->first();

    expect($floor->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.edit', $floor->site_id),
        __('Building') => route('archiving.register.building.edit', $floor->building_id),
        __('Floor') => route('archiving.register.floor.edit', $floor->id),
    ]);
});

test('parentLinks returns links based on hierarchical data present in the model or, if not, fetches them from the database', function () {
    Floor::factory()->create();

    $floor = Floor::first();
    $floor->load('building');

    expect($floor->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.edit', $floor->building->site_id),
        __('Building') => route('archiving.register.building.edit', $floor->building_id),
    ]);
});

test('hierarchy returns all floors with the respective building, site id and number/name and the number of rooms of each', function () {
    Floor::factory()->create(['number' => 10]);
    Floor::factory()->has(Room::factory(1), 'rooms')->create(['number' => 20]);
    Floor::factory()->has(Room::factory(2), 'rooms')->create(['number' => 30]);

    $all = Floor::hierarchy()->get();

    $floor_10 = $all->firstWhere('number', 10);
    $floor_20 = $all->firstWhere('number', 20);
    $floor_30 = $all->firstWhere('number', 30);

    expect($all)->toHaveCount(3)
    ->and(empty($floor_10->site_id))->toBeFalse()
    ->and(empty($floor_10->site_name))->toBeFalse()
    ->and(empty($floor_10->building_id))->toBeFalse()
    ->and(empty($floor_10->building_name))->toBeFalse()
    ->and($floor_10->rooms_count)->toBe(0)
    ->and($floor_20->rooms_count)->toBe(1)
    ->and($floor_30->rooms_count)->toBe(2);
});
