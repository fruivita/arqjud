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
test('throws exception when trying to create floors in duplicate, that is, with the same numbers and building', function () {
    $building = Building::factory()->create();

    expect(
        fn () => Floor::factory(2)->create([
            'number' => 100,
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

test('returns the floors using the default sort scope defined', function () {
    $first = 100;
    $second = 200;
    $third = 300;

    Floor::factory()->create(['number' => $third]);
    Floor::factory()->create(['number' => $first]);
    Floor::factory()->create(['number' => $second]);

    $floors = Floor::defaultOrder()->get();

    expect($floors->get(0)->number)->toBe($first)
    ->and($floors->get(1)->number)->toBe($second)
    ->and($floors->get(2)->number)->toBe($third);
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

test('parentLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    $floor = Floor::factory()->create();

    $floor->load('building.site');

    expect($floor->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $floor->building->site),
        __('Building') => route('archiving.register.building.show', $floor->building),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    $floor = Floor::factory()->create();

    $floor->load('building.site');

    expect($floor->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $floor->building->site),
        __('Building') => route('archiving.register.building.show', $floor->building),
        __('Floor') => route('archiving.register.floor.show', $floor),
    ]);
});
