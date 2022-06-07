<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Box;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create rooms in duplicate, that is, with the same numbers and floor', function () {
    $floor = Floor::factory()->create();

    expect(
        fn () => Room::factory(2)->create([
            'number' => 100,
            'floor_id' => $floor->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create room with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number',      -1,               'Out of range'],             // min 0
    ['number',      4294967296,       'Out of range'],             // max 4294967295
    ['number',     'foo',             'Incorrect integer value'],  // not convertible to integer
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['floor_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['floor_id', null, 'cannot be null'],                   // nonexistent
]);

// Happy path
test('create many rooms', function () {
    Room::factory(30)->create();

    expect(Room::count())->toBe(30);
});

test('fields in their minimum size are accepted', function () {
    Room::factory()->create(['number' => 0]);

    expect(Room::count())->toBe(1);
});

test('fields in their maximum size are accepted', function () {
    Room::factory()->create([
        'number' => 4294967295,
        'description' => Str::random(255),
    ]);

    expect(Room::count())->toBe(1);
});

test('optional fields are set', function () {
    Room::factory()->create(['description' => null]);

    expect(Room::count())->toBe(1);
});

test('returns the rooms using the default sort scope defined', function () {
    $first = 100;
    $second = 200;
    $third = 300;

    Room::factory()->create(['number' => $third]);
    Room::factory()->create(['number' => $first]);
    Room::factory()->create(['number' => $second]);

    $Rooms = Room::defaultOrder()->get();

    expect($Rooms->get(0)->number)->toBe($first)
    ->and($Rooms->get(1)->number)->toBe($second)
    ->and($Rooms->get(2)->number)->toBe($third);
});

test('one room belongs to one floor', function () {
    $floor = Floor::factory()->create();

    $room = Room::factory()
        ->for($floor, 'floor')
        ->create();

    $room->load(['floor']);

    expect($room->floor)->toBeInstanceOf(Floor::class);
});

test('one room has many boxes', function () {
    Room::factory()
        ->has(Box::factory(3), 'boxes')
        ->create();

    $room = Room::with('boxes')->first();

    expect($room->boxes)->toHaveCount(3);
});

test('parentEntitiesLinks returns show parents routes sorted from most distant to closest relationship', function () {
    $room = Room::factory()->create();

    $room->load('floor.building.site');

    $room->parentEntitiesLinks();

    expect($room->parentEntitiesLinks()->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $room->floor),
    ]);
});
