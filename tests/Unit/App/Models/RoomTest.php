<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Floor;
use App\Models\Room;
use App\Models\Stand;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('throws exception when trying to create rooms in duplicate, that is, with the same numbers and floor', function () {
    $floor = Floor::factory()->create();

    expect(
        fn () => Room::factory(2)->create([
            'number' => '100',
            'floor_id' => $floor->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('throws exception when trying to create room with invalid field', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['number',      null,             'cannot be null'],           // required
    ['number',      Str::random(51),  'Data too long for column'], // maximum 50 characters
    ['description', Str::random(256), 'Data too long for column'], // maximum 255 characters
]);

test('throws exception when trying to set invalid relationship', function ($field, $value, $message) {
    expect(
        fn () => Room::factory()->create([$field => $value])
    )->toThrow(QueryException::class, $message);
})->with([
    ['floor_id', 10,   'Cannot add or update a child row'], // nonexistent
    ['floor_id', null, 'cannot be null'],                   // required
]);

// Happy path
test('create many rooms', function () {
    Room::factory(30)->create();

    expect(Room::count())->toBe(30);
});

test('fields in their maximum size are accepted', function () {
    Room::factory()->create([
        'number' => Str::random(50),
        'description' => Str::random(255),
    ]);

    expect(Room::count())->toBe(1);
});

test('optional fields are set', function () {
    Room::factory()->create(['description' => null]);

    expect(Room::count())->toBe(1);
});

test('createStand save the stand as a child of the room and create a default shelf', function () {
    $stand = new Stand();
    $stand->number = 10;
    $stand->description = 'foo';

    $room = Room::factory()->create();

    $room->createStand($stand);

    $room->load('stands.shelves');

    $stand = $room->stands->first();

    $shelf = $stand->shelves->first();

    expect($stand->number)->toBe(10)
    ->and($stand->description)->toBe('foo')
    ->and($stand->room_id)->toBe($room->id)
    ->and($shelf->number)->toBe(0)
    ->and($shelf->stand_id)->toBe($stand->id)
    ->and($shelf->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
});

test('returns the rooms using the default sort scope defined', function () {
    $first = '100';
    $second = '200';
    $third = '300';

    Room::factory()->create(['number' => $third]);
    Room::factory()->create(['number' => $first]);
    Room::factory()->create(['number' => $second]);

    $rooms = Room::defaultOrder()->get();

    expect($rooms->get(0)->number)->toBe($first)
    ->and($rooms->get(1)->number)->toBe($second)
    ->and($rooms->get(2)->number)->toBe($third);
});

test('one room belongs to one floor', function () {
    $floor = Floor::factory()->create();

    $room = Room::factory()
        ->for($floor, 'floor')
        ->create();

    $room->load(['floor']);

    expect($room->floor)->toBeInstanceOf(Floor::class);
});

test('one room has many stands', function () {
    Room::factory()
        ->has(Stand::factory(3), 'stands')
        ->create();

    $room = Room::with('stands')->first();

    expect($room->stands)->toHaveCount(3);
});

test('parentLinks returns only show parents routes sorted from most distant to closest relationship if root is false', function () {
    $room = Room::factory()->create();

    $room->load('floor.building.site');

    expect($room->parentLinks(false)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $room->floor),
    ]);
});

test('parentLinks returns show parents routes, included the root element route, sorted from most distant to closest relationship if root is true', function () {
    $room = Room::factory()->create();

    $room->load('floor.building.site');

    expect($room->parentLinks(true)->toArray())->toBe([
        __('Site') => route('archiving.register.site.show', $room->floor->building->site),
        __('Building') => route('archiving.register.building.show', $room->floor->building),
        __('Floor') => route('archiving.register.floor.show', $room->floor),
        __('Room') => route('archiving.register.room.show', $room),
    ]);
});
